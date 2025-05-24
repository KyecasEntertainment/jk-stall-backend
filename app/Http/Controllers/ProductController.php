<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockBatches;
use Illuminate\Support\Str;


class ProductController extends Controller
{

    public function createProduct(Request $request)
    {
        $request->validate([
            'products_name' => 'required|array',
            'products_name.*' => 'required|string|max:255',
            'price' => 'required|array',
            'price.*' => 'required|numeric',
            'quantity' => 'required|array',
            'quantity.*' => 'required|integer|min:1',
        ]);

        $batchId = 'batch_' . uniqid();
        $products = [];

        $names = $request->input('products_name');
        $prices = $request->input('price');
        $quantities = $request->input('quantity');

        if (count($names) !== count($prices) || count($prices) !== count($quantities)) {
            return response()->json(['error' => 'Input arrays must be of the same length.'], 422);
        }

        for ($i = 0; $i < count($names); $i++) {
            $productName = $names[$i];

            // Check if product name exists in any previous batch
            $existing = StockBatches::where('product_name', $productName)->first();

            if ($existing) {
                $productId = $existing->product_id;
            } else {
                $productId = 'prod-' . Str::uuid(); // or use uniqid() if preferred
            }

            try {
                StockBatches::create([
                    'batch_id' => $batchId,
                    'product_id' => $productId,
                    'product_name' => $productName,
                    'remaining_quantity' => $quantities[$i],
                    'unit_cost' => $prices[$i],
                    'received_at' => now(),
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }


            $products[] = [
                'product_id' => $productId,
                'product_name' => $productName,
                'price' => (string)$prices[$i],
                'quantity' => (string)$quantities[$i],
            ];
        }

        return response()->json([
            'batch_id' => $batchId,
            'products' => $products,
        ]);
    }


    public function viewProducts()
    {
        $batchId = 'batch_' . uniqid();

        return response()->json([
            'batch_id' => $batchId,
        ]);
    }

    public function addNewStockProduct(){

    }

    public function outProductsQuantity(){

    }

    public function backProductsQuantity(){

    }
}
