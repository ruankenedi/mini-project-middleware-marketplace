<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // check supplier sync existence if supplier_id provided
        if ($request->filled('supplier_id')) {
            $supplierId = (int)$request->get('supplier_id');
            $exists = Product::where('supplier_id', $supplierId)->exists();
            if (!$exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier data not synchronized. Please run sync endpoint: /api/suppliers/' . $supplierId . '/sync'
                ], 422);
            }
            $query->where('supplier_id', $supplierId);
        }

        if ($request->filled('category_id')) $query->where('category_id', $request->get('category_id'));
        if ($request->filled('department_id')) $query->where('department_id', $request->get('department_id'));
        if ($request->filled('store_id')) $query->where('store_id', $request->get('store_id'));
        if ($request->filled('q')) $query->where('name', 'like', '%' . $request->get('q') . '%');
        if ($request->filled('sku')) {
            $query->whereHas('skus', function ($q) use ($request) {
                $q->where('sku', $request->get('sku'));
            });
        }

        $perPage = (int)$request->get('per_page', 25);
        $results = $query->with('skus')->paginate($perPage);

        return response()->json(['success' => true, 'data' => $results]);
    }
}
