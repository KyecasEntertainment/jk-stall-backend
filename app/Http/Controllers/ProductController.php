<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockBatches;
use App\Models\DailyStockActivity;
use App\Models\TotalProductQuantity;
use App\Models\ProductsList;
use Illuminate\Support\Str;


class ProductController extends Controller
{
    public function createProduct(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
        ]);

        // Check if product name already exists
        $existingProduct = ProductsList::where('product_name', $request->input('product_name'))->first();
        if ($existingProduct) {
            return response()->json([
                'error' => 'Product name already exists.'
            ], 409);
        }

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

    public function viewProducts(){
        $products = ProductsList::all()->map(function ($product) {
            return [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
            ];
        });

        return response()->json($products);
    }

    public function updateProductName(Request $request, $id)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
        ]);

        try {
            $product = ProductsList::where('product_id', $id)->first();
            if (!$product) {
                return response()->json(['error' => 'Product not found.'], 404);
            }

            // Check if new product name already exists (excluding current product)
            $existingProduct = ProductsList::where('product_name', $request->input('product_name'))
                ->where('product_id', '!=', $product->product_id)
                ->first();
            if ($existingProduct) {
                return response()->json(['error' => 'Product name already exists.'], 409);
            }

            $product->product_name = $request->input('product_name');
            $product->save();

            return response()->json([
                'message' => 'Product name updated successfully.',
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

        $batchId = 'batch_' . date('mdY') . '_' . uniqid();
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
                    'entry_quantity' => $quantities[$i],
                    'unit_cost' => $prices[$i],
                    'received_at' => now(),
                ]);

                $totalProduct = TotalProductQuantity::where('product_id', $productId)->first();

                if ($totalProduct) {
                    $totalProduct->all_total_quantity += $quantities[$i];
                    $totalProduct->current_total_quantity += $quantities[$i];
                    $totalProduct->save();
                } else {
                    TotalProductQuantity::create([
                        'product_id' => $productId,
                        'all_total_quantity' => $quantities[$i],
                        'current_total_quantity' => $quantities[$i],
                    ]);
                }

                $products[] = [
                    'product_id' => $productId,
                    'product_name' => $productName,
                    'price' => (string)$prices[$i] . ' Pesos',
                    'quantity' => (string)$quantities[$i],
                ];
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }


        return response()->json([
            'batch_id' => $batchId,
            'products' => $products,
        ]);
    }

    public function viewBatches()
    {
        $batches = StockBatches::all()->map(function ($batch) {
            return [
                'batch_id' => $batch->batch_id,
                'product_id' => $batch->product_id,
                'product_name' => $batch->product_name,
                'entry_quantity' => (string)$batch->entry_quantity,
                'unit_cost' => (string)$batch->unit_cost,
                'received_at' => $batch->received_at->toDateTimeString(),
            ];
        });

        return response()->json($batches);
    }

    // Fix notes not being saved on the database
    public function displayProductsQuantity(Request $request){
        $request->validate([
            'product_id' => 'required|array',
            'product_id.*' => 'required|string|max:255',
            'displayed_quantity' => 'required|array',
            'displayed_quantity.*' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $activityId = 'display_' . Str::uuid();
        $productIds = $request->input('product_id');
        $displayedQuantities = $request->input('displayed_quantity');
        $notes = $request->input('notes', null);

        $displayed = [];

        try {
            for ($i = 0; $i < count($productIds); $i++) {
                $productId = $productIds[$i];
                $displayQty = $displayedQuantities[$i];

                $displayed[] = [
                    'product_id' => $productId,
                    'displayed_quantity' => (string)$displayQty,
                ];

                $productQty = TotalProductQuantity::where('product_id', $productId)->first();

                if (!$productQty) {
                    return response()->json([
                        'error' => "Product {$productId} not found or there's no data for it."
                    ], 404);
                }

                if ($displayQty > $productQty->current_total_quantity) {
                    return response()->json([
                        'error' => "Displayed quantity for product {$productId} exceeds current available quantity."
                    ], 400);
                }

                $productQty->current_total_quantity = max(0, $productQty->current_total_quantity - $displayQty);
                $productQty->total_displayed_quantity += $displayQty;
                $productQty->save();

                // Record activity
                DailyStockActivity::create([
                    'activity_id' => $activityId,
                    'product_id' => $productId,
                    'date' => now(),
                    'displayed_quantity' => $displayQty,
                    'back_quantity' => 0,
                    'notes' => $notes,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'activity_id' => $activityId,
            'displayed' => $displayed,
            'notes' => $notes,
        ]);
    }

    // Fix notes not being saved on the database
    public function returnProductsQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|array',
            'product_id.*' => 'required|string|max:255',
            'returned_quantity' => 'required|array',
            'returned_quantity.*' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $activityId = 'back_' . Str::uuid();
        $productIds = $request->input('product_id');
        $returnedQuantities = $request->input('returned_quantity');
        $notes = $request->input('notes', null);

        if (count($productIds) !== count($returnedQuantities)) {
            return response()->json(['error' => 'Input arrays must be of the same length.'], 422);
        }

        $returned = [];

        try {
            for ($i = 0; $i < count($productIds); $i++) {
                $productId = $productIds[$i];
                $returnQty = $returnedQuantities[$i];

                $returned[] = [
                    'product_id' => $productId,
                    'returned_quantity' => (string)$returnQty,
                ];

                $productQty = TotalProductQuantity::where('product_id', $productId)->first();

                if (!$productQty) {
                    return response()->json([
                        'error' => "Product {$productId} not found."
                    ], 404);
                }

                if ($returnQty > $productQty->total_displayed_quantity) {
                    return response()->json([
                        'error' => "Returned quantity for product {$productId} exceeds displayed quantity."
                    ], 400);
                }

                $productQty->current_total_quantity += $returnQty;
                $productQty->total_displayed_quantity = max(0, $productQty->total_displayed_quantity - $returnQty);
                $productQty->save();

                // Record activity
                DailyStockActivity::create([
                    'activity_id' => $activityId,
                    'product_id' => $productId,
                    'date' => now(),
                    'displayed_quantity' => 0,
                    'back_quantity' => $returnQty,
                    'notes' => $notes,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'activity_id' => $activityId,
            'returned' => $returned,
            'notes' => $notes,
        ]);
    }

    // This Totals all without the outProducts and backProducts
    public function viewProductsQuantity()
    {
        $products = StockBatches::select('product_id', 'product_name', 'entry_quantity')
            ->get()
            ->groupBy('product_id')
            ->map(function ($items) {
            $first = $items->first();
            $totalQuantity = $items->sum('entry_quantity');
            return [
                'product_id' => $first->product_id,
                'product_name' => $first->product_name,
                'entry_quantity' => (string)$totalQuantity,
            ];
            })
            ->values();

        return response()->json($products);
    }

    public function calculateSoldProductsToday()
    {
        $products = TotalProductQuantity::where('total_displayed_quantity', '>', 0)->get();

        $result = [];
        $totalSum = 0;

        foreach ($products as $product) {
            $latestBatch = StockBatches::where('product_id', $product->product_id)
                ->orderByDesc('received_at')
                ->first();

            $unitCost = $latestBatch ? $latestBatch->unit_cost : 0;
            $soldQty = $product->total_displayed_quantity;
            $subtotal = $soldQty * $unitCost;

            // Reset total_displayed_quantity to 0 after calculation
            $product->sold_quantity = $soldQty;
            $product->total_displayed_quantity = 0;
            $product->save();

            $result[] = [
                'product_id' => $product->product_id,
                'product_name' => $latestBatch ? $latestBatch->product_name : null,
                'sold_quantity' => $soldQty,
                'unit_cost' => $unitCost,
                'subtotal' => $subtotal,
            ];

            $totalSum += $subtotal;
        }

        return response()->json([
            'products' => $result,
            'total_sum' => $totalSum,
        ]);
    }
}
