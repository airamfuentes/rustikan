<?php

namespace App\Observers;

use App\Mail\StockDisponible;
use App\Models\Producto;
use App\Models\StockAlert;
use Illuminate\Support\Facades\Mail;

class ProductoObserver
{
    public function updating(Producto $producto): void
    {
        // Fire stock alerts when stock transitions from 0 → positive
        if ($producto->isDirty('stock') && $producto->getOriginal('stock') == 0 && $producto->stock > 0) {
            $alerts = StockAlert::with('usuario')
                ->where('producto_id', $producto->id)
                ->get();

            foreach ($alerts as $alert) {
                try {
                    Mail::to($alert->email)->send(
                        new StockDisponible($producto, $alert->usuario?->name ?? 'Cliente')
                    );
                } catch (\Throwable) {
                    // Never block the stock update if mail fails
                }
            }
        }
    }
}
