<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TotalProductQuantity;
use App\Models\StockBatches;
use App\Models\SalesHistory;
use App\Models\DailyStockActivity;
use Illuminate\Support\Str;

class CalculationController extends Controller
{
    public function calculateSoldProductsToday()
    {
        $products = TotalProductQuantity::where('total_displayed_quantity', '>', 0)->get();

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found to calculate.'], 404);
        }

        $result = [];
        $totalSum = 0;
        $totalProfit = 0;

        foreach ($products as $product) {
            $latestBatch = StockBatches::where('product_id', $product->product_id)
                ->orderByDesc('received_at')
                ->first();

            $originalCost = $latestBatch ? $latestBatch->original_cost : 0;
            $sellingCost = $latestBatch ? $latestBatch->selling_cost : 0;
            $soldQty = $product->total_displayed_quantity;
            $subtotal = $soldQty * $sellingCost;
            $profit = $soldQty * ($sellingCost - $originalCost);

            // Log to sales_histories table
            SalesHistory::create([
                'sale_id' => Str::uuid(),
                'product_id' => $product->product_id,
                'quantity_sold' => $subtotal,
            ]);
            DailyStockActivity::create([
                'activity_id' => Str::uuid(),
                'activity_type' => 'sold',
                'product_id' => $product->product_id,
                'quantity' => $soldQty,
                'notes' => null, // No notes for sold products
            ]);

            // Reset total_displayed_quantity to 0 after calculation
            $product->sold_quantity = $soldQty;
            $product->total_displayed_quantity = 0;
            $product->save();

            $result[] = [
                'product_id' => $product->product_id,
                'product_name' => $latestBatch ? $latestBatch->product_name : null,
                'sold_quantity' => $soldQty,
                'original_cost' => $originalCost,
                'selling_cost' => $sellingCost,
                'subtotal' => $subtotal,
                'profit' => $profit,
            ];

            $totalSum += $subtotal;
            $totalProfit += $profit;
        }

        return response()->json([
            'products' => $result,
            'total_sum' => $totalSum,
            'total_profit' => $totalProfit,
        ]);
    }


    // Not used currently, but can be used for future profit calculations

    public function calculateProfit(Request $request){
        $monthSelected = $request->input('month_selected');
        $yearSelected = $request->input('year_selected');

        if (empty($monthSelected) || empty($yearSelected)) {
            $previousMonth = now()->subMonth();
            $monthSelected = $previousMonth->format('m');
            $yearSelected = $previousMonth->format('Y');
        }
        //compare to this month and year
        $month = now()->format('m');
        $year = now()->format('Y');

        //compare to this month and year to selected month and year

        $sales = SalesHistory::whereMonth('created_at', $monthSelected)
            ->whereYear('created_at', $yearSelected)
            ->get();
        $profit = 0;
        foreach ($sales as $sale) {
            $batch = StockBatches::where('product_id', $sale->product_id)
                ->orderByDesc('received_at')
                ->first();
            if ($batch) {
                $profit += ($batch->selling_cost - $batch->original_cost) * $sale->quantity_sold;
            }
        }
        return response()->json([
            'month' => $monthSelected,
            'year' => $yearSelected,
            'profit' => $profit,
            'current_month' => $month,
            'current_year' => $year
        ]);
    }

    public function testRequest(Request $request)
    {
        // Example of processing a request
        $data = $request->all();

        // Perform some operations with the data
        // For example, you can log it or return it as a response
        return response()->json([
            'message' => 'Test request received successfully.',
            'data' => $data,
        ]);
    }
}
