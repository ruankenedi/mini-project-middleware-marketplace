<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\SupplierSyncService;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    protected $syncService;

    public function __construct(SupplierSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function sync(Supplier $supplier, Request $request)
    {
        // synchronous sync
        $result = $this->syncService->syncSupplier($supplier);

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => 'Sync completed', 'processed' => $result['processed']]);
        }

        return response()->json(['success' => false, 'message' => 'Sync failed', 'error' => $result['message'] ?? null], 500);
    }
}
