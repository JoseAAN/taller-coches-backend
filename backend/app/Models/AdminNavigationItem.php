<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNavigationItem extends Model
{

    use HasFactory;
    protected $fillable = [
        'label',
        'icon',
        'route',
        'parent_id',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    //esto obtiene los hijos de cada tab padre
    public function children(): HasMany
    {
        return $this->hasMany(AdminNavigationItem::class, 'parent_id')->orderBy('order', 'asc');
    }

    //esto obtiene el padre de cada tab
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AdminNavigationItem::class, 'parent_id');
    }
}
