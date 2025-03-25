<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    //
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'is_deleted'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
