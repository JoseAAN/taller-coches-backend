<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Invoice",
    required: ["total", "user_id"],
    title: "Factura Unificada",
    description: "Factura generada tanto para compras de carrito como para citas directas"
)]
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'total',
        'cart_id',
        'user_id',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::creating(function ($invoice) {
            $invoice->invoice_number = self::generateInvoiceNumber();
        });
    }

    public static function generateInvoiceNumber()
    {
        $lastInvoice = self::orderBy('id', 'desc')->first();
        $next = $lastInvoice ? $lastInvoice->id + 1 : 1;
        return 'INV-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
