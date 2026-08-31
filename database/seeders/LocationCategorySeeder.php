<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use DB;

class LocationCategorySeeder extends Seeder 
{
    public function run(): void 
    {
        DB::table('location_categories')->insert([
            [
                'id' => uuid(),
                'name' => 'Airport',
                'sort_order' => 1,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Airport',
                'sort_order' => 1,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Bridge',
                'sort_order' => 2,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Building',
                'sort_order' => 3,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Bus Depot',
                'sort_order' => 4,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Dilli Haats',
                'sort_order' => 5,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Flyover',
                'sort_order' => 6,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Foot Over Bridge',
                'sort_order' => 7,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Forest',
                'sort_order' => 8,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Golf Course',
                'sort_order' => 9,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Landmarks',
                'sort_order' => 10,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Market',
                'sort_order' => 11,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Metro',
                'sort_order' => 12,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Monuments',
                'sort_order' => 13,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Others',
                'sort_order' => 14,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Park',
                'sort_order' => 15,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Public Bus',
                'sort_order' => 16,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Railway Station',
                'sort_order' => 17,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Railways Junction',
                'sort_order' => 18,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Road',
                'sort_order' => 19,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'School',
                'sort_order' => 20,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Sports',
                'sort_order' => 21,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
            [
                'id' => uuid(),
                'name' => 'Stadium',
                'sort_order' => 22,
                'status' => 1,
                'created_at' => currentDateTime(),
                'created_by' => '32e68310-18e7-11ef-8177-00155d022d06'
            ],
        ]);
    }
}