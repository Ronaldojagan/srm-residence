<?php

namespace App\Service\Api\Shop;

use App\Enums\Status;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductUpdateRequest;
use App\Models\ShopChangeRequest;
use App\Models\StockHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductService
{
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
            $data['approved'] = -1;

            if (!empty($data['keywords'])) {
                $keywordsArray = array_map('trim', explode(',', $data['keywords']));
                $data['keywords'] = json_encode($keywordsArray);
            } else {
                $data['keywords'] = null;
            }

            if (!empty($data['additional_info'])) {

                if (is_string($data['additional_info'])) {
                    $decoded = json_decode($data['additional_info'], true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $data['additional_info'] = null;
                    } else {
                        $data['additional_info'] = $this->processAdditionalInfo($decoded);
                    }
                } elseif (is_array($data['additional_info'])) {
                    $data['additional_info'] = $this->processAdditionalInfo($data['additional_info']);
                }

            } else {
                $data['additional_info'] = null;
            }

            if (!empty($data['youtube_videos'])) {

                if (is_string($data['youtube_videos'])) {
                    $decoded = json_decode($data['youtube_videos'], true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $data['youtube_videos'] = null;
                    } else {
                        $data['youtube_videos'] = $this->processYoutubeVideos($decoded);
                    }
                } elseif (is_array($data['youtube_videos'])) {
                    $data['youtube_videos'] = $this->processYoutubeVideos($data['youtube_videos']);
                }

            } else {
                $data['youtube_videos'] = null;
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

            PriceHistory::create([
                'product_id' => $product->id,
                'old_amount' => null,
                'new_amount' => $data['amount'] ?? null,
                'old_discount_amount' => null,
                'new_discount_amount' => $data['discount_amount'] ?? null,
                'old_discount_percentage' => null,
                'new_discount_percentage' => $data['discount_percentage'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();
            return response()->json([
                'success' => 1,
                'message' => 'Product has been successfully created!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => 0,
                'message' => 'Failed to update Product',
                'error' => $e->getMessage()
            ], 500);
        }
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

    public function updateStatus($request, Product $product)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Unauthorized access.'
                ], 401);
            }

            if ($user->shop->id != $product->shop_id) {
                return response()->json([
                    'success' => 0,
                    'message' => 'You can only update products from your own shop.'
                ], 403);
            }

            $request->validate([
                'status' => 'required|in:1,0'
            ]);

            $product->status = $request->status;
            $product->save();

            return response()->json([
                'success' => 1,
                'message' => 'Status updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => 0,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function productsDetail($productId)
    {
        $product = Product::where('id', $productId)
            ->with(['shop', 'feedbacks'])
            ->first();
        if (!$product) {
            return response()->json([
                'status' => 0,
                'data' => [
                    'product' => [],
                    'similar_products' => []
                ],
                'message' => 'Product Detail Not Found'
            ]);
        }
        $productDetail = $this->formatProduct($product);
        $reviews = $product->feedbacks()
            ->with('user:id,name')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($review) {
                return [
                    'customer_name' => $review->user->name ?? 'Guest',
                    'rating'        => (float)$review->rating,
                    'review'        => $review->review ?? '',
                    'created_at'    => $review->created_at->format('d M Y'),
                ];
            });

        return response()->json([
            'status' => 1,
            'data' => [
                'product' => $productDetail,
                'customer_reviews'  => $reviews,
            ],
            'message' => 'Product Details'
        ]);
    }

    public function formatProduct($product)
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category_name' => $product->category->name,
            'sub_category_name' => $product->subCategory->name,
            'brand_name' => $product->brand->name,
            'category_id' => $product->category->id,
            'sub_category_id' => $product->subCategory->id,
            'brand_id' => $product->brand->id,
            'image' => $product->full_images,
            'quantity' => $product->quantity,
            'product_stock' => $product->product_stock,
            'amount' => $product->amount,
            'discount_amount' => $product->discount_amount,
            'discount_percentage' => $product->discount_percentage,
            'description' => $product->description,
            'status' => $product->status == 1 ? true : false,
            'approved' => match ($product->approved) {
                1   => 'Approved',
                -1  => 'Unapproved',
                2   => 'Rejected',
                default => 'unknown',
            },
            'shop_name' => optional($product->shop)->name,
            'avg_rating' => (string)round($product->averageRating(), 1),
            'total_rating' => (int)$product->feedbacks->count(),

            'additional_images' =>
            $product->additionalImages->map(fn($img) => $img->image_url)
                ->prepend($product->image_url)
                ->toArray(),

            'additional_info' => $product->additional_info ? $this->formatAdditionalInfo($product->additional_info) : [],
            'youtube_videos' => $product->youtube_videos ? $this->formatYoutubeVideos($product->youtube_videos) : [],
        ];
    }

    private function formatYoutubeVideos($videos)
    {
        if (empty($videos)) {
            return [];
        }

        if (is_string($videos)) {
            $decoded = json_decode($videos, true);
        } else {
            $decoded = $videos;
        }

        if (!is_array($decoded)) {
            return [];
        }

        return collect($decoded)->map(function ($video) {
            return [
                'url' => $video['url'] ?? '',
            ];
        })->toArray();
    }

    public function formatAdditionalInfo($info)
    {
        if (empty($info)) {
            return [];
        }

        if (is_string($info)) {
            $decoded = json_decode($info, true);
        } else {
            $decoded = $info;
        }

        if (!is_array($decoded)) {
            return [];
        }

        return collect($decoded)->map(function ($section) {

            return [
                'heading' => $section['heading'] ?? '',
                'items' => collect($section['items'] ?? [])->map(function ($item) {
                    return [
                        'title' => $item['title'] ?? '',
                        'value' => $item['value'] ?? '',
                    ];
                })->toArray()
            ];
        })->toArray();
    }

    public function updatePriceStatus(array $data, Product $product)
    {
        DB::beginTransaction();
        try {
            $oldAmount = $product->amount;
            $oldDiscountAmount = $product->discount_amount;
            $oldDiscountPercentage = $product->discount_percentage;

            $product->update([
                'amount' => $data['amount'] ?? $product->amount,
                'discount_amount' => $data['discount_amount'] ?? $product->discount_amount,
                'discount_percentage' => $data['discount_percentage'] ?? $product->discount_percentage,
            ]);

            PriceHistory::create([
                'product_id' => $product->id,
                'old_amount' => $oldAmount,
                'new_amount' => $product->amount,
                'old_discount_amount' => $oldDiscountAmount,
                'new_discount_amount' => $product->discount_amount,
                'old_discount_percentage' => $oldDiscountPercentage,
                'new_discount_percentage' => $product->discount_percentage,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Product price updated successfully',
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Failed to update product price: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateStock(array $data, Product $product, string $type = 'Addition')
    {
        DB::beginTransaction();

        try {
            $previousStock = $product->product_stock;
            $newStock = $type === 'Addition'
                ? $previousStock + $data['quantity']
                : max($previousStock - $data['quantity'], 0);

            $product->update([
                'product_stock' => $newStock
            ]);

            StockHistory::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => $type,
                'quantity' => $data['quantity'],
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'total_stock' => $newStock,
            ]);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Stock updated successfully.',
                'data' => [
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'product_id' => $product->id
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Failed to update stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateDescriptionRequestSend(array $data, Product $product)
    {
        DB::beginTransaction();

        try {
            $existingRequest = ProductUpdateRequest::where('product_id', $product->id)
                ->where('type', 'description')
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'success' => 0,
                    'message' => 'You already have a pending description update request for this product'
                ], 422);
            }

            $oldData = [
                'description' => $product->description,
            ];

            $newData = [
                'description' => $data['description'] ?? null,
            ];

            $updateRequest = ProductUpdateRequest::create([
                'product_id'   => $product->id,
                'requested_by' => auth()->id(),
                'type'         => 'description',
                'old_data'     => $oldData,
                'new_data'     => $newData,
                'status'       => 'pending',
            ]);

            activity()
                ->performedOn($product)
                ->causedBy(auth()->user())
                ->withProperties([
                    'request_id' => $updateRequest->id,
                    'type'       => 'description',
                ])
                ->log('requested description update');

            DB::commit();

            return response()->json([
                'success' => 1,
                'message' => 'Description update request sent successfully',
                'data' => [
                    'status'     => 'Pending',
                    'review_time' => 'Within 24-48 hours'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Description update request failed', [
                'product_id' => $product->id,
                'user_id'    => auth()->id(),
                'error'      => $e->getMessage()
            ]);

            return response()->json([
                'success' => 0,
                'message' => 'Failed to send update request',
                'error'   => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    public function updateDetailsRequestSend(array $data, Product $product)
    {
        DB::beginTransaction();

        try {
            $existingRequest = ProductUpdateRequest::where('product_id', $product->id)
                ->where('type', 'details')
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'success' => 0,
                    'message' => 'You already have a pending details update request'
                ], 422);
            }

            $currentAdditionalImages = $product->additionalImages()->pluck('image_path')->toArray();

            $oldData = [
                'product_no'              => $product->product_no,
                'name'                    => $product->name,
                'category_id'             => $product->category_id,
                'sub_category_id'         => $product->sub_category_id,
                'brand_id'                => $product->brand_id,
                'shop_id'                 => $product->shop_id,
                'quantity'                => $product->quantity,
                'product_stock'           => $product->product_stock,
                'amount'                  => $product->amount,
                'discount_amount'         => $product->discount_amount,
                'discount_percentage'     => $product->discount_percentage,
                'max_product_stock_alert' => $product->max_product_stock_alert,
                'description'             => $product->description,
                'keywords'                => $product->keywords,
                'additional_info'         => $product->additional_info,
                'image'                   => $product->image,
                'additional_images'       => $currentAdditionalImages,
                'youtube_videos'          => $product->youtube_videos,
            ];

            $newData = [];
            $hasImageChanges = false;

            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $imageName = $this->saveFile($data['image']);

                if ($imageName) {
                    $newData['image'] = $imageName;
                    $hasImageChanges = true;
                }

                unset($data['image']);
            }

            $uploadedAdditionalImages = [];
            if (isset($data['additional_images']) && is_array($data['additional_images'])) {
                foreach ($data['additional_images'] as $index => $additionalImage) {
                    if ($additionalImage instanceof \Illuminate\Http\UploadedFile) {
                        $additionalImageName = $this->saveFile($additionalImage);

                        if ($additionalImageName) {
                            $uploadedAdditionalImages[] = $additionalImageName;
                            $hasImageChanges = true;
                        }
                    }
                }

                if (!empty($uploadedAdditionalImages)) {
                    $newData['additional_images'] = $uploadedAdditionalImages;
                }

                unset($data['additional_images']);
            }

            if (!empty($data['additional_info'])) {

                if (is_string($data['additional_info'])) {
                    $decoded = json_decode($data['additional_info'], true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $data['additional_info'] = null;
                    } else {
                        $data['additional_info'] = $this->processAdditionalInfo($decoded);
                    }
                } elseif (is_array($data['additional_info'])) {
                    $data['additional_info'] = $this->processAdditionalInfo($data['additional_info']);
                }

            } else {
                $data['additional_info'] = null;
            }

            if (!empty($data['youtube_videos'])) {

                if (is_string($data['youtube_videos'])) {
                    $decoded = json_decode($data['youtube_videos'], true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $data['youtube_videos'] = null;
                    } else {
                        $data['youtube_videos'] = $this->processYoutubeVideos($decoded);
                    }
                } elseif (is_array($data['youtube_videos'])) {
                    $data['youtube_videos'] = $this->processYoutubeVideos($data['youtube_videos']);
                }

            } else {
                $data['youtube_videos'] = null;
            }

            foreach ($data as $key => $value) {
                if (array_key_exists($key, $oldData) && $oldData[$key] != $value) {
                    $newData[$key] = $value;
                }
            }

            if (empty($newData) && !$hasImageChanges) {
                return response()->json([
                    'success' => 0,
                    'message' => 'No changes detected'
                ], 422);
            }

            $updateRequest = ProductUpdateRequest::create([
                'product_id'   => $product->id,
                'requested_by' => auth()->id(),
                'type'         => 'details',
                'old_data'     => $oldData,
                'new_data'     => $newData,
                'status'       => 'pending',
            ]);

            if ($hasImageChanges) {
                $this->storeTemporaryImages($updateRequest, $newData);
            }

            activity()
                ->performedOn($product)
                ->causedBy(auth()->user())
                ->withProperties([
                    'request_id' => $updateRequest->id,
                    'type' => 'details',
                    'has_image_changes' => $hasImageChanges,
                ])
                ->log('requested product details update');

            DB::commit();

            return response()->json([
                'success' => 1,
                'message' => 'Details update request sent successfully',
                'data' => [
                    'status' => 'Pending',
                    'review_time' => 'Within 24–48 hours',
                    'has_image_changes' => $hasImageChanges,
                    'request_id' => $updateRequest->id,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Details update request failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => 0,
                'message' => 'Failed to send update request',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
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

    private function storeTemporaryImages(ProductUpdateRequest $updateRequest, array $newData)
    {
        $temporaryImages = [];

        if (isset($newData['image'])) {
            $sourcePath = storage_path('app/public/product/' . $newData['image']);
            $tempPath = storage_path('app/public/temp/update-requests/' . $updateRequest->id);

            $this->ensureDirectoryExists($tempPath);

            $destinationPath = $tempPath . '/main_' . $newData['image'];
            if (file_exists($sourcePath)) {
                copy($sourcePath, $destinationPath);
                $temporaryImages['main_image'] = $newData['image'];
            }
        }

        if (isset($newData['additional_images']) && is_array($newData['additional_images'])) {
            $tempPath = storage_path('app/public/temp/update-requests/' . $updateRequest->id . '/additional');

            $this->ensureDirectoryExists($tempPath);

            $temporaryAdditionalImages = [];
            foreach ($newData['additional_images'] as $imageName) {
                $sourcePath = storage_path('app/public/product/' . $imageName);
                $destinationPath = $tempPath . '/' . $imageName;

                if (file_exists($sourcePath)) {
                    copy($sourcePath, $destinationPath);
                    $temporaryAdditionalImages[] = $imageName;
                }
            }

            if (!empty($temporaryAdditionalImages)) {
                $temporaryImages['additional_images'] = $temporaryAdditionalImages;
            }
        }

        if (!empty($temporaryImages)) {
            $updateRequest->update([
                'temp_images' => $temporaryImages
            ]);
        }
    }

    private function ensureDirectoryExists($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

    public function getPendingRequests(Product $product)
    {
        $requests = $product->pendingUpdateRequests()
            ->with('requester:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    public function updateShopDetails(array $data, $shop)
    {
        if ($shop->user_id !== auth()->id()) {
            return response()->json([
                'success' => 0,
                'message' => 'You are not authorized to update this shop'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $existingRequest = ShopChangeRequest::where('shop_id', $shop->id)
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'success' => 0,
                    'message' => 'You already have a pending shop update request'
                ], 422);
            }

            $oldData = [
                'name' => $shop->name,
                'image' => $shop->image ? url('storage/shop/' . $shop->image) : null,
                'address' => $shop->address,
            ];

            $changes = [];
            $hasImageChanges = false;
            $tempImageName = null;

            if (isset($data['name']) && trim($data['name']) != $shop->name) {
                $changes['name'] = trim($data['name']);
            }

            if (isset($data['address']) && trim($data['address']) != $shop->address) {
                $changes['address'] = trim($data['address']);
            }

            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $image = $data['image'];

                $validator = Validator::make(['image' => $image], [
                    'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => 0,
                        'message' => 'Invalid image',
                        'errors' => $validator->errors()
                    ], 422);
                }

                $imageName = 'temp_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                $imagePath = $image->storeAs('temp/shop-updates', $imageName, 'public');

                if (!$imagePath) {
                    throw new \Exception('Failed to store image');
                }

                $changes['image'] = $imageName;
                $hasImageChanges = true;
                $tempImageName = $imageName;
            }

            if (empty($changes)) {
                return response()->json([
                    'success' => 0,
                    'message' => 'No changes detected'
                ], 422);
            }

            $changeRequestData = [
                'shop_id' => $shop->id,
                'user_id' => auth()->id(),
                'changes' => $changes,
                'old_data' => $oldData,
                'status' => 'pending',
            ];

            if (isset($data['remarks'])) {
                $changeRequestData['remarks'] = trim($data['remarks']);
            }

            $changeRequest = ShopChangeRequest::create($changeRequestData);

            if ($hasImageChanges && $tempImageName) {
                $changeRequest->update([
                    'temp_image' => $tempImageName
                ]);
            }

            activity()
                ->performedOn($shop)
                ->causedBy(auth()->user())
                ->withProperties([
                    'request_id' => $changeRequest->id,
                    'changes' => $changes,
                ])
                ->log('requested shop details update');

            DB::commit();

            return response()->json([
                'success' => 1,
                'message' => 'Shop update request sent successfully',
                'data' => [
                    'request_id' => $changeRequest->id,
                    'status' => 'Pending',
                    'review_time' => 'Within 24-48 hours',
                    'has_image_changes' => $hasImageChanges,
                    'changes_count' => count($changes),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Shop update request failed', [
                'shop_id' => $shop->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($tempImageName) && Storage::disk('public')->exists('temp/shop-updates/' . $tempImageName)) {
                Storage::disk('public')->delete('temp/shop-updates/' . $tempImageName);
            }

            return response()->json([
                'success' => 0,
                'message' => 'Failed to send update request',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    public function productStockHistory($productId)
    {
        try {
            $product = Product::find($productId);

            if (!$product) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Product not found'
                ], 404);
            }

            $stockHistory = StockHistory::where('product_id', $productId)
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
                    ];
                });

            $currentStock = $stockHistory->first()['current_stock'] ?? $product->current_stock ?? 0;

            return response()->json([
                'status' => 1,
                'message' => 'Product stock history retrieved successfully',
                'data' =>  $stockHistory
            ]);

        } catch (\Exception $e) {
            \Log::error('Get product stock history error: ' . $e->getMessage());

            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve product stock history'
            ], 500);
        }
    }
}
