<?php

namespace Database\Seeders;

use App\Models\GalleryImage;
use Illuminate\Database\Seeder;

class GalleryImageSeeder extends Seeder
{
    /**
     * Seeds a handful of licensed stock photos as starter gallery content.
     * Replace via Admin > Gallery once real gym photography is available.
     */
    public function run(): void
    {
        $images = [
            ['image_path' => 'gallery/gallery-1.jpg', 'caption' => 'Group training session', 'sort_order' => 1],
            ['image_path' => 'gallery/gallery-2.jpg', 'caption' => 'Daily fitness class', 'sort_order' => 2],
            ['image_path' => 'gallery/gallery-3.jpg', 'caption' => 'Strength training floor', 'sort_order' => 3],
            ['image_path' => 'gallery/gallery-4.jpg', 'caption' => 'Guided machine training', 'sort_order' => 4],
            ['image_path' => 'gallery/gallery-5.jpg', 'caption' => 'Our facility', 'sort_order' => 5],
            ['image_path' => 'gallery/gallery-6.jpg', 'caption' => 'Floor workout', 'sort_order' => 6],
        ];

        foreach ($images as $image) {
            GalleryImage::updateOrCreate(
                ['image_path' => $image['image_path']],
                $image + ['is_active' => true]
            );
        }
    }
}
