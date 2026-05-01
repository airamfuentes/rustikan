<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Tienda;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $endpoint;

    public function __construct()
    {
        $this->apiKey  = (string) config('services.gemini.key', '');
        $this->model   = (string) config('services.gemini.model', 'gemini-2.5-flash');
        $this->endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * @param array<int, array{role: string, text: string}> $history
     */
    public function chat(string $message, array $history = []): string
    {
        if (! $this->isConfigured()) {
            return 'El asistente IA no está configurado. Por favor, contacta con el administrador.';
        }

        $contents = [];
        foreach ($history as $msg) {
            $role = ($msg['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
            $text = trim((string) ($msg['text'] ?? ''));
            if ($text === '') continue;
            $contents[] = [
                'role'  => $role,
                'parts' => [['text' => $text]],
            ];
        }
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $message]],
        ];

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $this->systemPrompt()]],
            ],
            'contents'         => $contents,
            'generationConfig' => [
                'temperature'     => 0.7,
                'maxOutputTokens' => 600,
                'topP'            => 0.9,
            ],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_ONLY_HIGH'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH',       'threshold' => 'BLOCK_ONLY_HIGH'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
            ],
        ];

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->endpoint . '?key=' . $this->apiKey, $payload);

            if (! $response->successful()) {
                Log::warning('Gemini API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return 'Lo siento, ahora mismo no puedo responder. Inténtalo de nuevo en unos segundos.';
            }

            $data = $response->json();
            $text = data_get($data, 'candidates.0.content.parts.0.text');

            if (! is_string($text) || trim($text) === '') {
                return 'No he podido generar una respuesta. ¿Puedes reformular tu pregunta?';
            }

            return trim($text);
        } catch (\Throwable $e) {
            Log::error('Gemini exception: ' . $e->getMessage());
            return 'Ha ocurrido un error de conexión con el asistente. Inténtalo más tarde.';
        }
    }

    private function systemPrompt(): string
    {
        $contexto = Cache::remember('gemini_contexto_rustikan', 600, function () {
            $categorias = Categoria::query()->pluck('nombre')->implode(', ');
            $tiendas    = Tienda::query()
                ->where('visible', true)
                ->where('activa', true)
                ->with('categoria:id,nombre')
                ->limit(40)
                ->get(['id', 'nombre', 'direccion', 'categoria_id'])
                ->map(fn ($t) => "- {$t->nombre} ({$t->categoria?->nombre}) — {$t->direccion}")
                ->implode("\n");

            return [
                'categorias' => $categorias ?: 'sin categorías cargadas',
                'tiendas'    => $tiendas ?: 'sin tiendas registradas todavía',
            ];
        });

        return <<<PROMPT
Eres Rusti, el asistente virtual oficial de **Rustikan**, una plataforma de comercio local de Lanzarote (Canarias) que conecta productores artesanales con consumidores.

# Sobre Rustikan
- Plataforma web para descubrir y comprar productos de productores locales de Lanzarote.
- Categorías de productos disponibles: {$contexto['categorias']}.
- Los usuarios pueden navegar tiendas, ver productos, hacer pedidos y dejar reseñas.
- Existen tres tipos de usuario: cliente (compra), productor/owner (tiene tienda) y admin.
- Para vender hay que registrarse y solicitar ser productor desde la sección "Vende con nosotros".
- Para repartir hay una sección "Hazte repartidor".
- El pago se hace al recibir el pedido (contra reembolso) o según el método de cada tienda.
- Hay una página de contacto en /contacto y FAQs en /preguntas-frecuentes.

# Tiendas activas (resumen)
{$contexto['tiendas']}

# Reglas
- Responde SIEMPRE en español, salvo que el usuario escriba en otro idioma.
- Sé breve, cordial y útil. Máximo 4-5 frases salvo que pidan detalles.
- No inventes precios, productos ni datos que no aparezcan arriba. Si no lo sabes, di que el usuario puede consultar la tienda concreta o escribir a contacto.
- Si te preguntan por una tienda concreta y no aparece arriba, di que pueden buscarla en el buscador del navbar.
- No respondas a temas no relacionados con Rustikan (política, código, tareas escolares, etc). Redirige amablemente a la web.
- Nunca pidas datos personales sensibles (contraseñas, tarjetas, DNI).
- Si detectas que el usuario tiene un problema técnico grave, recomiéndale escribir a /contacto.
PROMPT;
    }
}
