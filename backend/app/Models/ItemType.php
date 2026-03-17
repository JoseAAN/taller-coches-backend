<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    protected $fillable = ['name'];

    public const PRODUCT = 1;
    public const SERVICE = 2;
}
