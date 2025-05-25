<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockBatches;
use App\Models\DailyStockActivity;
use App\Models\ProductsList;
use Illuminate\Support\Str;


class ProductController extends Controller
{
    public function createProduct(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
        ]);

        $productID =  'prod-' . Str::uuid();

        ProductsList::create([
            'product_id' => $productID,
            'product_name' => $request->input('product_name'),
        ]);

        return response()->json([
            'message' => 'Product created successfully.',
            'product_id' => $productID,
            'product_name' => $request->input('product_name')
        ], 201);

    }


    public function createBatch(Request $request)
    {
        $request->validate([
            'products_id' => 'required|array',
            'products_id.*' => 'required|string|max:255',
            'price' => 'required|array',
            'price.*' => 'required|numeric',
            'quantity' => 'required|array',
            'quantity.*' => 'required|integer|min:1',
        ]);

        $batchId = 'batch_' . uniqid();
        $products = [];

        $productIds = $request->input('products_id');
        $prices = $request->input('price');
        $quantities = $request->input('quantity');

        if (count($productIds) !== count($prices) || count($prices) !== count($quantities)) {
            return response()->json(['error' => 'Input arrays must be of the same length.'], 422);
        }

        for ($i = 0; $i < count($productIds); $i++) {
            $productId = $productIds[$i];

            // Get product name from ProductsList
            $product = ProductsList::where('product_id', $productId)->first();
            if (!$product) {
                return response()->json(['error' => "Product with ID {$productId} not found."], 404);
            }
            $productName = $product->product_name;

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


    public function outProductsQuantity(){
    
    }

    public function backProductsQuantity(){

    }



    // This Totals all without the outProducts and backProducts
    public function viewProductsQuantity()
    {
        $products = StockBatches::select('product_id', 'product_name', 'remaining_quantity')
            ->get()
            ->groupBy('product_id')
            ->map(function ($items) {
            $first = $items->first();
            $totalQuantity = $items->sum('remaining_quantity');
            return [
                'product_id' => $first->product_id,
                'product_name' => $first->product_name,
                'remaining_quantity' => (string)$totalQuantity,
            ];
            })
            ->values();

        return response()->json($products);
    }
}
