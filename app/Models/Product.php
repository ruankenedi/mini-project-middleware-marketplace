<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'supplier_id',
        'external_id',
        'name',
        'description',
        'price',
        'department_id',
        'store_id',
        'extra'
    ];

    protected $casts = [
        'extra' => 'array'
    ];

    public function skus()
    {
        return $this->hasMany(Sku::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
