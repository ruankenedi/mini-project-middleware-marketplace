<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'stock',
        'price',
        'attributes'
    ];

    protected $casts = [
        'attributes' => 'array'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
