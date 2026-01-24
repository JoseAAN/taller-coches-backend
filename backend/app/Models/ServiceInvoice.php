<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Factura de servicios
class ServiceInvoice extends Model
{
    use HasFactory;
    protected $table = 'service_invoices';

    protected $fillable = [
        'user_id',
        'total',
        'appointment_id'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
