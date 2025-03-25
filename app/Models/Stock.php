<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    //
    // use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'updated_date',
        'is_deleted'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
