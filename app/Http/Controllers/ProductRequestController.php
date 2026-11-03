<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class ProductRequestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $requests = ProductRequest::with(['product.shop', 'product.category', 'product.subCategory', 'requester'])
                ->where('approved', 0)
                ->select('product_requests.*');

            return DataTables::eloquent($requests)
                ->addColumn('product_info', function ($request) {
                    $product = $request->product;
                    $img = $product->image ? url('storage/product/' . $product->image) : url('assets/images/default-product.png');
                    return '<div class="d-flex align-items-center">
                                <img src="' . $img . '" class="rounded me-2" width="40" height="40" alt="' . $product->name . '">
                                <div>
                                    <h6 class="mb-0">' . $product->name . '</h6>
                                    <small class="text-muted">' . ($product->category->name ?? '') . '</small><br>
                                    <small class="text-muted">' . ($product->subCategory->name ?? '') . '</small>
                                </div>
                            </div>';
                })
                ->addColumn('shop_info', function ($request) {
                    $shop = $request->product->shop ?? null;
                    return $shop
                        ? '<a href="' . url('admin/shops/' . Crypt::encrypt($shop->id)) . '" class="text-primary">' . $shop->name . '</a>'
                        : '<span class="text-muted">N/A</span>';
                })
                ->addColumn('category', function ($request) {
                    return $request->product->category->name ?? 'N/A';
                })
                ->addColumn('sub_category', function ($request) {
                    return $request->product->subCategory->name ?? 'N/A';
                })
                ->addColumn('requested_by', function ($request) {
                    return $request->requester->name ?? 'N/A';
                })
                ->addColumn('amount', function ($request) {
                    return $request->product->amount ?? 'N/A';
                })
                ->addColumn('action', function ($request) {
                    $encryptedId = Crypt::encrypt($request->id);

                    $buttons = '<button class="btn btn-sm btn-outline-primary view-product"
                                data-id="' . $encryptedId . '">
                            <i class="mdi mdi-eye"></i> View
                        </button>';

                    $buttons .= '<button class="btn btn-sm btn-success ms-1 approve-product"
                                data-id="' . $encryptedId . '">
                            <i class="mdi mdi-check"></i> Approve
                        </button>';

                    $buttons .= '<button class="btn btn-sm btn-danger ms-1 reject-product"
                                data-id="' . $encryptedId . '">
                            <i class="mdi mdi-close"></i> Reject
                        </button>';

                    return $buttons;
                })
                ->rawColumns(['product_info', 'shop_info', 'action', 'category', 'sub_category', 'amount'])
                ->make(true);
        }

        return view('backend.product.request');
    }

    public function updateStatus(Request $request, ProductRequest $productRequest)
    {
        try {
            $productRequest = ProductRequest::findOrFail($productRequest->id);

            $productRequest->fill([
                'approved' => $request->status,
            ])->save();

            $productRequest->markAsRead();

            $productRequest->product->update([
                'approved' => $request->status,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getProductDetails(ProductRequest $productRequest)
    {
        try {
            $productRequest = ProductRequest::with(['product.shop', 'product.category', 'product.subCategory', 'requester'])
                ->findOrFail($productRequest->id);

            return response()->json([
                'success' => true,
                'html' => view('backend.product.request_details', compact('productRequest'))->render()
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function markAllAsRead()
    {
        ProductRequest::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json(['success' => true]);
    }
}
