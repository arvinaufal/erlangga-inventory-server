<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SaleDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsReportExport;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = $this->getReportData($request);
        
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Successfully retrieved product report data',
            'data' => $products['data'],
            'total' => $products['total'],
            'page' => $products['page'],
            'perPage' => $products['perPage'],
            'offset' => $products['offset'] ?? 0
        ], 200);
    }

    public function exportExcel(Request $request)
    {
        // Get all data without pagination
        $data = $this->getAllReportData($request);
        
        return Excel::download(
            new ProductsReportExport($data), 
            'products_report.xlsx',
            \Maatwebsite\Excel\Excel::XLSX,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }
    
    public function exportPdf(Request $request)
    {
        // Get all data without pagination
        $data = $this->getAllReportData($request);
        $pdf = Pdf::loadView('exports.products-report', ['products' => $data]);
        
        return $pdf->download('products_report.pdf')->header(
            'Content-Type', 'application/pdf'
        );
    }

    private function getReportData(Request $request)
    {
        // Setup pagination for normal API response
        if ($request->input('perPage') !== 'all') {
            $perPage = $request->input('perPage', 10);
            $page = $request->input('page', 1);
            $offset = ($page - 1) * $perPage;
        }

        $query = $this->buildBaseQuery($request);

        $total = $query->count();

        // Handle data with or without pagination
        if ($request->input('perPage') === 'all') {
            $products = $query->orderBy('id', 'DESC')->get();
            $perPage = $total;
            $page = 1;
            $offset = 0;
        } else {
            $products = $query->orderBy('id', 'DESC')
                ->skip($offset)
                ->take($perPage)
                ->get();
        }

        return [
            'data' => $this->mapProductsData($products),
            'total' => $total,
            'page' => $page ?? 1,
            'perPage' => $perPage ?? $total,
            'offset' => $offset ?? 0
        ];
    }

    private function getAllReportData(Request $request)
    {
        $query = $this->buildBaseQuery($request);
        $products = $query->orderBy('id', 'DESC')->get();
        return $this->mapProductsData($products);
    }

    private function buildBaseQuery(Request $request)
    {
        // Base query for products
        $query = Product::active()->with([
            'category:id,name',
            'stock:product_id,quantity,id',
            'photos' => function($query) {
                $query->active()->select('product_id', 'path');
            }
        ]);

        // Apply search filter if exists
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%")
                  ->orWhereHas('category', function($q) use ($search) {
                      $q->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    private function mapProductsData($products)
    {
        return $products->map(function($product) {
            // Hitung total quantity yang terjual
            $totalSold = SaleDetail::where('product_id', $product->id)
                ->active()
                ->sum('quantity');

            // Hitung profit
            $grossProfit = $product->sell_price * $totalSold;
            $nettProfit = ($product->sell_price - $product->buy_price) * $totalSold;

            // Hitung stok
            $openingStock = $product->stock ? ($product->stock->quantity + $totalSold) : 0;
            $closingStock = $product->stock ? $product->stock->quantity : 0;

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_code' => $product->code,
                'product_category' => $product->category ? $product->category->name : 'No Category',
                'registered_date' => $product->registered_date,
                'sell_price' => $product->sell_price,
                'buy_price' => $product->buy_price,
                'sales_quantity' => $totalSold,
                'opening_stock' => $openingStock,
                'closing_stock' => $closingStock,
                'gross_profit' => $grossProfit,
                'nett_profit' => $nettProfit,
                'photo_path' => $product->photos->first() ? $product->photos->first()->path : null,
                'stock_id' => $product->stock ? $product->stock->id : null
            ];
        });
    }
}