<?php

namespace App\Service;

use App\Enums\UserType;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductStatusHistory;
use App\Models\ProductUpdateRequest;
use App\Models\Shop;
use App\Models\StockHistory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class ProductService
{

    public function index($request)
    {
        if ($request->ajax()) {
            $query = Product::with(['category', 'shop'])
                ->select('products.*');

            if ($request->has('status') && $request->status !== null) {
                $status = $request->status;

                if ($status == '0') {
                    $query->where('status', 0)
                        ->where('approved', 1);
                } else {
                    $query->where('approved', $status);
                }
            }

            if ($request->has('shop_id') && $request->shop_id) {
                $query->where('shop_id', $request->shop_id);
            }

            if ($request->has('category_id') && $request->category_id) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('products.name', 'LIKE', "%{$search}%")
                    ->orWhere('products.product_no', 'LIKE', "%{$search}%")
                    ->orWhere('products.description', 'LIKE', "%{$search}%")
                    ->orWhereHas('shop', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('category', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%");
                    });
                });
            }

            $query->orderBy('created_at', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return $row->created_at ? $row->created_at->format('d-m-Y') : '-';
                })
                ->addColumn('product_no', function ($row) {
                    return $row->product_no ? '<a href="' . url('/admin/products/' . Crypt::encrypt($row->id)) . '" class="text-primary fw-semibold">' . $row->product_no . '</a>' : "-";
                })
                ->addColumn('name', function ($row) {
                    $name = $row->name ?? '-';
                    $description = $row->description ? '<br><small class="text-muted">' . Str::limit($row->description, 50) . '</small>' : '';
                    return $name . $description;
                })
                ->addColumn('shop', function ($row) {
                    return $row->shop ? '<span class="badge bg-info">' . $row->shop->name . '</span>' : '-';
                })
                ->addColumn('category', function ($row) {
                    return $row->category ? $row->category->name : '-';
                })
                ->addColumn('image', function ($row) {
                    if ($row->image) {
                        $url = url('storage/product/' . $row->image);
                        return '<img src="' . $url . '" data-url="' . $url . '" alt="shop-img" width="40" height="40" class="rounded-circle border open-image-model">';
                    }
                    return '<img src="' . url('img/avatar.jpg') . '" alt="product-img" width="40" height="40" class="rounded-circle border">';
                })
                ->addColumn('stock', function ($row) {
                    $stock = $row->product_stock ?? 0;
                    $alert = $row->max_product_stock_alert ?? 0;
                    $stockClass = $stock <= $alert ? 'stock-low' : 'stock-ok';

                    $html = '<div class="text-center">';
                    $html .= '<span class="' . $stockClass . '">' . $stock . '</span>';
                    if ($stock <= $alert) {
                        $html .= '<br><small class="text-danger">Low Stock!</small>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('amount', function ($row) {
                    $price = $row->amount ?? 0;
                    $discount = $row->discount_amount ?? 0;
                    $finalPrice = $discount > 0 ? $discount : $price;

                    $html = '<div class="text-end">';
                    $html .= '<span class="fw-semibold">₹' . number_format($finalPrice, 2) . '</span>';
                    if ($discount > 0) {
                        $html .= '<br><small class="text-danger"><s>₹' . number_format($price, 2) . '</s></small>';
                        $html .= '<br><small class="text-success">' . $row->discount_percentage . '% off</small>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';
                    return '<div class="form-check form-switch">
                        <input class="form-check-input status-toggle" type="checkbox" ' . $checked . ' data-id="' . Crypt::encrypt($row->id) . '">
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    $encryptedId = Crypt::encrypt($row->id);
                    $viewUrl = url('/admin/products/' . $encryptedId);
                    $editUrl = url('/admin/products/' . $encryptedId . '/edit');

                    $html = '<div class="d-flex gap-2 align-items-center">';

                    $html .= '
                        <a href="' . e($viewUrl) . '"
                        class="action-icon"
                        title="View" data-bs-toggle="tooltip">
                            <img src="' . url('img/eye-icon.png') . '" width="30" height="30">
                        </a>';

                    $html .= '
                        <a href="' . e($editUrl) . '"
                        class="action-icon"
                        title="Edit" data-bs-toggle="tooltip">
                            <img src="' . url('img/edit-icon.png') . '" width="30" height="30">
                        </a>';

                    if ($row->overall_status === 'Pending Review') {
                        $html .= '
                            <a href="javascript:void(0);"
                            class="action-icon approve-btn"
                            data-id="' . e($encryptedId) . '"
                            title="Approve" data-bs-toggle="tooltip">
                                <img src="' . url('img/tick-icon.png') . '" width="30" height="30">
                            </a>';

                        $html .= '
                            <a href="javascript:void(0);"
                            class="action-icon reject-btn"
                            data-id="' . e($encryptedId) . '"
                            title="Reject" data-bs-toggle="tooltip">
                                <img src="' . url('img/cancel-icon.png') . '" width="30" height="30">
                            </a>';
                    } elseif ($row->overall_status === 'Rejected') {
                        $html .= '
                            <a href="javascript:void(0);"
                            class="action-icon approve-btn"
                            data-id="' . e($encryptedId) . '"
                            title="Approve" data-bs-toggle="tooltip">
                                <img src="' . url('img/tick-icon.png') . '" width="30" height="30">
                            </a>';
                    }

                    $html .= '
                        <a href="javascript:void(0);"
                        class="action-icon delete-btn"
                        data-id="' . e($encryptedId) . '"
                        title="Delete" data-bs-toggle="tooltip">
                            <img src="' . url('img/delete-icon.png') . '" width="30" height="30">
                        </a>';

                    $html .= '</div>';

                    return $html;
                })
                ->rawColumns(['product_no', 'name', 'shop', 'image', 'stock', 'amount', 'status', 'action'])
                ->make(true);
        }

        $shops = Shop::where('status', true)->orderBy('name')->get();
        $categories = Category::where('status', true)->orderBy('name')->get();

        return view('backend.product.product_index', compact('shops', 'categories'));
    }

    public function getCounts($request)
    {
        $query = Product::query();

        if ($request->has('shop_id') && $request->shop_id) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $total = $query->count();
        $approved = Product::where('approved', Product::APPROVAL_APPROVED)
            ->where('status', Product::STATUS_ACTIVE)
            ->count();
        $pending = Product::where('approved', Product::APPROVAL_PENDING)->count();
        $rejected = Product::where('approved', Product::APPROVAL_REJECTED)->count();
        $deactive = Product::where('approved', Product::APPROVAL_APPROVED)
            ->where('status', Product::STATUS_DEACTIVATED)
            ->count();

        $updateRequestsCount = ProductUpdateRequest::where('status', 'pending')
            ->when($request->shop_id, function ($query) use ($request) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('shop_id', $request->shop_id);
                });
            })
            ->count();

        return response()->json([
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'deactive' => $deactive,
            'update_requests' => $updateRequestsCount
        ]);
    }

    public function pendingRequests($request)
    {
        if ($request->ajax()) {
            $query = ProductUpdateRequest::with(['product', 'requester'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc');

            if ($request->has('shop_id') && $request->shop_id) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('shop_id', $request->shop_id);
                });
            }

            if ($request->has('category_id') && $request->category_id) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('product_update_requests.type', 'LIKE', "%{$search}%")
                    ->orWhereHas('product', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('product_no', 'LIKE', "%{$search}%")
                            ->orWhere('sku', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('requester', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('product.shop', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%");
                    });
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-';
                })
                ->addColumn('product_info', function ($row) {
                    $product = $row->product;
                    if (!$product) return '-';

                    return '
                        <div>
                            <strong>' . e($product->name) . '</strong>
                            <br>
                            <small class="text-muted">ID: ' . e($product->product_no ?? 'N/A') . '</small>
                            <br>
                            <small class="text-muted">SKU: ' . e($product->sku ?? 'N/A') . '</small>
                        </div>
                    ';
                })
                ->addColumn('shop', function ($row) {
                    if ($row->product && $row->product->shop) {
                        return '<span class="badge bg-info">' . e($row->product->shop->name) . '</span>';
                    }
                    return '-';
                })
                ->addColumn('request_type', function ($row) {
                    $typeBadge = [
                        'description' => 'info',
                        'details' => 'warning',
                        'price' => 'danger'
                    ];

                    $color = $typeBadge[$row->type] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($row->type) . ' Update</span>';
                })
                ->addColumn('requester', function ($row) {
                    if ($row->requester) {
                        return '
                            <div>
                                <strong>' . e($row->requester->name) . '</strong>
                                <br>
                                <small class="text-muted">' . e($row->requester->email) . '</small>
                            </div>
                        ';
                    }
                    return '-';
                })
                ->addColumn('changes_summary', function ($row) {
                    $summary = $row->getChangeSummary();
                    if (empty($summary)) {
                        return '<span class="text-muted">No changes detected</span>';
                    }

                    $html = '<div class="changes-summary">';
                    $count = 0;
                    foreach ($summary as $field => $change) {
                        if ($count >= 3) {
                            $html .= '<small class="text-primary">+ ' . (count($summary) - 3) . ' more changes</small>';
                            break;
                        }
                        $html .= '
                            <div class="mb-1">
                                <small class="text-muted">' . e($change['field_name']) . ':</small>
                                <br>
                                <small class="text-danger"><s>' . e($change['old']) . '</s></small>
                                <br>
                                <small class="text-success">→ ' . e($change['new']) . '</small>
                            </div>
                        ';
                        $count++;
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('action', function ($row) {
                    $encryptedId = Crypt::encrypt($row->id);
                    return '
                        <div class="d-flex gap-2">
                            <a href="javascript:void(0);"
                            class="view-request-btn"
                            data-id="' . e($encryptedId) . '"
                            title="View & Process" data-bs-toggle="tooltip">
                                <img src="' . url('img/eye-icon.png') . '" alt="View" width="30" height="30">
                            </a>

                            <a href="javascript:void(0);"
                            class="approve-request-btn"
                            data-id="' . e($encryptedId) . '"
                            title="Approve" data-bs-toggle="tooltip">
                                <img src="' . url('img/tick-icon.png') . '" alt="View" width="30" height="30">
                            </a>

                            <a href="javascript:void(0);"
                            class="reject-request-btn"
                            data-id="' . e($encryptedId) . '"
                            title="Reject" data-bs-toggle="tooltip">
                                <img src="' . url('img/cancel-icon.png') . '" alt="View" width="30" height="30">
                            </a>
                        </div>
                    ';
                })
                ->rawColumns(['product_info', 'shop', 'request_type', 'requester', 'changes_summary', 'action'])
                ->make(true);
        }

        return view('backend.product.pending_requests');
    }

    public function approve($request)
    {
        try {
            $id = Crypt::decrypt($request->id);
            $product = Product::findOrFail($id);

            $oldStatus = $product->approved;
            $product->approved = Product::APPROVAL_APPROVED;
            $product->status = Product::STATUS_ACTIVE;
            $product->rejection_reason = null;
            $product->save();

            $this->logStatusChange($product->id, $oldStatus, Product::APPROVAL_APPROVED, 'Approved by admin');

            return response()->json([
                'success' => true,
                'message' => 'Product approved and activated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve product: ' . $e->getMessage()
            ]);
        }
    }

    public function reject($request)
    {
        try {
            $id = Crypt::decrypt($request->id);
            $product = Product::findOrFail($id);

            $reason = $request->reason;
            if (empty($reason) || strlen(trim($reason)) < 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a valid rejection reason (minimum 10 characters)'
                ]);
            }

            $oldStatus = $product->approved;
            $product->approved = Product::APPROVAL_REJECTED;
            $product->status = Product::STATUS_DEACTIVATED;
            $product->rejection_reason = $reason;
            $product->save();

            $this->logStatusChange($product->id, $oldStatus, Product::APPROVAL_REJECTED, 'Rejected by admin: ' . $reason);

            return response()->json([
                'success' => true,
                'message' => 'Product rejected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject product: ' . $e->getMessage()
            ]);
        }
    }

    public function getUpdateRequest($request, $productUpdateRequest)
    {
        try {
            $productUpdateRequest->load([
                'product.shop',
                'product.category',
                'product.subCategory',
                'product.brand',
                'requester'
            ]);

            $changeSummary = $productUpdateRequest->getChangeSummaryWithPreviews();

            return response()->json([
                'success' => true,
                'data' => $productUpdateRequest,
                'change_summary' => $changeSummary,
                'image_previews' => $this->getImagePreviews($productUpdateRequest)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found'
            ], 404);
        }
    }

    private function getImagePreviews($updateRequest)
    {
        $previews = [];
        $newData = $updateRequest->new_data ?? [];
        $oldData = $updateRequest->old_data ?? [];

        if (isset($newData['image']) && $newData['image'] != ($oldData['image'] ?? null)) {
            $tempPath = storage_path('app/public/temp/update-requests/' . $updateRequest->id . '/main_' . $newData['image']);

            if (file_exists($tempPath)) {
                $previews['main_image'] = [
                    'new' => asset('storage/temp/update-requests/' . $updateRequest->id . '/main_' . $newData['image']),
                    'old' => $oldData['image'] ? asset('storage/product/' . $oldData['image']) : null
                ];
            } else {
                $permPath = storage_path('app/public/product/' . $newData['image']);
                if (file_exists($permPath)) {
                    $previews['main_image'] = [
                        'new' => asset('storage/product/' . $newData['image']),
                        'old' => $oldData['image'] ? asset('storage/product/' . $oldData['image']) : null
                    ];
                }
            }
        }

        if (isset($newData['additional_images'])) {
            $newImages = $newData['additional_images'] ?? [];
            $oldImages = $oldData['additional_images'] ?? [];

            $previews['additional_images'] = [
                'new' => [],
                'old' => []
            ];

            foreach ($newImages as $imageName) {
                $tempPath = storage_path('app/public/temp/update-requests/' . $updateRequest->id . '/additional/' . $imageName);

                if (file_exists($tempPath)) {
                    $previews['additional_images']['new'][] = [
                        'name' => $imageName,
                        'url' => asset('storage/temp/update-requests/' . $updateRequest->id . '/additional/' . $imageName)
                    ];
                } else {
                    $permPath = storage_path('app/public/product/' . $imageName);
                    if (file_exists($permPath)) {
                        $previews['additional_images']['new'][] = [
                            'name' => $imageName,
                            'url' => asset('storage/product/' . $imageName)
                        ];
                    }
                }
            }

            foreach ($oldImages as $imageName) {
                $permPath = storage_path('app/public/product/' . $imageName);
                if (file_exists($permPath)) {
                    $previews['additional_images']['old'][] = [
                        'name' => $imageName,
                        'url' => asset('storage/product/' . $imageName)
                    ];
                }
            }
        }

        return $previews;
    }

    public function approveUpdateRequest($request, $productUpdateRequest)
    {
        DB::beginTransaction();

        try {
            $newData = $productUpdateRequest->new_data;
            $dataToUpdate = array_filter($newData, function ($key) {
                return !in_array($key, ['image', 'additional_images']);
            }, ARRAY_FILTER_USE_KEY);

            $product = $productUpdateRequest->product;

            if (!empty($dataToUpdate)) {
                $product->update($dataToUpdate);
            }

            if (isset($newData['image']) || isset($newData['additional_images'])) {
                $productUpdateRequest->applyImageUpdates();
            }

            $productUpdateRequest->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Update request approved successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to approve update request', [
                'request_id' => $productUpdateRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve request: ' . $e->getMessage()
            ]);
        }
    }

    public function rejectUpdateRequest($request, $productUpdateRequest)
    {
        try {
            $request->validate([
                'rejection_reason' => 'required|string|min:10|max:500'
            ]);

            $productUpdateRequest->cleanupTempImages();

            $productUpdateRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Update request rejected successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to reject update request', [
                'request_id' => $productUpdateRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject request: ' . $e->getMessage()
            ]);
        }
    }

    private function logStatusChange($productId, $oldStatus, $newStatus, $remarks = '', $type = 'approval')
    {
        ProductStatusHistory::create([
            'product_id' => $productId,
            'type' => $type,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'remarks' => $remarks,
            'changed_by' => auth()->id()
        ]);
    }
    public function store(array $data)
    {
        DB::beginTransaction();

        try {
            $latestCode = Product::where('product_no', 'like', 'ALKA%')
                ->orderBy('id', 'desc')
                ->value('product_no');

            if ($latestCode) {
                $num = (int) str_replace('ALKA', '', $latestCode);
                $nextNum = $num + 1;
            } else {
                $nextNum = 1;
            }

            $data['product_no'] = 'ALKA' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

            $product = new Product();

            if ($data['image'] ?? null) {
                $data['image'] = $this->saveFile($data['image']);
            }

            $data['created_by'] = auth()->user()->id;
            $data['approved'] = true;

            if (!empty($data['keywords'])) {
                $keywordsArray = array_map('trim', explode(',', $data['keywords']));
                $data['keywords'] = json_encode($keywordsArray);
            } else {
                $data['keywords'] = null;
            }

            if (!empty($data['additional_info'])) {
                $data['additional_info'] = $this->processAdditionalInfo($data['additional_info']);
            } else {
                $data['additional_info'] = null;
            }

            $product->fill($data);
            $product->save();

            if (!empty($data['additional_images'])) {
                foreach ($data['additional_images'] as $additionalImage) {
                    $imagePath = $this->saveFile($additionalImage, 'product/additional');

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePath,
                        'created_by' => auth()->user()->id
                    ]);
                }
            }

            $stockHistory = new StockHistory();
            $stockHistory->fill([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'Addition',
                'quantity' => $data['product_stock'],
                'previous_stock' => 0,
                'new_stock' => $data['product_stock'],
                'total_stock' => $data['product_stock'],
            ]);
            $stockHistory->save();

            DB::commit();
            session()->flash("success", "Product has been successfully created!");
            return redirect('/admin/products');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash("error", "Error creating product: " . $e->getMessage());
            return back()->withInput();
        }
    }

    public function update(array $data, Product $product)
    {
        DB::beginTransaction();

        try {
            if ($data['image'] ?? null && $data['image']->isValid()) {
                if ($product->image) {
                    Storage::disk('public')->delete('product/' . $product->image);
                }
                $data['image'] = $this->saveFile($data['image']);
            } else {
                $data['image'] = $product->image;
            }

            if (!empty($data['deleted_additional_images'])) {
                $deletedIds = explode(',', $data['deleted_additional_images']);
                $imagesToDelete = $product->additionalImages()
                    ->whereIn('id', $deletedIds)
                    ->get();

                foreach ($imagesToDelete as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }

            if (!empty($data['additional_images'])) {
                $currentImageCount = $product->additionalImages()->count();
                $remainingSlots = 3 - $currentImageCount;

                if (count($data['additional_images']) > $remainingSlots) {
                    throw new \Exception('Cannot upload more than 3 additional images total');
                }

                foreach ($data['additional_images'] as $image) {
                    if ($image->isValid()) {
                        $imagePath = $this->saveFile($image, 'products/additional');
                        $product->additionalImages()->create([
                            'image_path' => $imagePath,
                            'created_by' => auth()->id()
                        ]);
                    }
                }
            }

            if (!empty($data['keywords'])) {
                $keywordsArray = array_map('trim', explode(',', $data['keywords']));
                $data['keywords'] = json_encode($keywordsArray);
            } else {
                $data['keywords'] = null;
            }

            if (!empty($data['additional_info'])) {
                $data['additional_info'] = $this->processAdditionalInfo($data['additional_info']);
            } else {
                $data['additional_info'] = null;
            }

            if (!empty($data['youtube_videos'])) {
                $data['youtube_videos'] = $this->processYoutubeVideos($data['youtube_videos']);
            } else {
                $data['youtube_videos'] = null;
            }

            $product->fill($data);
            $product->save();

            DB::commit();
            session()->flash("success", "Product has been successfully updated!");
            return redirect('/admin/products');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash("error", "Error updating product: " . $e->getMessage());
            return back()->withInput();
        }
    }

    private function processYoutubeVideos(array $youtubeVideos): ?string
    {
        $processedVideos = [];

        foreach ($youtubeVideos as $video) {
            if (empty(trim($video['url'] ?? ''))) {
                continue;
            }

            $processedVideo = [
                'url' => trim($video['url']),
            ];

            $processedVideos[] = $processedVideo;
        }

        if (empty($processedVideos)) {
            return null;
        }

        return json_encode($processedVideos, JSON_UNESCAPED_UNICODE);
    }

    private function processAdditionalInfo(array $additionalInfo): ?string
    {
        $processedInfo = [];

        foreach ($additionalInfo as $section) {
            if (empty(trim($section['heading'] ?? ''))) {
                continue;
            }

            $processedSection = [
                'heading' => trim($section['heading']),
                'items' => []
            ];

            if (!empty($section['items']) && is_array($section['items'])) {
                foreach ($section['items'] as $item) {
                    if (empty(trim($item['title'] ?? '')) && empty(trim($item['value'] ?? ''))) {
                        continue;
                    }

                    $processedSection['items'][] = [
                        'title' => trim($item['title'] ?? ''),
                        'value' => trim($item['value'] ?? '')
                    ];
                }
            }

            if (!empty($processedSection['items'])) {
                $processedInfo[] = $processedSection;
            }
        }

        if (empty($processedInfo)) {
            return null;
        }

        return json_encode($processedInfo, JSON_UNESCAPED_UNICODE);
    }

    public function saveFile($data)
    {
        $image = null;
        if ($data->isValid()) {
            $image = time() . '_' . $data->getClientOriginalName();
            Storage::disk('public')->putFileAs('product/', $data, $image);
        }
        return $image;
    }
    public function destroy($product)
    {
        if ($product->count() != 0) {
            $product->delete();
            return ['status' => '1'];
        } else {
            return ['status' => '0'];
        }
    }

    public function view($product)
    {
        $stockHistory = StockHistory::where('product_id', $product->id)
            ->latest()
            ->paginate(10);
        return view('backend.product.product_view', compact('product', 'stockHistory'));
    }

    public function stockHistory($request, Product $product)
    {
        if ($request->ajax()) {
            $data = StockHistory::where('product_id', $product->id)->with('user')->latest();

            return DataTables::of($data)
                ->addColumn('type', function ($row) {
                    switch ($row->type) {
                        case 'Addition':
                            $class = 'success';
                            break;
                        case 'Restock':
                            $class = 'info';
                            break;
                        default:
                            $class = 'warning';
                            break;
                    }
                    return '<span class="badge bg-' . $class . '">' . ucfirst($row->type) . '</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d M Y h:i A');
                })
                ->addColumn('changed_by', function ($row) {
                    if ($row->user) {
                        return $row->user->type == UserType::SUPERADMIN ? 'Admin' : $row->user->name;
                    }
                    return 'System';
                })

                ->rawColumns(['type'])
                ->make(true);
        }
    }

    public function addStock(array $data, Product $product)
    {
        try {
            DB::beginTransaction();

            $previousStock = $product->product_stock ?? 0;
            $newStock = $previousStock + $data['quantity'];

            $product->update(['product_stock' => $newStock]);

            $stockHistory = new StockHistory();
            $stockHistory->fill([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'Addition',
                'quantity' => $data['quantity'],
                'previous_stock' => $previousStock,
                'new_stock' => $data['quantity'],
                'total_stock' => $newStock,
            ]);
            $stockHistory->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus($request)
    {

        try {
            $id = Crypt::decrypt($request->id);
            $product = Product::findOrFail($id);

            $product->status = $request->status;
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }
}
