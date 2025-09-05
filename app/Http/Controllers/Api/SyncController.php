<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SyncServiceFactory;

class SyncController extends Controller
{
    public function syncSupplier($supplier)
    {
        try {
            $service = SyncServiceFactory::make($supplier);

            $success = $service->syncAll();

            return response()->json([
                'success' => $success,
                'message' => $success
                    ? ucfirst($supplier) . ' products synced successfully.'
                    : 'Error syncing ' . $supplier . ' products.'
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error: ' . $e->getMessage()
            ], 500);
        }
    }
}
