<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    //
    // use HasFactory;

    protected $fillable = [
        'code',
        'total_price',
        'status',
        'sale_date',
        'is_deleted'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }


    // public function void()
    // {
    //     if ($this->status === 'accepted' && $this->is_deleted === 0) {
    //         $this->product->stock->quantity += $this->quantity;
    //         $this->product->stock->save();
    //         $this->update(['status' => 'void', 'is_deleted' => 1]);
    //     }
    // }
}
