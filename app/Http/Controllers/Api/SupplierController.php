<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::select('id', 'name', 'slug', 'status', 'last_sync_at')->get();
        return response()->json(['success' => true, 'data' => $suppliers]);
    }
}
