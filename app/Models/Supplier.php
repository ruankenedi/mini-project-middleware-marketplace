<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'api_base_url',
        'api_key',
        'last_sync_at',
        'status'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function mappings()
    {
        return $this->hasMany(Mapping::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(SyncLog::class);
    }
}
