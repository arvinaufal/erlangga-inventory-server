<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductPhoto extends Model
{
    //
    // use HasFactory;

    protected $fillable = [
        'product_id',
        'path',
        'public_id',
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

    public function delete()
    {
        // Soft delete producePhoto
        $this->update(['is_deleted' => 1]);
    }

}
