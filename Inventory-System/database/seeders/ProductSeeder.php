<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Lumber
            ['name' => '2x4 Pine Stud (8ft)', 'sku' => 'LUM-1001', 'description' => 'Standard construction-grade pine stud.', 'category' => 'Lumber', 'quantity' => 250, 'reorder_level' => 50, 'unit_price' => 4.99, 'supplier' => 'Pacific Timber Co.'],
            ['name' => 'Plywood Sheet 4x8 (1/2")', 'sku' => 'LUM-1002', 'description' => 'CDX plywood sheet for general construction.', 'category' => 'Lumber', 'quantity' => 18, 'reorder_level' => 20, 'unit_price' => 34.99, 'supplier' => 'Pacific Timber Co.'],
            ['name' => 'Treated Deck Board (5/4x6)', 'sku' => 'LUM-1003', 'description' => 'Pressure-treated board for outdoor decking.', 'category' => 'Lumber', 'quantity' => 0, 'reorder_level' => 30, 'unit_price' => 8.49, 'supplier' => 'GreenWood Supply'],

            // Hardware
            ['name' => 'Wood Screws #8 x 2" (Box of 100)', 'sku' => 'HW-2001', 'description' => 'Multi-purpose wood screws, zinc coated.', 'category' => 'Hardware', 'quantity' => 140, 'reorder_level' => 40, 'unit_price' => 9.99, 'supplier' => 'FastenPro'],
            ['name' => 'Heavy Duty Door Hinges (3-Pack)', 'sku' => 'HW-2002', 'description' => 'Stainless steel door hinges, 3.5 inch.', 'category' => 'Hardware', 'quantity' => 12, 'reorder_level' => 15, 'unit_price' => 14.99, 'supplier' => 'FastenPro'],
            ['name' => 'Padlock 40mm', 'sku' => 'HW-2003', 'description' => 'Weather-resistant brass padlock with 2 keys.', 'category' => 'Hardware', 'quantity' => 65, 'reorder_level' => 20, 'unit_price' => 11.49, 'supplier' => 'SecureLine'],

            // Tools
            ['name' => 'Claw Hammer 16oz', 'sku' => 'TL-3001', 'description' => 'Fiberglass handle claw hammer.', 'category' => 'Tools', 'quantity' => 30, 'reorder_level' => 10, 'unit_price' => 19.99, 'supplier' => 'ToolMaster Inc.'],
            ['name' => 'Cordless Drill 18V', 'sku' => 'TL-3002', 'description' => 'Includes battery, charger, and carrying case.', 'category' => 'Tools', 'quantity' => 8, 'reorder_level' => 10, 'unit_price' => 89.99, 'supplier' => 'ToolMaster Inc.'],
            ['name' => 'Tape Measure 25ft', 'sku' => 'TL-3003', 'description' => 'Heavy-duty tape measure with magnetic hook.', 'category' => 'Tools', 'quantity' => 45, 'reorder_level' => 15, 'unit_price' => 12.99, 'supplier' => 'ToolMaster Inc.'],
            ['name' => 'Circular Saw 7-1/4"', 'sku' => 'TL-3004', 'description' => 'Corded circular saw with laser guide.', 'category' => 'Tools', 'quantity' => 0, 'reorder_level' => 8, 'unit_price' => 64.99, 'supplier' => 'ProCut Tools'],

            // Electrical
            ['name' => '14/2 Romex Wire (250ft)', 'sku' => 'ELEC-4001', 'description' => 'Copper NM-B electrical wire.', 'category' => 'Electrical', 'quantity' => 22, 'reorder_level' => 10, 'unit_price' => 79.99, 'supplier' => 'CircuitWorks'],
            ['name' => 'Duplex Outlet 15A', 'sku' => 'ELEC-4002', 'description' => 'Standard grounded electrical outlet.', 'category' => 'Electrical', 'quantity' => 95, 'reorder_level' => 30, 'unit_price' => 2.49, 'supplier' => 'CircuitWorks'],
            ['name' => 'LED Shop Light 4ft', 'sku' => 'ELEC-4003', 'description' => 'Linkable LED shop light, 4500 lumens.', 'category' => 'Electrical', 'quantity' => 5, 'reorder_level' => 10, 'unit_price' => 24.99, 'supplier' => 'BrightPath Lighting'],

            // Plumbing
            ['name' => 'PVC Pipe 1/2" (10ft)', 'sku' => 'PLB-5001', 'description' => 'Schedule 40 PVC pipe for plumbing.', 'category' => 'Plumbing', 'quantity' => 60, 'reorder_level' => 20, 'unit_price' => 6.49, 'supplier' => 'FlowRight Supply'],
            ['name' => 'Compression Fitting Kit', 'sku' => 'PLB-5002', 'description' => 'Assorted compression fittings for copper pipe.', 'category' => 'Plumbing', 'quantity' => 3, 'reorder_level' => 10, 'unit_price' => 17.99, 'supplier' => 'FlowRight Supply'],

            // Paint
            ['name' => 'Interior Latex Paint (1 Gallon)', 'sku' => 'PNT-6001', 'description' => 'Satin finish, white base.', 'category' => 'Paint', 'quantity' => 40, 'reorder_level' => 15, 'unit_price' => 28.99, 'supplier' => 'ColorCraft Paints'],
            ['name' => 'Paint Roller Kit', 'sku' => 'PNT-6002', 'description' => 'Includes roller frame, 2 covers, and tray.', 'category' => 'Paint', 'quantity' => 25, 'reorder_level' => 10, 'unit_price' => 9.99, 'supplier' => 'ColorCraft Paints'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}