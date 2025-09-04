<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'mapping_type',
        'local_id',
        'supplier_id',
        'supplier_external_id',
        'supplier_external_name'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
