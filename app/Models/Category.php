<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    //
    // use HasFactory;

    protected $fillable = [
        'name',
        'is_deleted'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function delete()
    {
        // Set category_id di products menjadi null
        DB::table('products')
            ->where('category_id', $this->id)
            ->update(['category_id' => null]);

        // Soft delete kategori
        $this->update(['is_deleted' => 1]);
    }

    // public static function rules($id = null)
    // {
    //     return [
    //         'name' => 'required|string|max:200|unique:categories,name,' . ($id ?: 'NULL') . ',id,is_deleted,0',
    //     ];
    // }

}
