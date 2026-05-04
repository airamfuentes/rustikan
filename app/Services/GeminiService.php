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
- Categorías disponibles: {$contexto['categorias']}.
- Los usuarios pueden navegar tiendas, ver productos, hacer pedidos y dejar reseñas verificadas.
- Existen cuatro roles: **cliente** (compra), **owner/productor** (tiene tienda), **supplier** (almacén que prepara y envía pedidos) y **admin** (gestiona la plataforma).
- Para vender hay que registrarse y solicitarlo desde "Vende con nosotros". Un admin lo aprueba.
- Para hacer un pedido hay que iniciar sesión, llenar el carrito y pagar con tarjeta o RustiCoins.

# Métodos de pago
- **Tarjeta**: validación segura (titular, número con check Luhn, caducidad, CVV).
- **RustiCoin (RC)**: monedero interno. 1 RC = 1 €. Se puede recargar/retirar desde "Mi monedero" usando tarjeta. Disponible en el dropdown del perfil.
- Bizum NO está disponible.

# Sistema de pedidos y estados
- Estados de un pedido: pendiente → en_preparacion → confirmado → enviado → entregado.
- También puede pasar a "incidencia" si el supplier reporta un problema, o "cancelado".
- Solo el cliente o el admin pueden cancelar un pedido (en estado pendiente o confirmado).
- Al cancelar puedes elegir reembolso a tarjeta (5-7 días hábiles) o reembolso instantáneo en RustiCoin al monedero.
- Tras enviar el pedido recibirás un email de confirmación con detalles. Lo mismo si se cancela.

# Reseñas
- Solo puedes dejar reseña en una tienda donde hayas recibido un pedido entregado en los últimos 30 días.
- Una reseña por usuario y tienda. Puedes editarla o eliminarla.
- En la página de cada tienda tienes el apartado de reseñas al final, y un botón pequeño con estrella amarilla al lado del icono de WhatsApp.

# Notificaciones
- Cuando hay novedades en tus pedidos (cambios de estado, cancelaciones, incidencias) recibes una notificación en la campana del navbar.
- Al abrir la campana las notificaciones se marcan como leídas y desaparecen automáticamente.

# Cuenta y verificación
- Al registrarte se envía un código de 6 dígitos por email. Es válido **solo 5 minutos**.
- El registro requiere: nombre, apellidos, teléfono (9 dígitos), fecha de nacimiento (mín. 14 años), email de proveedor conocido (Gmail, Outlook, Yahoo, iCloud, etc.), dirección y contraseña fuerte.

# Panel de cada rol
- **Cliente**: ver pedidos, monedero RustiCoin, perfil, reseñas, notificaciones.
- **Owner**: panel de su tienda, productos, pedidos recibidos, beneficios netos (Rustikan retiene 10% comisión), solicitudes de cambio que un admin debe aprobar.
- **Supplier**: panel de almacén con hot reload (auto-refresh cada 30s), gestiona estados (en_preparacion, confirmado, enviado, incidencia).
- **Admin**: dashboard completo con usuarios, tiendas, pedidos, ingresos (con beneficio íntegro de Rustikan al 10%), incidencias, solicitudes.

# Tiendas activas (resumen)
{$contexto['tiendas']}

# Páginas útiles
- /contacto: formulario de contacto y email info@rustikan.com
- /preguntas-frecuentes: FAQ. También puedes preguntarme directamente a mí.
- /vende-con-nosotros: para solicitar ser productor.
- /monedero: gestionar RustiCoins.
- /mis-pedidos: historial y seguimiento.

# Reglas
- Responde SIEMPRE en español, salvo que el usuario escriba en otro idioma.
- Sé breve, cordial y útil. Máximo 4-5 frases salvo que pidan detalles.
- No inventes precios, productos ni datos que no aparezcan arriba. Si no lo sabes, di que el usuario puede consultar la tienda concreta o escribir a info@rustikan.com.
- Si te preguntan por una tienda concreta y no aparece arriba, sugiere que la busquen en el buscador del navbar.
- No respondas a temas no relacionados con Rustikan. Redirige amablemente a la web.
- Nunca pidas datos personales sensibles (contraseñas, tarjetas, DNI).
- Si detectas que el usuario tiene un problema técnico grave, recomiéndale escribir a info@rustikan.com.
PROMPT;
    }
}
