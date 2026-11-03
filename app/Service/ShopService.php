<?php

namespace App\Service;

use App\Enums\UserType;
use App\Models\BankDetails;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopChangeRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ShopService
{
    public function index($request)
    {
        if ($request->ajax()) {
            $tab = $request->get('tab', 'shops');

            switch ($tab) {
                case 'shops':
                    return $this->getShopsData($request);
                case 'change-requests':
                    return $this->getChangeRequestsData($request);
                case 'unapproved-shops':
                    return $this->getUnapprovedShopsData($request);
                case 'approved-shops':
                    return $this->getApprovedShopsData($request);
                case 'rejected-shops':
                    return $this->getRejectedShopsData($request);
                default:
                    return $this->getShopsData($request);
            }
        }

        return view('backend.shop.shop_index');
    }

    private function getShopsData($request)
    {
        $query = Shop::with(['user', 'pendingChangeRequests'])
            ->select('shops.*')
            ->orderBy('id', 'desc');

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('shops.name', 'LIKE', "%{$search}%")
                    ->orWhere('shops.email', 'LIKE', "%{$search}%")
                    ->orWhere('shops.owner_name', 'LIKE', "%{$search}%")
                    ->orWhere('shops.owner_phone', 'LIKE', "%{$search}%")
                    ->orWhere('shops.address', 'LIKE', "%{$search}%")
                    ->orWhere('shops.city', 'LIKE', "%{$search}%")
                    ->orWhere('shops.pincode', 'LIKE', "%{$search}%")
                    ->orWhere('shops.website', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    });
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return $row->created_at ? $row->created_at->format('d-m-Y') : '-';
            })
            ->addColumn('name', function ($row) {
                $name = $row->name ?? '-';

                $pendingBadge = '';
                if ($row->has_pending_changes) {
                    $pendingBadge = ' <span class="badge bg-warning" title="Pending Changes">
                        <i class="mdi mdi-pencil me-1"></i>Pending
                    </span>';
                }

                return '<a href="' . url('/admin/shops/' . Crypt::encrypt($row->id)) . '">' .
                    $name . '</a>' . $pendingBadge;
            })
            ->addColumn('owner', function ($row) {
                return $row->owner_name ?? '-';
            })
            ->addColumn('phone', function ($row) {
                return $row->owner_phone ? '<a href="tel:' . $row->owner_phone . '">' . $row->owner_phone . '</a>' : '-';
            })
            ->addColumn('email', function ($row) {
                return $row->email ?? '-';
            })
            ->addColumn('image', function ($row) {
                if ($row->image) {
                    $url = url('storage/shop/' . $row->image);
                    return '<img src="' . $url . '" data-url="' . $url . '" alt="shop-img" width="40" height="40" class="rounded-circle border open-image-model">';
                }
                return '<img src="' . url('img/avatar.jpg') . '" alt="shop-img" width="40" height="40" class="rounded-circle border">';
            })
            ->addColumn('status', function ($row) {
                $checked = $row->status == 1 ? 'checked' : '';
                $shopId = Crypt::encrypt($row->id);

                return '<div class="form-check form-switch">
                    <input class="form-check-input shop-status-toggle" type="checkbox" ' . $checked . '
                    data-id="' . $shopId . '" ' . ($row->has_pending_changes ? 'disabled' : '') . '>
                    ' . ($row->has_pending_changes ? '<small class="text-warning d-block mt-1">Changes Pending</small>' : '') . '
                </div>';
            })
            ->addColumn('action', function ($row) {
                $encryptedId = Crypt::encrypt($row->id);
                $viewUrl = url("/admin/shops/{$encryptedId}");
                $editUrl = url("/admin/shops/{$encryptedId}/edit");
                $requestUrl = url("/admin/shops/{$encryptedId}/change-requests");

                $html = '<div class="d-flex align-items-center gap-2">';

                $html .= '
                    <a href="' . e($viewUrl) . '"
                    class="action-icon"
                    title="View" data-bs-toggle="tooltip">
                        <img src="' . url('img/eye-icon.png') . '" width="30" height="30">
                    </a>';

                if ($row->has_pending_changes) {
                    $html .= '
                        <span class="action-icon disabled-icon"
                            title="Edit (Changes Pending)" data-bs-toggle="tooltip">
                            <img src="' . url('img/tick-icon.png') . '" Disabled" width="30" height="30">
                        </span>';
                } else {
                    $html .= '
                        <a href="' . e($editUrl) . '"
                        class="action-icon"
                        title="Edit" data-bs-toggle="tooltip">
                            <img src="' . url('img/edit-icon.png') . '" width="30" height="30">
                        </a>';
                }

                if ($row->has_pending_changes) {
                    $html .= '
                        <a href="' . e($requestUrl) . '"
                        class="action-icon"
                        title="Review Changes" data-bs-toggle="tooltip">
                            <img src="' . url('img/tick-icon.png') . '" width="30" height="30">
                        </a>';
                }

                $html .= '
                    <a href="javascript:void(0);"
                    class="action-icon delete-shop"
                    data-id="' . e($encryptedId) . '"
                    title="Delete" data-bs-toggle="tooltip">
                        <img src="' . url('img/delete-icon.png') . '" width="30" height="30">
                    </a>';

                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['name', 'phone', 'status', 'action', 'image'])
            ->make(true);
    }

    private function getChangeRequestsData($request)
    {
        $query = ShopChangeRequest::with(['shop', 'user', 'reviewer'])
            ->where('status', 'pending')
            ->select('shop_change_requests.*')
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('shop_change_requests.type', 'LIKE', "%{$search}%")
                    ->orWhere('shop_change_requests.status', 'LIKE', "%{$search}%")
                    ->orWhere('shop_change_requests.remarks', 'LIKE', "%{$search}%")
                    ->orWhereHas('shop', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('owner_name', 'LIKE', "%{$search}%")
                            ->orWhere('owner_phone', 'LIKE', "%{$search}%")
                            ->orWhere('address', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('reviewer', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    });
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return $row->created_at ? $row->created_at->format('d-m-Y h:i A') : '-';
            })
            ->addColumn('shop', function ($row) {
                return $row->shop ? '<a href="' . url('/admin/shops/' . Crypt::encrypt($row->shop_id)) . '" target="_blank">' .
                    $row->shop->name . '</a>' : '-';
            })
            ->addColumn('user', function ($row) {
                return $row->user ? $row->user->name . '<br><small>' . $row->user->email . '</small>' : '-';
            })
            ->addColumn('image', function ($row) {
                $changes = $row->changes ?? [];
                if (isset($changes['image'])) {
                    $tempPath = storage_path('app/public/temp/shop-updates/' . $row->temp_image);
                    if (file_exists($tempPath)) {
                        $url = asset('storage/temp/shop-updates/' . $row->temp_image);
                        return '<img src="' . $url . '" data-url="' . $url . '" alt="new-image" width="40" height="40" class="rounded-circle border open-image-model">';
                    }
                }
                return $row->shop && $row->shop->image ?
                    '<img src="' . $row->shop->image_url . '" data-url="' . $row->shop->image_url . '" alt="shop-img" width="40" height="40" class="rounded-circle border open-image-model">' :
                    '<img src="' . url('img/avatar.jpg') . '" alt="shop-img" width="40" height="40" class="rounded-circle border">';
            })
            ->addColumn('action', function ($row) {
                $html = '<div class="d-flex align-items-center gap-2">';

                $html .= '<a href="javascript:void(0);"
                            class="action-icon review-shop-request"
                            data-id="' . Crypt::encrypt($row->id) . '"
                            title="Review" data-bs-toggle="tooltip">
                            <img src="' . url('img/eye-icon.png') . '" width="30" height="30">
                        </a>';

                $html .= '<a href="javascript:void(0);"
                            class="action-icon approve-shop-request"
                            data-id="' . Crypt::encrypt($row->id) . '"
                            title="Approve" data-bs-toggle="tooltip">
                            <img src="' . url('img/tick-icon.png') . '" width="30" height="30">
                        </a>';

                $html .= '<a href="javascript:void(0);"
                            class="action-icon reject-shop-request"
                            data-id="' . Crypt::encrypt($row->id) . '"
                            title="Reject" data-bs-toggle="tooltip">
                            <img src="' . url('img/close-icon.png') . '" width="30" height="30">
                        </a>';

                $html .= '</div>';
                return $html;
            })
            ->addColumn('action', function ($row) {
                $html = '<div class="d-flex align-items-center gap-2">';

                $html .= '<button class="btn btn-sm btn-info review-shop-request"
                     data-id="' . Crypt::encrypt($row->id) . '" title="Review">
                        <i class="mdi mdi-eye"></i>
                    </button>';

                $html .= '<button class="btn btn-sm btn-success approve-shop-request"
                     data-id="' . Crypt::encrypt($row->id) . '" title="Approve">
                        <i class="mdi mdi-check"></i>
                    </button>';

                $html .= '<button class="btn btn-sm btn-danger reject-shop-request"
                     data-id="' . Crypt::encrypt($row->id) . '" title="Reject">
                        <i class="mdi mdi-close"></i>
                    </button>';

                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['shop', 'user', 'image', 'changes', 'action'])
            ->make(true);
    }

    public function approveChangeRequest($request, $shopChangeRequest)
    {
        DB::beginTransaction();

        try {
            if (!$shopChangeRequest->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This request has already been processed.'
                ], 400);
            }

            $shop = $shopChangeRequest->shop;

            $changes = $shopChangeRequest->changes ?? [];

            if (isset($changes['image']) && $shopChangeRequest->temp_image) {
                $this->handleShopImageChange($shop, $shopChangeRequest);
                unset($changes['image']);
            }

            foreach ($changes as $field => $value) {
                if (in_array($field, $shop->getFillable())) {
                    $shop->$field = $value;
                }
            }

            $shop->save();

            $shopChangeRequest->update([
                'status' => ShopChangeRequest::STATUS_APPROVED,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'approved_data' => $shop->toArray(),
                'remarks' => 'Changes approved by admin',
                'action_taken_by' => auth()->id()
            ]);

            $this->cleanupShopTempImage($shopChangeRequest);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shop change request approved successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error approving shop change request: ' . $e->getMessage(), [
                'request_id' => $shopChangeRequest->id,
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve request: ' . $e->getMessage()
            ], 500);
        }
    }

    private function handleShopImageChange(Shop $shop, ShopChangeRequest $changeRequest)
    {
        $tempImage = $changeRequest->temp_image;
        $newImageName = $changeRequest->changes['image'] ?? null;

        if (!$tempImage || !$newImageName) {
            \Log::warning('Missing temp_image or new image name', [
                'temp_image' => $tempImage,
                'new_image_name' => $newImageName
            ]);
            return;
        }

        if (!file_exists(public_path('storage'))) {
            \Log::error('Storage link does not exist. Run: php artisan storage:link');
            throw new \Exception('Storage link is not configured. Please contact administrator.');
        }

        $tempPath = storage_path('app/public/temp/shop-updates/' . $tempImage);
        $permanentDir = storage_path('app/public/shop/');

        if (!file_exists($permanentDir)) {
            mkdir($permanentDir, 0755, true);
        }

        $permanentPath = $permanentDir . $newImageName;

        \Log::info('Attempting to move image', [
            'temp_path' => $tempPath,
            'permanent_path' => $permanentPath,
            'temp_file_exists' => file_exists($tempPath)
        ]);

        if (!file_exists($tempPath)) {
            $alternativePaths = [
                storage_path('temp/shop-updates/' . $tempImage),
                public_path('temp/shop-updates/' . $tempImage),
                public_path('storage/temp/shop-updates/' . $tempImage),
                storage_path('app/temp/shop-updates/' . $tempImage),
            ];

            foreach ($alternativePaths as $altPath) {
                if (file_exists($altPath)) {
                    $tempPath = $altPath;
                    \Log::info('Found temp image at alternative path', ['path' => $altPath]);
                    break;
                }
            }

            if (!file_exists($tempPath)) {
                \Log::error('Temp image not found at any location', [
                    'temp_image' => $tempImage,
                    'checked_paths' => $alternativePaths
                ]);
                throw new \Exception("Temporary image file not found. Please re-upload the image.");
            }
        }

        if (!copy($tempPath, $permanentPath)) {
            \Log::error('Failed to copy image', [
                'from' => $tempPath,
                'to' => $permanentPath,
                'permission_temp' => is_writable(dirname($tempPath)),
                'permission_dest' => is_writable($permanentDir)
            ]);
            throw new \Exception("Failed to save the image. Check directory permissions.");
        }

        if (!file_exists($permanentPath)) {
            throw new \Exception("Image copy failed. The file was not created at destination.");
        }

        $oldImage = $shop->image;
        if ($oldImage && $oldImage !== 'avatar.jpg') {
            $oldPath = $permanentDir . $oldImage;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $shop->image = $newImageName;

        \Log::info('Image successfully moved/updated', [
            'old_image' => $oldImage,
            'new_image' => $newImageName
        ]);
    }

    private function cleanupShopTempImage(ShopChangeRequest $changeRequest)
    {
        $tempImage = $changeRequest->temp_image;
        if ($tempImage) {
            $pathsToCheck = [
                storage_path('app/public/temp/shop-updates/' . $tempImage),
                storage_path('temp/shop-updates/' . $tempImage),
                public_path('temp/shop-updates/' . $tempImage),
                public_path('storage/temp/shop-updates/' . $tempImage),
                storage_path('app/temp/shop-updates/' . $tempImage),
            ];

            foreach ($pathsToCheck as $path) {
                if (file_exists($path)) {
                    unlink($path);
                    \Log::info('Cleaned up temp image', ['path' => $path]);
                    break;
                }
            }
        }
    }

    public function rejectChangeRequest($request, $shopChangeRequest)
    {
        DB::beginTransaction();

        try {
            if (!$shopChangeRequest->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This request has already been processed.'
                ], 400);
            }

            $reason = $request->reason;
            if (empty($reason) || strlen(trim($reason)) < 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a valid rejection reason (minimum 10 characters)'
                ], 422);
            }

            $this->cleanupShopTempImage($shopChangeRequest);

            $shopChangeRequest->update([
                'status' => ShopChangeRequest::STATUS_REJECTED,
                'rejection_reason' => trim($reason),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'remarks' => 'Rejected: ' . trim($reason),
                'action_taken_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shop change request rejected successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error rejecting shop change request: ' . $e->getMessage(), [
                'request_id' => $shopChangeRequest->id,
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getChangeRequestDetails($shopChangeRequest)
    {
        try {
            $shopChangeRequest->load(['shop', 'user']);

            return response()->json([
                'success' => true,
                'data' => $shopChangeRequest,
                'shop_data' => $shopChangeRequest->shop
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting shop change request details: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load request details'
            ], 500);
        }
    }

    private function getShoplessUsersData($request)
    {
        $query = User::whereDoesntHave('shop')
            ->where('type', UserType::SHOP)
            ->select('users.*')
            ->orderBy('id', 'desc');

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'LIKE', "%{$search}%")
                    ->orWhere('users.email', 'LIKE', "%{$search}%")
                    ->orWhere('users.phone', 'LIKE', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return $row->created_at ? $row->created_at->format('d-m-Y') : '-';
            })
            ->addColumn('name', function ($row) {
                return $row->name ?? '-';
            })
            ->addColumn('phone', function ($row) {
                return $row->phone ?? '-';
            })
            ->addColumn('email', function ($row) {
                return $row->email ?? '-';
            })
            ->addColumn('tax_type', function ($user) {
                return strtoupper($user->tax_type ?? '-');
            })
            ->addColumn('gst_no', function ($user) {
                return $user->tax_type === 'gst'
                    ? ($user->gst_no ?? '-')
                    : '-';
            })
            ->addColumn('pan_no', function ($user) {
                return $user->tax_type === 'pan'
                    ? ($user->pan_no ?? '-')
                    : '-';
            })
            ->addColumn('action', function ($row) {
                $html = '<div class="d-flex align-items-center gap-2">';

                $html .= '<a href="javascript:void(0);"
                            class="action-icon create-shop-for-user"
                            data-id="' . Crypt::encrypt($row->id) . '"
                            title="Create Shop" data-bs-toggle="tooltip">
                            <img src="' . url('img/add-icon.png') . '" width="30" height="30">
                        </a>';

                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['action', 'tax_type', 'gst_no', 'pan_no'])
            ->make(true);
    }

    private function getUnapprovedShopsData($request)
    {
        $query = Shop::with(['user'])
            ->where('approved', 0) // Unapproved shops
            ->select('shops.*')
            ->orderBy('id', 'desc');

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('shops.name', 'LIKE', "%{$search}%")
                    ->orWhere('shops.email', 'LIKE', "%{$search}%")
                    ->orWhere('shops.owner_name', 'LIKE', "%{$search}%")
                    ->orWhere('shops.owner_phone', 'LIKE', "%{$search}%")
                    ->orWhere('shops.address', 'LIKE', "%{$search}%")
                    ->orWhere('shops.city', 'LIKE', "%{$search}%")
                    ->orWhere('shops.pincode', 'LIKE', "%{$search}%")
                    ->orWhere('shops.website', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    });
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return $row->created_at ? $row->created_at->format('d-m-Y') : '-';
            })
            ->addColumn('name', function ($row) {
                $name = $row->name ?? '-';
                return '<a href="' . url('/admin/shops/' . Crypt::encrypt($row->id)) . '">' . $name . '</a>';
            })
            ->addColumn('owner', function ($row) {
                return $row->owner_name ?? '-';
            })
            ->addColumn('phone', function ($row) {
                return $row->owner_phone ? '<a href="tel:' . $row->owner_phone . '">' . $row->owner_phone . '</a>' : '-';
            })
            ->addColumn('email', function ($row) {
                return $row->email ?? '-';
            })
            ->addColumn('image', function ($row) {
                if ($row->image) {
                    $url = url('storage/shop/' . $row->image);
                    return '<img src="' . $url . '" data-url="' . $url . '" alt="shop-img" width="40" height="40" class="rounded-circle border open-image-model">';
                }
                return '<img src="' . url('img/avatar.jpg') . '" alt="shop-img" width="40" height="40" class="rounded-circle border">';
            })
            ->addColumn('rejection_reason', function ($row) {
                return $row->rejection_reason ?? '<span class="text-muted">Not provided</span>';
            })
            ->addColumn('action', function ($row) {
                $encryptedId = Crypt::encrypt($row->id);
                $viewUrl = url("/admin/shops/{$encryptedId}");

                $html = '<div class="d-flex align-items-center gap-2">';

                $html .= '
                    <a href="' . e($viewUrl) . '"
                    class="action-icon"
                    title="View" data-bs-toggle="tooltip">
                        <img src="' . url('img/eye-icon.png') . '" width="30" height="30">
                    </a>';

                $html .= '
                    <a href="javascript:void(0);"
                    class="action-icon approve-shop"
                    data-id="' . e($encryptedId) . '"
                    title="Approve Shop" data-bs-toggle="tooltip">
                        <img src="' . url('img/tick-icon.png') . '" width="30" height="30">
                    </a>';

                $html .= '
                    <a href="javascript:void(0);"
                    class="action-icon reject-shop"
                    data-id="' . e($encryptedId) . '"
                    title="Reject Shop" data-bs-toggle="tooltip">
                        <img src="' . url('img/cancel-icon.png') . '" width="30" height="30">
                    </a>';

                $html .= '
                    <a href="javascript:void(0);"
                    class="action-icon delete-shop"
                    data-id="' . e($encryptedId) . '"
                    title="Delete" data-bs-toggle="tooltip">
                        <img src="' . url('img/delete-icon.png') . '" width="30" height="30">
                    </a>';

                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['name', 'phone', 'image', 'rejection_reason', 'action'])
            ->make(true);
    }

    private function getApprovedShopsData($request)
    {
        $query = Shop::with(['user'])
            ->where('approved', 1) // Approved shops
            ->select('shops.*')
            ->orderBy('id', 'desc');

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('shops.name', 'LIKE', "%{$search}%")
                    ->orWhere('shops.email', 'LIKE', "%{$search}%")
                    ->orWhere('shops.owner_name', 'LIKE', "%{$search}%")
                    ->orWhere('shops.owner_phone', 'LIKE', "%{$search}%")
                    ->orWhere('shops.address', 'LIKE', "%{$search}%")
                    ->orWhere('shops.city', 'LIKE', "%{$search}%")
                    ->orWhere('shops.pincode', 'LIKE', "%{$search}%")
                    ->orWhere('shops.website', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    });
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return $row->created_at ? $row->created_at->format('d-m-Y') : '-';
            })
            ->addColumn('name', function ($row) {
                $name = $row->name ?? '-';
                return '<a href="' . url('/admin/shops/' . Crypt::encrypt($row->id)) . '">' . $name . '</a>';
            })
            ->addColumn('owner', function ($row) {
                return $row->owner_name ?? '-';
            })
            ->addColumn('phone', function ($row) {
                return $row->owner_phone ? '<a href="tel:' . $row->owner_phone . '">' . $row->owner_phone . '</a>' : '-';
            })
            ->addColumn('email', function ($row) {
                return $row->email ?? '-';
            })
            ->addColumn('image', function ($row) {
                if ($row->image) {
                    $url = url('storage/shop/' . $row->image);
                    return '<img src="' . $url . '" data-url="' . $url . '" alt="shop-img" width="40" height="40" class="rounded-circle border open-image-model">';
                }
                return '<img src="' . url('img/avatar.jpg') . '" alt="shop-img" width="40" height="40" class="rounded-circle border">';
            })
            ->addColumn('approved_at', function ($row) {
                return $row->approved_at
                    ? Carbon::parse($row->approved_at)->format('d-m-Y h:i A')
                    : '-';
            })
            ->addColumn('action', function ($row) {
                $encryptedId = Crypt::encrypt($row->id);
                $viewUrl = url("/admin/shops/{$encryptedId}");

                $html = '<div class="d-flex align-items-center gap-2">';

                $html .= '
                    <a href="' . e($viewUrl) . '"
                    class="action-icon"
                    title="View" data-bs-toggle="tooltip">
                        <img src="' . url('img/eye-icon.png') . '" width="30" height="30">
                    </a>';

                $html .= '
                    <a href="javascript:void(0);"
                    class="action-icon unapprove-shop"
                    data-id="' . e($encryptedId) . '"
                    title="Unapprove Shop" data-bs-toggle="tooltip">
                        <img src="' . url('img/cancel-icon.png') . '" width="30" height="30">
                    </a>';

                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['name', 'phone', 'image', 'action'])
            ->make(true);
    }

    private function getRejectedShopsData($request)
    {
        $query = Shop::with(['user'])
            ->where('approved', -1) // Rejected shops
            ->select('shops.*')
            ->orderBy('id', 'desc');

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('shops.name', 'LIKE', "%{$search}%")
                    ->orWhere('shops.email', 'LIKE', "%{$search}%")
                    ->orWhere('shops.owner_name', 'LIKE', "%{$search}%")
                    ->orWhere('shops.owner_phone', 'LIKE', "%{$search}%")
                    ->orWhere('shops.address', 'LIKE', "%{$search}%")
                    ->orWhere('shops.city', 'LIKE', "%{$search}%")
                    ->orWhere('shops.pincode', 'LIKE', "%{$search}%")
                    ->orWhere('shops.website', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    });
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return $row->created_at ? $row->created_at->format('d-m-Y') : '-';
            })
            ->addColumn('name', function ($row) {
                $name = $row->name ?? '-';
                return '<a href="' . url('/admin/shops/' . Crypt::encrypt($row->id)) . '">' . $name . '</a>';
            })
            ->addColumn('owner', function ($row) {
                return $row->owner_name ?? '-';
            })
            ->addColumn('phone', function ($row) {
                return $row->owner_phone ? '<a href="tel:' . $row->owner_phone . '">' . $row->owner_phone . '</a>' : '-';
            })
            ->addColumn('email', function ($row) {
                return $row->email ?? '-';
            })
            ->addColumn('image', function ($row) {
                if ($row->image) {
                    $url = url('storage/shop/' . $row->image);
                    return '<img src="' . $url . '" data-url="' . $url . '" alt="shop-img" width="40" height="40" class="rounded-circle border open-image-model">';
                }
                return '<img src="' . url('img/avatar.jpg') . '" alt="shop-img" width="40" height="40" class="rounded-circle border">';
            })
            ->addColumn('rejection_reason', function ($row) {
                return $row->rejection_reason ?? '<span class="text-muted">Not provided</span>';
            })
            ->addColumn('rejected_at', function ($row) {
                return $row->rejected_at
                    ? Carbon::parse($row->rejected_at)->format('d-m-Y h:i A')
                    : '-';
            })
            ->addColumn('action', function ($row) {
                $encryptedId = Crypt::encrypt($row->id);
                $viewUrl = url("/admin/shops/{$encryptedId}");

                $html = '<div class="d-flex align-items-center gap-2">';

                $html .= '
                    <a href="' . e($viewUrl) . '"
                    class="action-icon"
                    title="View" data-bs-toggle="tooltip">
                        <img src="' . url('img/eye-icon.png') . '" width="30" height="30">
                    </a>';

                $html .= '
                    <a href="javascript:void(0);"
                    class="action-icon approve-shop"
                    data-id="' . e($encryptedId) . '"
                    title="Approve Shop" data-bs-toggle="tooltip">
                        <img src="' . url('img/tick-icon.png') . '" width="30" height="30">
                    </a>';

                $html .= '
                    <a href="javascript:void(0);"
                    class="action-icon delete-shop"
                    data-id="' . e($encryptedId) . '"
                    title="Delete" data-bs-toggle="tooltip">
                        <img src="' . url('img/delete-icon.png') . '" width="30" height="30">
                    </a>';

                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['name', 'phone', 'image', 'rejection_reason', 'action'])
            ->make(true);
    }

    public function createShopForUser(array $data)
    {
        DB::beginTransaction();
        try {

            $userId = Crypt::decrypt($data['user_id']);
            $user = User::findOrFail($userId);

            $imagePath = null;
            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $imagePath = $this->saveFile($data['image']);
            }

            $shop = Shop::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'owner_name' => $data['owner_name'],
                'owner_phone' => $data['owner_phone'],
                'address' => $data['address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'commission_percentage' => $data['commission_percentage'],
                'status' => $data['status'],
                'image' => $imagePath,
            ]);

            BankDetails::create([
                'user_id' => $user->id,
                'bank_name' => $data['bank_name'],
                'account_number' => $data['account_number'],
                'ifsc_code' => $data['ifsc_code'],
                'holder_name' => $data['holder_name'],
                'branch' => $data['branch'],
                'status' => 1
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shop created successfully with bank details',
                'shop_id' => Crypt::encrypt($shop->id),
                'data' => [
                    'shop_name' => $shop->name,
                    'shop_email' => $shop->email,
                ]
            ]);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Invalid user ID'
            ], 400);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            $errorCode = $e->errorInfo[1] ?? null;

            if ($errorCode == 1062) { // Duplicate entry error code for MySQL
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate entry found. Shop or bank details may already exist.'
                ], 409);
            }

            \Log::error('Database error creating shop: ' . $e->getMessage(), [
                'error_info' => $e->errorInfo,
                'user_id' => $data['user_id'] ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database error occurred. Please try again.'
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create shop for user: ' . $e->getMessage(), [
                'user_id' => $data['user_id'] ?? 'unknown',
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create shop: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        $plainPassword = $data['password'];
        $user = new User();
        $user->name = $data['owner_name'];
        $user->phone = $data['owner_phone'];
        $user->email = $data['email'];
        $user->type = UserType::SHOP;
        $user->password = Hash::make($plainPassword);
        $user->save();
        $shop = new Shop();
        if (!empty($data['image'])) {
            $data['image'] = $this->saveFile($data['image']);
        }
        $shop->user_id = $user->id;
        $shop->password = $plainPassword;
        $shop->fill($data);
        $shop->save();
        $bank = new BankDetails();
        $data['user_id'] = $user->id;
        $bank->fill($data);
        $bank->save();
        DB::commit();
        session()->flash("success", "Shop has been successfully created!");
        return redirect('/admin/shops');
    }

    public function update(array $data, Shop $shop)
    {
        DB::beginTransaction();
        $user = $shop->user;

        $user->name = $data['owner_name'];
        $user->email = $data['email'];
        $user->type = UserType::SHOP;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        if (!empty($data['image'])) {
            $data['image'] = $this->saveFile($data['image']);
        } else {
            $data['image'] = $shop->image;
        }

        $data['user_id'] = $user->id;
        $data['password'] = $data['password'] ?? $shop->password;

        $shop->fill($data);
        $shop->save();

        $bank = BankDetails::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );
        DB::commit();
        session()->flash("success", "Shop has been successfully updated!");
        return redirect('/admin/shops');
    }

    public function saveFile($data)
    {
        $image = null;
        if ($data->isValid()) {
            $image = time() . '_' . $data->getClientOriginalName();
            Storage::disk('public')->putFileAs('shop/', $data, $image);
        }
        return $image;
    }

    public function destroy($shop)
    {
        if ($shop) {
            if ($shop->user) {
                $shop->user->delete();
            }
            $shop->delete();
            return ['status' => '1'];
        } else {
            return ['status' => '0'];
        }
    }

    public function updateShop($shop)
    {
        try {
            $shop = Shop::findOrFail($shop->id);

            $shop->status = !$shop->status;
            $shop->save();

            return redirect()->back()->with('success', 'Shop status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update shop status.');
        }
    }

    public function getShopOrders($request, $shop)
    {
        $query = Order::where('shop_id', $shop->id)->latest();

        return DataTables::of($query)
            ->addColumn('action', function ($order) {
                return '<button class="btn btn-sm btn-outline-secondary view-order-items"
                                data-id="' . Crypt::encrypt($order->id) . '" title="View Items">
                            <i class="mdi mdi-eye-outline"></i>
                        </button>';
            })
            ->editColumn('created_at', function ($order) {
                return $order->created_at->format('d M Y');
            })
            ->editColumn('total_amount', function ($order) {
                return '₹ ' . number_format($order->total_amount, 2);
            })

            ->editColumn('status', function ($order) {
                $color = match (strtolower($order->status)) {
                    'pending' => 'warning',
                    'inprogress' => 'info',
                    'completed' => 'success',
                    default => 'secondary',
                };

                return '<span class="badge bg-' . $color . '">' . ucfirst($order->status) . '</span>';
            })

            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function getShopProducts($request, $shop)
    {
        $query = Product::with('category')->where('shop_id', $shop->id);

        return DataTables::of($query)
            ->addColumn('product_info', function ($product) {
                $img = $product->image ? url('storage/product/' . $product->image) : url('assets/images/default-product.png');
                return '<div class="d-flex align-items-center">
                    <img src="' . $img . '" class="rounded me-2" width="40" height="40" alt="' . $product->name . '">
                    <div>
                        <h6 class="mb-0">' . $product->name . '</h6>
                        <small class="text-muted">' . ($product->category->name ?? '') . '</small>
                        <small class="text-muted">' . ($product->subCategory->name ?? '') . '</small>
                    </div>
                </div>';
            })
            ->editColumn('amount', function ($product) {
                return '₹ ' . number_format($product->amount, 2);
            })
            ->editColumn('discount_amount', function ($product) {
                return '₹ ' . number_format($product->discount_amount, 2);
            })
            ->addColumn('product_status', function ($row) {
                return match ($row->approved) {
                    1 => '<span class="badge bg-success float-start">Approved</span>',
                    0 => '<span class="badge bg-warning float-start">Pending</span>',
                    -1 => '<span class="badge bg-danger float-start">Rejected</span>',
                    default => '<span class="badge bg-secondary float-start">Unknown</span>',
                };
            })
            ->editColumn('status', function ($product) {
                $class = $product->status ? 'success' : 'danger';
                $label = $product->status ? 'Active' : 'Inactive';
                return '<span class="badge bg-' . $class . '">' . $label . '</span>';
            })
            ->addColumn('action', function ($product) {
                $buttons = '<button class="btn btn-sm btn-outline-secondary view-product-details"
                        data-id="' . Crypt::encrypt($product->id) . '" title="View Details">
                    <i class="mdi mdi-eye-outline"></i>
                </button>';

                // Add approve/reject buttons if product is pending
                if ($product->approved == 0) {
                    $buttons .= '<button class="btn btn-sm btn-success ms-1 approve-product"
                            data-id="' . Crypt::encrypt($product->id) . '" title="Approve">
                        <i class="mdi mdi-check"></i>
                    </button>
                    <button class="btn btn-sm btn-danger ms-1 reject-product"
                            data-id="' . Crypt::encrypt($product->id) . '" title="Reject">
                        <i class="mdi mdi-close"></i>
                    </button>';
                }

                return $buttons;
            })
            ->rawColumns(['product_info', 'status', 'product_status', 'action', 'discount_amount'])
            ->make(true);
    }

    public function getOrderItems($order)
    {
        try {
            $order = Order::with(['orderItems.product' => function ($query) {
                $query->withTrashed();
            }])->findOrFail($order->id);

            $data = [
                'order' => $order,
                'currency' => config('settings.currency_symbol', '₹'),
                'date_format' => config('settings.date_format', 'd M Y'),
            ];

            return response()->json([
                'success' => true,
                'html' => view('backend.shop.shop_order_item_modal', $data)->render()
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching order items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProductDetails($product)
    {
        try {
            $product = Product::with(['category', 'subCategory', 'shop'])
                ->findOrFail($product->id);

            $data = [
                'product' => $product,
                'currency' => config('settings.currency_symbol', '₹'),
            ];

            return response()->json([
                'success' => true,
                'html' => view('backend.shop.product_details_modal', $data)->render()
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching product details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProductStatus($request)
    {
        try {
            $productId = Crypt::decrypt($request->product_id);
            $product = Product::findOrFail($productId);

            $product->approved = $request->status;
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Product status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating product status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus($request)
    {

        $id = Crypt::decrypt($request->id);
        $shop = Shop::findOrFail($id);

        $shop->status = $request->status;
        $shop->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
        try {
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }
}
