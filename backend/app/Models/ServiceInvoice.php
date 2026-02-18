<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Factura de servicios
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ServiceInvoice",
    required: ["total", "user_id", "appointment_id"],
    title: "Factura de Servicios",
    description: "Factura generada por servicios realizados"
)]
class ServiceInvoice extends Model
{
    use HasFactory;
    protected $table = 'service_invoices';

    #[OA\Property(format: "int64", description: "ID de la factura", example: 1)]
    private $id;

    #[OA\Property(description: "Número de factura (SINV-XXXXX)", example: "SINV-00001")]
    private $invoice_number;

    #[OA\Property(format: "float", description: "Total de la factura", example: 85.50)]
    private $total;

    #[OA\Property(format: "int64", description: "ID del usuario", example: 1)]
    private $user_id;

    #[OA\Property(format: "int64", description: "ID de la cita asociada", example: 10)]
    private $appointment_id;

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
