<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockBatches;
use App\Models\DailyStockActivity;
use App\Models\TotalProductQuantity;
use App\Models\ProductsList;
use App\Models\SalesHistory;
use Illuminate\Support\Str;


class ProductController extends Controller
{
    public function createProduct(Request $request)
    {
        $request->validate([
            'product_name' => 'required|array',
            'product_name.*' => 'required|string|max:255',
        ]);

        $createdProducts = [];
        $errors = [];

        foreach ($request->input('product_name') as $name) {
            $existingProduct = ProductsList::where('product_name', $name)->first();
            if ($existingProduct) {
                $errors[] = [
                    'product_name' => $name,
                    'error' => 'Product name already exists.'
                ];
                continue;
            }

            $productID = 'prod-' . Str::uuid();

            ProductsList::create([
                'product_id' => $productID,
                'product_name' => $name,
            ]);

            $createdProducts[] = [
                'product_id' => $productID,
                'product_name' => $name
            ];
        }

        $response = [
            'message' => 'Product creation completed.',
            'created_products' => $createdProducts,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, empty($createdProducts) ? 409 : 201);
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
            'new_product_name' => 'required|string|max:255',
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

            $product->product_name = $request->input('new_product_name');
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

    public function deleteProduct($id)
    {
        try {
            $product = ProductsList::where('product_id', $id)->first();
            if (!$product) {
                return response()->json(['error' => 'Product not found.'], 404);
            }

            // Check if there are any batches associated with this product
            if (StockBatches::where('product_id', $id)->exists()) {
                return response()->json(['error' => 'Cannot delete product with existing stock batches.'], 400);
            }

            $product->delete();

            return response()->json(['message' => 'Product deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createBatch(Request $request)
    {
        $request->validate([
            'products_id' => 'required|array',
            'products_id.*' => 'required|string|max:255',
            'original_price' => 'required|array',
            'original_price.*' => 'required|numeric',
            'selling_price' => 'required|array',
            'selling_price.*' => 'required|numeric',
            'quantity' => 'required|array',
            'quantity.*' => 'required|integer|min:1',
        ]);

        $batchId = 'batch_' . date('mdY') . '_' . uniqid();
        $products = [];

        $productIds = $request->input('products_id');
        $originalPrices = $request->input('original_price');
        $sellingPrices = $request->input('selling_price');
        $quantities = $request->input('quantity');

        if (
            count($productIds) !== count($originalPrices) ||
            count($originalPrices) !== count($sellingPrices) ||
            count($sellingPrices) !== count($quantities)
        ) {
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
                    'original_cost' => $originalPrices[$i],
                    'selling_cost' => $sellingPrices[$i],
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
                    'original_cost' => (string)$originalPrices[$i] . ' Php',
                    'selling_cost' => (string)$sellingPrices[$i] . ' Php',
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
                'original_cost' => (string)$batch->original_cost . ' Php',
                'selling_cost' => (string)$batch->selling_cost . ' Php',
                'received_at' => $batch->received_at->toDateTimeString(),
            ];
        });

        return response()->json($batches);
    }

    // Update batch information
    public function updateBatch(Request $request, $batch_id, $product_id)
    {
        $request->validate([
            'original_cost' => 'required|numeric',
            'selling_cost' => 'required|numeric',
            'entry_quantity' => 'required|integer|min:1',
        ]);

        try {
            $batch = StockBatches::where('batch_id', $batch_id)->first();
            if (!$batch) {
                return response()->json(['error' => 'Batch not found.'], 404);
            }

            // Check if product exists
            $product = ProductsList::where('product_id', $product_id)->first();
            if (!$product) {
                return response()->json(['error' => 'Product not found.'], 404);
            }

            // Update batch details
            $batch->product_id = $request->input('product_id');
            $batch->product_name = $product->product_name;
            $batch->original_cost = $request->input('original_cost');
            $batch->selling_cost = $request->input('selling_cost');
            $batch->entry_quantity = $request->input('entry_quantity');
            $batch->save();

            return response()->json([
                'message' => 'Batch updated successfully.',
                'batch' => [
                    'batch_id' => $batch->batch_id,
                    'product_id' => $batch->product_id,
                    'product_name' => $batch->product_name,
                    'entry_quantity' => (string)$batch->entry_quantity,
                    'original_cost' => (string)$batch->original_cost . ' Php',
                    'selling_cost' => (string)$batch->selling_cost . ' Php',
                    'received_at' => $batch->received_at->toDateTimeString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // Delete a batch
    public function deleteBatch($id)
    {
        try {
            $batch = StockBatches::where('batch_id', $id)->first();
            if (!$batch) {
                return response()->json(['error' => 'Batch not found.'], 404);
            }

            // Update the total quantities in TotalProductQuantity
            $totalProduct = TotalProductQuantity::where('product_id', $batch->product_id)->first();
            if ($totalProduct) {
                $totalProduct->all_total_quantity -= $batch->entry_quantity;
                $totalProduct->current_total_quantity -= $batch->entry_quantity;
                $totalProduct->save();
            }

            $batch->delete();

            return response()->json(['message' => 'Batch deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // Delete a product from a batch
    public function deleteProductFromBatch($id)
    {
        try {
            $batch = StockBatches::where('product_id', $id)->first();
            if (!$batch) {
                return response()->json(['error' => 'Product not found in any batch.'], 404);
            }

            // Update the total quantities in TotalProductQuantity
            $totalProduct = TotalProductQuantity::where('product_id', $id)->first();
            if ($totalProduct) {
                $totalProduct->all_total_quantity -= $batch->entry_quantity;
                $totalProduct->current_total_quantity -= $batch->entry_quantity;
                $totalProduct->save();
            }

            $batch->delete();

            return response()->json(['message' => 'Product deleted from batch successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

        $activityId = Str::uuid();
        $activityType = 'display';
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

                // Record activity using new model fields
                DailyStockActivity::create([
                    'activity_id' => $activityId,
                    'activity_type' => $activityType,
                    'product_id' => $productId,
                    'quantity' => $displayQty,
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

        $activityId = Str::uuid();
        $activityType = 'return';
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

                // Record activity using new model fields
                DailyStockActivity::create([
                    'activity_id' => $activityId,
                    'activity_type' => $activityType,
                    'product_id' => $productId,
                    'date' => now(),
                    'quantity' => $returnQty,
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

    public function discardedProductQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|array',
            'product_id.*' => 'required|string|max:255',
            'discarded_quantity' => 'required|array',
            'discarded_quantity.*' => 'required|integer|min:0',
        ]);

        $activityId = Str::uuid();
        $activityType = 'discarded';
        $productIds = $request->input('product_id');
        $discardedQuantities = $request->input('discarded_quantity');

        if (count($productIds) !== count($discardedQuantities)) {
            return response()->json(['error' => 'Input arrays must be of the same length.'], 422);
        }

        $discarded = [];

        try {
            for ($i = 0; $i < count($productIds); $i++) {
                $productId = $productIds[$i];
                $discardQty = $discardedQuantities[$i];

                $discarded[] = [
                    'product_id' => $productId,
                    'discarded_quantity' => (string)$discardQty,
                ];

                $productQty = TotalProductQuantity::where('product_id', $productId)->first();

                if (!$productQty) {
                    return response()->json([
                        'error' => "Product {$productId} not found."
                    ], 404);
                }

                if ($discardQty > $productQty->current_total_quantity) {
                    return response()->json([
                        'error' => "Discarded quantity for product {$productId} exceeds current available quantity."
                    ], 400);
                }

                // Update the total quantities
                $productQty->total_displayed_quantity -= $discardQty;
                $productQty->total_discarded_quantity = isset($productQty->total_discarded_quantity) 
                    ? $productQty->total_discarded_quantity + $discardQty 
                    : $discardQty;
                // $productQty->all_total_quantity -= $discardQty;
                $productQty->save();

                // Record activity
                DailyStockActivity::create([
                    'activity_id' => $activityId,
                    'activity_type' => $activityType,
                    'product_id' => $productId,
                    'date' => now(),
                    'quantity' => $discardQty,
                    'notes' => null, // No notes for discarded products
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'activity_id' => $activityId,
            'discarded_products' => $discarded,
        ]);
        
    }

    public function replaceDiscardedProductsQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|array',
            'product_id.*' => 'required|string|max:255',
            'replaced_quantity' => 'required|array',
            'replaced_quantity.*' => 'required|integer|min:0',
        ]);

        $activityId = Str::uuid();
        $activityType = 'replaced';
        $productIds = $request->input('product_id');
        $replacedQuantities = $request->input('replaced_quantity');

        if (count($productIds) !== count($replacedQuantities)) {
            return response()->json(['error' => 'Input arrays must be of the same length.'], 422);
        }

        $replaced = [];

        try {
            for ($i = 0; $i < count($productIds); $i++) {
                $productId = $productIds[$i];
                $replaceQty = $replacedQuantities[$i];

                $replaced[] = [
                    'product_id' => $productId,
                    'replaced_quantity' => (string)$replaceQty,
                ];

                // Find the product quantity record
                $productQty = TotalProductQuantity::where('product_id', $productId)->first();

                if (!$productQty) {
                    return response()->json([
                        'error' => "Product {$productId} not found."
                    ], 404);
                }

                // Check if replaced quantity does not exceed total_discarded_quantity
                if (isset($productQty->total_discarded_quantity) && $replaceQty > $productQty->total_discarded_quantity) {
                    return response()->json([
                        'error' => "Replaced quantity for product {$productId} exceeds total discarded quantity."
                    ], 400);
                }

                // Update the total quantities
                $productQty->current_total_quantity += $replaceQty;
                if (isset($productQty->total_discarded_quantity)) {
                    $productQty->total_discarded_quantity = max(0, $productQty->total_discarded_quantity - $replaceQty);
                }
                $productQty->save();

                // Record activity
                DailyStockActivity::create([
                    'activity_id' => $activityId,
                    'activity_type' => $activityType,
                    'product_id' => $productId,
                    'quantity' => $replaceQty,
                    'date' => now(),
                    'notes' => null, // No notes for replaced products
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'activity_id' => $activityId,
            'replaced_products' => $replaced,
        ]);
    }

    // This Totals all without the outProducts and backProducts
    public function viewProductsQuantity()
    {
        $products = TotalProductQuantity::with('product')
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => optional($item->product)->product_name,
                    'current_total_quantity' => (string)$item->current_total_quantity,
                ];
            });

        return response()->json($products);
    }

}
