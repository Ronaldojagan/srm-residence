<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUpdateRequest extends Model
{
    use HasFactory, ResolveRouteBinding;

    protected $fillable = [
        'product_id',
        'requested_by',
        'type',
        'old_data',
        'new_data',
        'temp_images',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'temp_images' => 'array',
        'reviewed_at' => 'datetime'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getChangeSummary()
    {
        $summary = [];
        $oldData = $this->old_data ?? [];
        $newData = $this->new_data ?? [];

        // Main Image changes
        if (isset($newData['image']) && isset($oldData['image'])) {
            if ($newData['image'] != $oldData['image']) {
                $summary['image'] = [
                    'old' => $oldData['image'] ? 'Existing Image' : '(no image)',
                    'new' => 'New Image Uploaded',
                    'field_name' => 'Main Image'
                ];
            }
        } elseif (isset($newData['image'])) {
            $summary['image'] = [
                'old' => $oldData['image'] ?? '(no image)',
                'new' => 'New Image Uploaded',
                'field_name' => 'Main Image'
            ];
        }

        // Additional Images changes
        if (isset($newData['additional_images']) || isset($oldData['additional_images'])) {
            $oldCount = is_array($oldData['additional_images'] ?? null) ? count($oldData['additional_images']) : 0;
            $newCount = is_array($newData['additional_images'] ?? null) ? count($newData['additional_images']) : 0;

            if ($oldCount != $newCount) {
                $summary['additional_images'] = [
                    'old' => $oldCount . ' image(s)',
                    'new' => $newCount . ' image(s)',
                    'field_name' => 'Additional Images'
                ];
            }
        }

        // YouTube Videos changes
        if (isset($newData['youtube_videos']) || isset($oldData['youtube_videos'])) {
            $oldVideos = $this->parseYoutubeVideosForSummary($oldData['youtube_videos'] ?? null);
            $newVideos = $this->parseYoutubeVideosForSummary($newData['youtube_videos'] ?? null);

            $oldCount = count($oldVideos);
            $newCount = count($newVideos);

            if ($oldCount != $newCount) {
                $summary['youtube_videos'] = [
                    'old' => $oldCount . ' video(s)',
                    'new' => $newCount . ' video(s)',
                    'field_name' => 'YouTube Videos'
                ];
            } elseif (json_encode($oldVideos) !== json_encode($newVideos)) {
                // Videos count same but URLs might have changed
                $summary['youtube_videos'] = [
                    'old' => $oldCount . ' video(s) (URLs updated)',
                    'new' => $newCount . ' video(s) (URLs updated)',
                    'field_name' => 'YouTube Videos'
                ];
            }
        }

        foreach ($newData as $key => $newValue) {
            if (in_array($key, ['image', 'additional_images', 'youtube_videos'])) {
                continue;
            }

            $oldValue = $oldData[$key] ?? null;

            $displayOld = $oldValue;
            $displayNew = $newValue;

            switch ($key) {
                case 'category_id':
                    $oldCategory = Category::find($oldValue);
                    $newCategory = Category::find($newValue);
                    $displayOld = $oldCategory ? $oldCategory->name : '(empty)';
                    $displayNew = $newCategory ? $newCategory->name : '(empty)';
                    break;

                case 'sub_category_id':
                    $oldSubcategory = Category::find($oldValue);
                    $newSubcategory = Category::find($newValue);
                    $displayOld = $oldSubcategory ? $oldSubcategory->name : '(empty)';
                    $displayNew = $newSubcategory ? $newSubcategory->name : '(empty)';
                    break;

                case 'brand_id':
                    $oldBrand = Brand::find($oldValue);
                    $newBrand = Brand::find($newValue);
                    $displayOld = $oldBrand ? $oldBrand->name : '(empty)';
                    $displayNew = $newBrand ? $newBrand->name : '(empty)';
                    break;

                case 'features':
                case 'keywords':
                case 'meta_keywords':
                case 'tags':
                    $displayOld = is_array($oldValue) ? implode(', ', $oldValue) : $oldValue;
                    $displayNew = is_array($newValue) ? implode(', ', $newValue) : $newValue;
                    break;

                case 'additional_info':
                    $oldInfo = $this->parseAdditionalInfoForSummary($oldValue);
                    $newInfo = $this->parseAdditionalInfoForSummary($newValue);

                    $displayOld = $oldInfo['summary'] ?? '(empty)';
                    $displayNew = $newInfo['summary'] ?? '(empty)';
                    break;

                default:
                    if (is_string($oldValue) && strlen($oldValue) > 50) {
                        $displayOld = substr($oldValue, 0, 50) . '...';
                    }
                    if (is_string($newValue) && strlen($newValue) > 50) {
                        $displayNew = substr($newValue, 0, 50) . '...';
                    }
                    break;
            }

            $displayOld = $displayOld ?? '(empty)';
            $displayNew = $displayNew ?? '(empty)';

            if ($displayOld != $displayNew) {
                $summary[$key] = [
                    'old' => $displayOld,
                    'new' => $displayNew,
                    'field_name' => $this->getFieldDisplayName($key)
                ];
            }
        }

        return $summary;
    }

    private function parseYoutubeVideosForSummary($videosData)
    {
        if (empty($videosData)) {
            return [];
        }

        if (is_string($videosData)) {
            $parsed = json_decode($videosData, true);
        } else {
            $parsed = $videosData;
        }

        if (!is_array($parsed)) {
            return [];
        }

        return array_filter($parsed, function($video) {
            return !empty($video['url']);
        });
    }

    private function parseAdditionalInfoForSummary($infoData)
    {
        if (empty($infoData)) {
            return [
                'summary' => 'No additional information',
                'sections' => 0,
                'items' => 0
            ];
        }

        if (is_string($infoData)) {
            $parsed = json_decode($infoData, true);
        } else {
            $parsed = $infoData;
        }

        if (!is_array($parsed)) {
            return [
                'summary' => 'Invalid data',
                'sections' => 0,
                'items' => 0
            ];
        }

        $totalSections = 0;
        $totalItems = 0;

        foreach ($parsed as $section) {
            if (!empty($section['heading'])) {
                $totalSections++;
                $sectionItems = $section['items'] ?? [];
                $totalItems += count($sectionItems);
            }
        }

        if ($totalSections === 0) {
            return [
                'summary' => 'No additional information',
                'sections' => 0,
                'items' => 0
            ];
        }

        return [
            'summary' => $totalSections . ' section(s), ' . $totalItems . ' item(s)',
            'sections' => $totalSections,
            'items' => $totalItems,
            'data' => $parsed
        ];
    }

    private function getFieldDisplayName($key)
    {
        $fieldNames = [
            'description' => 'Description',
            'short_description' => 'Short Description',
            'features' => 'Features',
            'name' => 'Product Name',
            'category_id' => 'Category',
            'sub_category_id' => 'Sub Category',
            'brand_id' => 'Brand',
            'sku' => 'SKU',
            'weight' => 'Weight',
            'dimensions' => 'Dimensions',
            'warranty_period' => 'Warranty Period',
            'warranty_type' => 'Warranty Type',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'meta_keywords' => 'Meta Keywords',
            'tags' => 'Tags',
            'product_no' => 'Product Number',
            'quantity' => 'Quantity',
            'product_stock' => 'Product Stock',
            'amount' => 'Original Price',
            'discount_amount' => 'Selling Price',
            'discount_percentage' => 'Discount Percentage',
            'max_product_stock_alert' => 'Min Stock Alert',
            'keywords' => 'Keywords',
            'additional_info' => 'Additional Information',
            'youtube_videos' => 'YouTube Videos',
            'image' => 'Main Image',
            'additional_images' => 'Additional Images',
            'shop_id' => 'Shop',
        ];

        return $fieldNames[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    public function applyImageUpdates()
    {
        $product = $this->product;
        $newData = $this->new_data;
        $tempImages = $this->temp_images ?? [];

        if (isset($newData['image']) && isset($tempImages['main_image'])) {
            $oldImage = $product->image;

            $tempPath = storage_path('app/public/temp/update-requests/' . $this->id . '/main_' . $newData['image']);
            $permanentPath = storage_path('app/public/product/' . $newData['image']);

            if (file_exists($tempPath)) {
                if ($oldImage && file_exists(storage_path('app/public/product/' . $oldImage))) {
                    unlink(storage_path('app/public/product/' . $oldImage));
                }

                rename($tempPath, $permanentPath);

                $product->update(['image' => $newData['image']]);
            }
        }

        if (isset($newData['additional_images']) && isset($tempImages['additional_images'])) {
            $tempPath = storage_path('app/public/temp/update-requests/' . $this->id . '/additional');

            $product->additionalImages()->delete();

            foreach ($newData['additional_images'] as $imageName) {
                $sourcePath = $tempPath . '/' . $imageName;
                $destinationPath = storage_path('app/public/product/' . $imageName);

                if (file_exists($sourcePath)) {
                    rename($sourcePath, $destinationPath);

                    $product->additionalImages()->create([
                        'image_path' => $imageName,
                        'created_by' => auth()->id()
                    ]);
                }
            }
        }

        $this->cleanupTempImages();
    }

    public function cleanupTempImages()
    {
        $tempDir = storage_path('app/public/temp/update-requests/' . $this->id);

        if (file_exists($tempDir)) {
            $this->deleteDirectory($tempDir);
        }
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    public function getChangeSummaryWithPreviews()
    {
        $summary = [];
        $oldData = $this->old_data ?? [];
        $newData = $this->new_data ?? [];

        $categories = Category::whereIn('id', [$oldData['category_id'] ?? null, $newData['category_id'] ?? null])
            ->pluck('name', 'id');

        $subcategories = Category::whereIn('id', [$oldData['sub_category_id'] ?? null, $newData['sub_category_id'] ?? null])
            ->pluck('name', 'id');

        $brands = Brand::whereIn('id', [$oldData['brand_id'] ?? null, $newData['brand_id'] ?? null])
            ->pluck('name', 'id');

        if (isset($newData['image']) && ($oldData['image'] ?? null) != $newData['image']) {
            $summary['image'] = [
                'old' => $oldData['image'] ?? '(no image)',
                'new' => 'New Image Uploaded',
                'field_name' => 'Main Image',
                'type' => 'image',
                'has_preview' => true
            ];
        }

        if (isset($newData['additional_images']) || isset($oldData['additional_images'])) {
            $oldCount = is_array($oldData['additional_images'] ?? null) ? count($oldData['additional_images']) : 0;
            $newCount = is_array($newData['additional_images'] ?? null) ? count($newData['additional_images']) : 0;

            if ($oldCount != $newCount) {
                $summary['additional_images'] = [
                    'old' => $oldCount . ' image(s)',
                    'new' => $newCount . ' image(s)',
                    'field_name' => 'Additional Images',
                    'type' => 'image_gallery',
                    'has_preview' => true
                ];
            }
        }

        foreach ($newData as $key => $newValue) {
            if (in_array($key, ['image', 'additional_images'])) {
                continue;
            }

            $oldValue = $oldData[$key] ?? null;

            if ($oldValue == $newValue) {
                continue;
            }

            $displayOld = $oldValue;
            $displayNew = $newValue;
            $fieldType = 'text';

            switch ($key) {
                case 'category_id':
                    $displayOld = $categories[$oldValue] ?? '(empty)';
                    $displayNew = $categories[$newValue] ?? '(empty)';
                    break;

                case 'sub_category_id':
                    $displayOld = $subcategories[$oldValue] ?? '(empty)';
                    $displayNew = $subcategories[$newValue] ?? '(empty)';
                    break;

                case 'brand_id':
                    $displayOld = $brands[$oldValue] ?? '(empty)';
                    $displayNew = $brands[$newValue] ?? '(empty)';
                    break;

                case 'amount':
                case 'discount_amount':
                case 'discount_percentage':
                case 'product_stock':
                case 'max_product_stock_alert':
                case 'quantity':
                    $fieldType = 'number';
                    $displayOld = number_format($oldValue ?? 0);
                    $displayNew = number_format($newValue ?? 0);
                    break;

                case 'description':
                case 'additional_info':
                case 'keywords':
                    $fieldType = 'textarea';
                    if (is_string($oldValue) && strlen($oldValue) > 100) {
                        $displayOld = substr($oldValue, 0, 100) . '...';
                    }
                    if (is_string($newValue) && strlen($newValue) > 100) {
                        $displayNew = substr($newValue, 0, 100) . '...';
                    }
                    break;

                case 'product_no':
                case 'name':
                    if (is_string($oldValue) && strlen($oldValue) > 50) {
                        $displayOld = substr($oldValue, 0, 50) . '...';
                    }
                    if (is_string($newValue) && strlen($newValue) > 50) {
                        $displayNew = substr($newValue, 0, 50) . '...';
                    }
                    break;

                default:
                    if (is_string($oldValue) && strlen($oldValue) > 50) {
                        $displayOld = substr($oldValue, 0, 50) . '...';
                    }
                    if (is_string($newValue) && strlen($newValue) > 50) {
                        $displayNew = substr($newValue, 0, 50) . '...';
                    }
                    break;
            }

            $summary[$key] = [
                'old' => $displayOld ?? '(empty)',
                'new' => $displayNew ?? '(empty)',
                'field_name' => $this->getFieldDisplayName($key),
                'type' => $fieldType,
                'has_preview' => false
            ];
        }

        return $summary;
    }
}
