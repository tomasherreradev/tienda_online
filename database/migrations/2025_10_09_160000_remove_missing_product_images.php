<?php

use App\Models\ProductImage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        ProductImage::query()->each(function (ProductImage $image) {
            $path = $image->image_url;

            if (empty($path) || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return;
            }

            if (! Storage::disk('public')->exists(ltrim($path, '/'))) {
                $image->delete();
            }
        });
    }

    public function down(): void
    {
        //
    }
};
