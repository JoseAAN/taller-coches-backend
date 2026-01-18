<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInvoice extends Model
{
    use HasFactory;
    
    protected $table = 'product_invoices';
    protected $fillable = [
        'invoice_number',
        'total',
        'cart_id',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

}
