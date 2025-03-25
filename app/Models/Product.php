<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'code',
        'registered_date',
        'sell_price',
        'buy_price',
        'is_deleted'
    ];

    // Scope untuk data aktif
    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    // Scope untuk data termasuk yang dihapus
    public function scopeWithTrashed($query)
    {
        return $query; // Tidak melakukan filter, mengambil semua data
    }

    // Relasi dengan category hanya yang aktif
    public function category()
    {
        return $this->belongsTo(Category::class)->where('is_deleted', 0);
    }

    // Relasi dengan photos hanya yang aktif
    public function photos()
    {
        return $this->hasMany(ProductPhoto::class)->where('is_deleted', 0);
    }

    // Relasi dengan stock hanya yang aktif
    public function stock()
    {
        return $this->hasOne(Stock::class)->where('is_deleted', 0);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    // Method delete yang lebih robust
    public function delete()
    {
        DB::transaction(function() {
            // Soft delete stock
            if ($this->stock) {
                $this->stock->update(['is_deleted' => 1]);
            }

            // Soft delete photos
            $this->photos()->update(['is_deleted' => 1]);

            // Soft delete product
            $this->update(['is_deleted' => 1]);
        });
    }
}