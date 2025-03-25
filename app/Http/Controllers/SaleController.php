<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;

        $query = Sale::active();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('code', 'ilike', "%{$search}%");
        }

        $data = $query->orderBy('id', 'DESC')->skip($offset)->take($perPage)->get();
        $total = $query->count();

        return response()->json([
            'code'      => 200,
            'status'    => 'success',
            'message'   => 'Successfully retrieved sales data',
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
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:sales,code,NULL,id,is_deleted,0',
            'total_price' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'items' => 'required|array|min:1', // Minimal 1 item
            'items.*.product_id' => 'required|exists:products,id', // Setiap item harus memiliki product_id yang valid
            'items.*.product_quantity' => 'required|numeric|min:1', // Setiap item harus memiliki quantity minimal 1
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'      => 400,
                'status'    => 'failed',
                'message'   => 'Kolom tidak valid!',
                'errors'    => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Cek stok untuk setiap item
            foreach ($request->input('items') as $item) {
                $productId = $item['product_id'];
                $quantity = $item['product_quantity'];

                // Cek stok produk
                $stock = Stock::where('product_id', $productId)->first();
                if (!$stock || $stock->quantity < $quantity) {
                    DB::rollback();
                    return response()->json([
                        'code'      => 400,
                        'status'    => 'failed',
                        'message'   => 'Stok produk tidak mencukupi untuk product_id: ' . $productId,
                        'data'      => null
                    ], 400);
                }
            }

            $sale = Sale::create([
                'code' => $request->input('code'),
                'total_price' => $request->input('total_price'),
                'sale_date' => $request->input('sale_date'),
                'status' => 'accepted',
                'is_deleted' => 0
            ]);



            // Buat data sale details dan kurangi stok
            foreach ($request->input('items') as $item) {
                $productId = $item['product_id'];
                $quantity = $item['product_quantity'];

                // Buat sale detail
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'is_deleted' => 0
                ]);

                // Kurangi stok produk
                $stock = Stock::where('product_id', $productId)->first();
                $stock->quantity -= $quantity;
                $stock->save();
            }

            DB::commit();

            return response()->json([
                'code'      => 201,
                'status'    => 'success',
                'message'   => 'Sale created successfully',
                'data'      => $sale->load('saleDetails')
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error creating sale: ' . $e->getMessage());

            return response()->json([
                'code'      => 500,
                'status'    => 'failed',
                'message' => 'Error when creating sale',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        //
    }
}
