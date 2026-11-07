<?php

namespace Database\Seeders;

use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        {
            DB::table('pages')->insert([
                [
                    'id' => 2,
                    'name' => 'home',
                    'slug' => '/',
                    'template_name' => 'light',
                    'custom_link' => NULL,
                    'page_title' => 'Home',
                    'meta_title' => 'Home',
                    'meta_keywords' => '["home"]',
                    'meta_description' => 'This is meta home',
                    'meta_image' => NULL,
                    'meta_image_driver' => NULL,
                    'breadcrumb_image' => NULL,
                    'breadcrumb_image_driver' => 'local',
                    'breadcrumb_status' => 0,
                    'status' => 1,
                    'type' => 0,
                    'is_breadcrumb_img' => 1,
                    'created_at' => '2024-03-27 17:46:22',
                    'updated_at' => '2024-03-28 20:04:51'
                ],
                [
                    'id' => 4,
                    'name' => 'feature',
                    'slug' => 'features',
                    'template_name' => 'light',
                    'custom_link' => NULL,
                    'page_title' => 'Features',
                    'meta_title' => 'Feature',
                    'meta_keywords' => '["feature"]',
                    'meta_description' => 'This is feature',
                    'meta_image' => NULL,
                    'meta_image_driver' => NULL,
                    'breadcrumb_image' => NULL,
                    'breadcrumb_image_driver' => 'local',
                    'breadcrumb_status' => 0,
                    'status' => 1,
                    'type' => 0,
                    'is_breadcrumb_img' => 1,
                    'created_at' => '2024-03-28 16:32:14',
                    'updated_at' => '2024-03-28 20:06:11'
                ]
            ]);
        }

    }
}
