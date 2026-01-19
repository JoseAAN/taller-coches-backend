<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInvoice extends Model
{
    use HasFactory;
    
    protected $table = 'product_invoices';
    protected $fillable = [
        'total',
        'cart_id',
    ];

    public function cart(){
        return $this->belongsTo(Cart::class);
    }

    //ESto genera el invoice_number directamente
    protected static function booted(){
        static::creating(function ($invoice) {
            $invoice->invoice_number = self::generateInvoiceNumber();
        });
    }

    public static function generateInvoiceNumber(){
        $lastInvoice = self::orderBy('id', 'desc')->first();
        $next = $lastInvoice ? $lastInvoice->id + 1 : 1; //Ternario: si existe lastInvoice se le suma 1 a next, si no se queda en 1
        //Utilicé str_pad para rellenar con ceros la parte izquierda hasta los 5 números, funcion de php
        return 'PINV-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

}
