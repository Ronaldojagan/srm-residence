<?php

namespace App\Service\Api\Shop;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\Status;
use App\Models\User;
use App\Models\Banner;
use App\Models\Address;
use App\Models\BankDetails;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Courier;
use App\Models\Notification;
use App\Models\OrderItem;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\StockHistory;
use App\Models\SubCategory;
use App\Models\UserAccountReason;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CommonService
{
    public function profile()
    {
        $user = User::select('id', 'name', 'phone', 'email', 'image', 'status', 'tax_type', 'gst_no', 'pan_no')
            ->where('id', auth()->id())
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'User not found'
            ], 404);
        }

        $shop = null;

        if ($user->shop) {
            $shop = Shop::select('id', 'name', 'address', 'image', 'status')
                ->where('id', $user->shop->id)
                ->first();
        }

        $userImage = $user->image === 'avatar.jpg'
            ? url('/img/avatar.jpg')
            : url('storage/user/' . $user->image);

        $shopImage = $shop && $shop->image
            ? url('storage/shop/' . $shop->image)
            : null;

        return response()->json([
            'status' => 1,
            'data' => [
                'user' => [
                    'name'   => $user->name,
                    'phone'  => $user->phone,
                    'email'  => $user->email,
                    'image'  => $userImage,
                    'tax_type' => $user->tax_type ? strtoupper($user->tax_type) : null,
                    'gst_no'   => $user->gst_no ?? null,
                    'pan_no'   => $user->pan_no ?? null,
                ],
                'shop' => $shop ? [
                    'id'    => $shop->id,
                    'name'    => $shop->name,
                    'address' => $shop->address,
                    'image'   => $shopImage,
                    'status'  => $shop->status ? "Active" : "Inactive",
                ] : null
            ]
        ]);
    }

    public function profileUpdate(array $data)
    {
        DB::beginTransaction();
        $user = auth()->user();
        if ($data['image'] ?? null) {
            $data['image'] = $this->saveFile($data['image'], 'user');
            if ($user['image']) {
                Storage::disk('public')->delete('user/' . $user['image']);
            }
        }
        $user->fill($data);
        $user->save();
        DB::commit();

        return response()->json([
            'status' => 1,
            'message' => 'Profile updated.',
        ]);
    }

    public function saveFile($file, $path)
    {
        $image = null;
        if ($file->isValid()) {
            $imageName = time() . '_' . $file->getClientOriginalName();
            Storage::disk('public')->putFileAs($path, $file, $imageName);
            return $imageName;
        }
        return $image;
    }

    public function home($request)
    {
        try {
            $finalData = [];
            $user = auth()->user();

            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            $finalData['user_data'] = $this->userData();
            $finalData['shop_create'] = auth()->user()->shop->approved ? true : false;

            $finalData['notification_count'] = Notification::unreadCount(auth()->id());

            $finalData['total_orders_amount'] = $this->getOrderData($user, null, true, $fromDate, $toDate);
            $finalData['total_orders_count'] = $this->getOrderData($user, null, false, $fromDate, $toDate);
            $finalData['delivered_orders_count'] = $this->getOrderData($user, OrderStatus::COMPLETED, false, $fromDate, $toDate);
            $finalData['pending_orders_count'] = $this->getOrderData($user, OrderStatus::PENDING, false, $fromDate, $toDate);
            $finalData['in_progress_orders_count'] = $this->getOrderData($user, OrderStatus::PROCESSING, false, $fromDate, $toDate);
            $finalData['ready_to_deliver_orders_count'] = $this->getOrderData($user, OrderStatus::SHIPPED, false, $fromDate, $toDate);
            $finalData['total_replaced_items'] = $this->getOrderItemsData($user, OrderItemStatus::REPLACED, $fromDate, $toDate);
            $finalData['total_cancelled_items'] = $this->getOrderItemsData($user, OrderItemStatus::CANCELLED, $fromDate, $toDate);
            $finalData['total_returned_items'] = $this->getOrderItemsData($user, OrderItemStatus::RETURNED, $fromDate, $toDate);
            $finalData['recent_orders'] = $this->getRecentOrders($user, $fromDate, $toDate);

            $status = 1;
        } catch (\Exception $e) {
            $status = 0;
            $finalData = $e->getMessage();
        }

        return ['status' => $status, 'data' => $finalData];
    }

    public function userData()
    {
        $user = User::select('name', 'phone', 'image', 'status')->find(auth()->id());

        if (!$user) {
            return null;
        }

        return [
            'name' => $user->name,
            'phone' => $user->phone,
            'image' => $user->image == 'avatar.jpg'
                ? url('/img/avatar.jpg')
                : url('storage/user/' . $user->image),
        ];
    }

    public function addressData()
    {
        $address = Address::where('user_id', auth()->id())
            ->where('is_default', true)
            ->first();

        if (!$address) return null;

        return [
            'city' => $address->city,
            'district' => $address->district,
            'pincode' => $address->pincode
        ];
    }

    public function banner()
    {
        return Banner::where('status', Status::ACTIVE)
            ->get()
            ->map(fn($b) => url("storage/banner/" . $b->image));
    }

    public function getCategories()
    {
        return Category::where('status', Status::ACTIVE)
            ->orderBy('sort_order')
            ->get()
            ->unique('id')
            ->values()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image' => $item->image_url,
                ];
            });
    }


    public function notification($request)
    {
        $user = auth()->user();

        $limit = $request->input('page', 20);

        $notifications = Notification::forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        $formattedNotifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type_label' => $notification->notification_type_label,
                'title' => $notification->title,
                'message' => $notification->message,
                'time_ago' => $notification->time_ago,
                'date_time' => $notification->formatted_date,
            ];
        });

        $unreadCount = Notification::unreadCount($user->id);

        Notification::markAllAsRead($user->id);

        return response()->json([
            'status' => 1,
            'data' => [
                'unread_count' => $unreadCount,
                'notifications' => $formattedNotifications,
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'total' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                    'current_page_url' => $notifications->url($notifications->currentPage()),
                    'next_page_url' => $notifications->nextPageUrl(),
                    'prev_page_url' => $notifications->previousPageUrl(),
                    'from' => $notifications->firstItem(),
                    'to' => $notifications->lastItem(),
                ]
            ]
        ]);
    }

    public function userAccountReasons()
    {
        $reasons = UserAccountReason::where('is_show', true)->get(['id', 'reason']);
        return response()->json(['status' => 1, 'data' => $reasons]);
    }

    public function courier()
    {
        $courier = Courier::where('is_active', true)->get(['id', 'name']);
        return response()->json(['status' => 1, 'data' => $courier]);
    }

    public function getOrderData($user, $status = null, $returnTotalAmount = false, $fromDate = null, $toDate = null)
    {
        if (!$user->shop) {
            return 0;
        }

        $query = ShopOrder::where('shop_id', $user->shop->id);

        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [$fromDate, $toDate]);
        }
        elseif ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        elseif ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $returnTotalAmount ? $query->sum('total_amount') : $query->count();
    }

    public function getOrderItemsData($user, $status = null, $fromDate = null, $toDate = null)
    {
        if (!$user->shop) {
            return 0;
        }

        $query = OrderItem::where('shop_id', $user->shop->id);

        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [$fromDate, $toDate]);
        }
        elseif ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        elseif ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->count();
    }

    public function getRecentOrders($user, $fromDate = null, $toDate = null)
    {
        if (!$user->shop) {
            return [];
        }

        $query = ShopOrder::where('shop_id', $user->shop->id);

        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [$fromDate, $toDate]);
        }
        elseif ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        elseif ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $orders = $query->latest()
            ->limit(5)
            ->get();

        return $orders->map(function ($shopOrder) {
            $item = $shopOrder->orderItems->first();
            return [
                'order_id' => optional($shopOrder->order)->id,
                'order_no' => optional($shopOrder->order)->order_no,
                'date' => $shopOrder->created_at->format('d-m-Y h:i A'),
                'total_items' => $shopOrder->orderItems->count(),
                'status' => ucfirst($shopOrder->status),
                'item_image' => $item->image_url ?? null,
            ];
        });
    }

    public function bankDetails($request)
    {
        $bankDetails = BankDetails::where('user_id', auth()->id())->first();

        return response()->json([
            'success' => 1,
            'data' => $bankDetails,
        ]);
    }

    public function bankDetailsUpdate(array $data)
    {
        $bankDetails = BankDetails::where('user_id', auth()->id())->first();

        if (!$bankDetails) {
            throw new ModelNotFoundException('Bank details not found');
        }

        $bankDetails->update($data);

        return response()->json([
            'success' => 1,
            'data' => 'successfully updated',
        ]);
    }

    public function categorySelectBoxList()
    {
        $categories = Category::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['status' => 1, 'data' => $categories]);
    }

    public function subCategorySelectBoxList($id)
    {
        $subCategories = SubCategory::where('category_id', $id)->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['status' => 1, 'data' => $subCategories]);
    }

    public function brandSelectBoxList()
    {
        $brands = Brand::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['status' => 1, 'data' => $brands]);
    }

    public function stockHistory($request)
    {
        try {
            $stockHistory = StockHistory::with('product')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'type' => ucfirst($item->type),
                        'date' => $item->created_at->format('d-m-Y h:i A'),
                        'previous_stock' => $item->previous_stock,
                        'new_stock' => $item->new_stock,
                        'total_stock' => $item->total_stock,
                        'quantity_change' => $item->quantity,
                        'product_name' => $item->product->name ?? 'N/A'
                    ];
                });


            return response()->json([
                'status' => 1,
                'message' => 'Stock history retrieved successfully',
                'data' =>  $stockHistory
            ]);

        } catch (\Exception $e) {
            \Log::error('Get stock history error: ' . $e->getMessage());

            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve stock history'
            ], 500);
        }
    }

}
