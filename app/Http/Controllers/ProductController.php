<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPhoto;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->input('perPage') !== 'all') {
            $perPage = $request->input('perPage', 10);
            $page = $request->input('page', 1);
            $offset = ($page - 1) * $perPage;
        }
    
        // Pastikan hanya mengambil data active
        $query = Product::active()->with([
            'stock:product_id,quantity,id',
            'category:id,name',
            'photos' => function($query) {
                // $query->active()->select('product_id', 'path')->orderBy('id')->take(1);
                $query->active()->select('product_id', 'path')->orderBy('id');
            }
        ]);
    
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%");
            });
        }

        $total = $query->count();

        if($request->input('perPage') !== 'all') {
            $data = $query->orderBy('id', 'DESC')
                    ->skip($offset)
                    ->take($perPage)
                    ->get()
                    ->map(function($product) {
                        return [
                            'id' => $product->id,
                            'category_id' => $product->category_id,
                            'name' => $product->name,
                            'code' => $product->code,
                            'registered_date' => $product->registered_date,
                            'sell_price' => $product->sell_price,
                            'buy_price' => $product->buy_price,
                            'stock' => $product->stock ? $product->stock->quantity : 0,
                            'stock_id' => $product->stock ? $product->stock->id : null,
                            'category_name' => $product->category ? $product->category->name : null,
                            'photo_path' => $product->photos->first() ? $product->photos->first()->path : null,
                            'photos' => $product->photos,
                            'is_deleted' => $product->is_deleted // Tambahkan ini untuk debugging
                        ];
                    });
        
        } else {
            $data = $query->orderBy('id', 'DESC')
                    ->get()
                    ->map(function($product) {
                        return [
                            'id' => $product->id,
                            'category_id' => $product->category_id,
                            'name' => $product->name,
                            'code' => $product->code,
                            'registered_date' => $product->registered_date,
                            'sell_price' => $product->sell_price,
                            'buy_price' => $product->buy_price,
                            'stock' => $product->stock ? $product->stock->quantity : 0,
                            'stock_id' => $product->stock ? $product->stock->id : null,
                            'category_name' => $product->category ? $product->category->name : null,
                            'photo_path' => $product->photos->first() ? $product->photos->first()->path : null,
                            'photos' => $product->photos,
                            'is_deleted' => $product->is_deleted // Tambahkan ini untuk debugging
                        ];
                    });

                    $perPage = $request->input('perPage');
                    $page = null;
                    $offset = null;

        }
    
        
        return response()->json([
            'code'      => 200,
            'status'    => 'success',
            'message'   => 'Successfully retrieved product data',
            'data'      => $data,
            'total'     => $total,
            'page'      => $page,
            'perPage'   => $perPage,
            'offset'    => $offset
        ], 200);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        //lAMA
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:50|unique:products,code,NULL,id,is_deleted,0',
            'registered_date' => 'required|date',
            'sell_price' => 'required|numeric|min:0',
            'buy_price' => 'required|numeric|min:0',
            'photos' => 'required|array|min:3',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'      => 400,
                'status'    => 'failed',
                'message' => 'Kolom tidak valid!',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Simpan product
            $product = Product::create($request->only([
                'category_id', 'name', 'code', 'registered_date', 'sell_price', 'buy_price'
            ]));

            // Simpan foto-foto
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    // dd($photo)
                    $path = $photo->store('product_photos', 'public'); // Simpan foto di storage
                    ProductPhoto::create([
                        'product_id' => $product->id,
                        'path' => $path
                    ]);
                }
            }


            Stock::create([
                'product_id' => $product->id,
                'quantity' => 0,
                'updated_date' => $request->input('registered_date')
            ]);

            DB::commit();

            return response()->json([
                'code'      => 201,
                'status'    => 'success',
                'message'   => 'Product created successfully',
                'data'      => $product->load('photos') // Load relasi photos
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error creating product: ' . $e->getMessage());

            return response()->json([
                'code'      => 500,
                'status'    => 'failed',
                'message' => 'Error when creating product',
                'data'    => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        // return response()->json([
        //     'code'      => 500,
        //     'status'    => 'failed',
        //     'message' => 'Kolom tidak valid!',
        //     'data' => 'dd($request->hasFile('photos'))'
        // ], 500);
        

        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:categories,id',
            'stock_id' => 'sometimes|exists:stocks,id',
            'name' => 'sometimes|string|max:200',
            'code' => 'sometimes|string|max:50|unique:products,code,' . $product->id . ',id,is_deleted,0',
            'registered_date' => 'sometimes|date',
            'sell_price' => 'sometimes|numeric|min:0',
            'buy_price' => 'sometimes|numeric|min:0',
            'new_photos' => 'sometimes|array|min:3', // Optional, tetapi jika ada, minimal 3 foto
            'new_photos.*' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048', // Format dan ukuran foto
            'stock' => 'sometimes|numeric|min:0', // Optional, tetapi jika ada, harus >= 0
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'code'      => 400,
                'status'    => 'failed',
                'message' => 'Kolom tidak valid!',
                'errors' => $validator->errors()
            ], 400);
        }
    
        DB::beginTransaction();
        try {
            // Update product

            if (!$request->has('add_stock')) {
                $product->update($request->only([
                    'category_id', 'name', 'code', 'registered_date', 'sell_price', 'buy_price'
                ]));
        
                // Update foto-foto (jika ada)
                if ($request->hasFile('new_photos')) {
                    // Hapus foto lama (opsional)
                    foreach ($product->photos as $photo) {
                        Storage::disk('public')->delete($photo->path);
                        $photo->delete();
                    }
        
                    // Simpan foto baru
                    foreach ($request->file('new_photos') as $photo) {
                        $path = $photo->store('product_photos', 'public');
                        ProductPhoto::create([
                            'product_id' => $product->id,
                            'path' => $path
                        ]);
                    }
                }
        
                // Update stock (jika ada)
                if ($request->has('stock')) {
                    $stock = Stock::where('product_id', $product->id)->first();
                    if ($stock) {
                        $stock->update([
                            'quantity' => $request->input('stock'),
                            'updated_date' => now()
                        ]);
                    } else {
                        Stock::create([
                            'product_id' => $product->id,
                            'quantity' => $request->input('stock'),
                            'updated_date' => now()
                        ]);
                    }
                }
            } else {
                $stock = Stock::where('product_id', $product->id)->first();
                if ($stock) {
                    $stock->update([
                        'quantity' => $request->input('add_stock') + $stock->quantity,
                        'updated_date' => now()
                    ]);
                }
            }
    
            DB::commit();
    
            return response()->json([
                'code'      => 200,
                'status'    => 'success',
                'message'   => 'Product updated successfully',
                'data'      => $product->load(['photos', 'stock']) // Load relasi photos dan stock
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
    
            Log::error('Error updating product: ' . $e->getMessage());
    
            return response()->json([
                'code'      => 500,
                'status'    => 'success',
                'message' => 'Error when updating product',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
        DB::beginTransaction();
        try {
            $product = Product::findOrFail($product->id)->delete();

            DB::commit();

            return response()->json([
                'code'      => 200,
                'status'    => 'success',
                'message'   => 'Product deleted successfully',
                'data'      => $product
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error deleting product: ' . $e->getMessage());

            return response()->json([
                'code'      => 500,
                'status'    => 'failed',
                'message' => 'Error when deleting product',
                'data'    => null
            ], 500);
        }
    }
}
