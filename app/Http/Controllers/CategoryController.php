<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        //
        if ($request->input('perPage') !== 'all') {

            $perPage = $request->input('perPage', 10);
            $page = $request->input('page', 1);
            $offset = ($page - 1) * $perPage;

        }

        $query = Category::active();
        
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'ilike', "%{$search}%");
        }

        $data = $query->orderBy('id', 'DESC');

        if($request->input('perPage') !== 'all') {

            $data = $data->skip($offset)->take($perPage)->get();
        } else {
            $perPage = $request->input('perPage');
            $page = null;
            $offset = null;
            $data = $data->get();
        }

        $total = $query->count();

        return response()->json([
            'code'      => 200,
            'message'   => 'Successfully retrieved category data',
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
            'name' => 'required|string|max:200|unique:categories,name,NULL,id,is_deleted,0',
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
            $category = Category::create($request->only(['name']));

            DB::commit();

            return response()->json([
                'code'      => 201,
                'status'    => 'success',
                'message'   => 'Category created successfully',
                'data'      => $category
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error creating category: ' . $e->getMessage());

            return response()->json([
                'code'      => 500,
                'status'    => 'failed',
                'message' => 'Error when creating category',
                'data'    => null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200|unique:categories,name,' . $category->id . ',id,is_deleted,0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status'    => 'failed',
                'message' => 'Kolom tidak valid!',
                'errors' => $validator->errors()
            ], 400);
        }
    
        DB::beginTransaction();
        try {
            $category->update($request->only(['name']));
    
            DB::commit();
    
            return response()->json([
                'code'      => 200,
                'status'    => 'success',
                'message'   => 'Category updated successfully',
                'data'      => $category
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
    
            Log::error('Error updating category: ' . $e->getMessage());
    
            return response()->json([
                'code' => 500,
                'status'    => 'failed',
                'message' => 'Error when updating category',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
        DB::beginTransaction();
        try {
            $category = Category::findOrFail($category->id)->delete();

            DB::commit();

            return response()->json([
                'code'      => 200,
                'status'    => 'success',
                'message'   => 'Category deleted successfully',
                'data'      => $category
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error deleting category: ' . $e->getMessage());

            return response()->json([
                'code' => 500,
                'status'    => 'failed',
                'message' => 'Error when deleting category',
                'data'    => null
            ], 500);
        }
    }
}
