<?php

namespace App\Http\Controllers;

use App\Models\DailyStockActivity;
use Illuminate\Http\Request;
use App\Models\SalesHistory;
use App\Models\ProductsList;


class DataAnalyticsController extends Controller
{
    public function calculateAnalytics(){
        $soldProductAnalytics = $this->getProductSoldProductAnalytics();
        $discardedProductAnalytics = $this->getDiscardedProductAnalytics();
        $perProductAnalytics = $this->getPerProductAnalytics();

        return response()->json([
            'sold_product_analytics' => $soldProductAnalytics,
            'discarded_product_analytics' => $discardedProductAnalytics,
            'per_product_analytics' => $perProductAnalytics
        ]);
    }

    private function getProductSoldProductAnalytics()
    {
        $today = now();
        $startOfThisWeek = $today->copy()->subDays(6)->startOfDay(); // 7 days including today
        $startOfLastWeek = $startOfThisWeek->copy()->subDays(7)->startOfDay();
        $endOfLastWeek = $startOfThisWeek->copy()->subDay()->endOfDay();

        // SALES HISTORY - Profit-based analytics
        $thisWeekSales = SalesHistory::whereBetween('created_at', [$startOfThisWeek, $today])
            ->selectRaw('product_id, SUM(quantity_sold) as total_sold')
            ->groupBy('product_id')
            ->get();

        $lastWeekSales = SalesHistory::whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])
            ->selectRaw('product_id, SUM(quantity_sold) as total_sold')
            ->groupBy('product_id')
            ->get();

        // DAILY STOCK ACTIVITY - Quantity analytics
        $thisWeekSalesQuantity = DailyStockActivity::whereBetween('created_at', [$startOfThisWeek, $today])
            ->where('activity_type', 'sold')
            ->selectRaw('product_id, SUM(quantity) as total_sold')
            ->groupBy('product_id')
            ->get();

        $lastWeekSalesQuantity = DailyStockActivity::whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])
            ->where('activity_type', 'sold')
            ->selectRaw('product_id, SUM(quantity) as total_sold')
            ->groupBy('product_id')
            ->get();

        $sumOfSoldThisWeek = $thisWeekSales->sum('total_sold');
        $sumOfSoldLastWeek = $lastWeekSales->sum('total_sold');


        $profitAnalytics = [];
        foreach ($thisWeekSales as $thisSale) {
            $lastSale = $lastWeekSales->firstWhere('product_id', $thisSale->product_id);
            $lastSold = $lastSale ? $lastSale->total_sold : 0;
            $difference = $thisSale->total_sold - $lastSold;

            $percentChange = $lastSold > 0 ? ($difference / $lastSold) * 100 : ($thisSale->total_sold > 0 ? 100 : 0);
            $trend = $difference > 0 ? 'increase' : ($difference < 0 ? 'decrease' : 'no_change');

            $profitAnalytics[] = [
                'product' => ProductsList::where('product_id', $thisSale->product_id)->first(),
                'this_week_sold' => $thisSale->total_sold,
                'last_week_sold' => $lastSold,
                'sum_last_week' => $sumOfSoldLastWeek,
                'difference' => $difference,
                'percent_change' => round($percentChange, 2),
                'trend' => $trend
            ];
        }

        $quantityAnalytics = [];
        foreach ($thisWeekSalesQuantity as $thisSale) {
            $lastSale = $lastWeekSalesQuantity->firstWhere('product_id', $thisSale->product_id);
            $lastSold = $lastSale ? $lastSale->total_sold : 0;
            $difference = $thisSale->total_sold - $lastSold;

            $percentChange = $lastSold > 0 ? ($difference / $lastSold) * 100 : ($thisSale->total_sold > 0 ? 100 : 0);
            $trend = $difference > 0 ? 'increase' : ($difference < 0 ? 'decrease' : 'no_change');

            $quantityAnalytics[] = [
                'product' => ProductsList::where('product_id', $thisSale->product_id)->first(),
                'this_week_sold' => $thisSale->total_sold,
                'last_week_sold' => $lastSold,
                'difference' => $difference,
                'percent_change' => round($percentChange, 2),
                'trend' => $trend
            ];
        }

        return [
            'date_range_this_week' => [$startOfThisWeek->toDateString(), $today->toDateString()],
            'date_range_last_week' => [$startOfLastWeek->toDateString(), $endOfLastWeek->toDateString()],
            'this_week_sales' => $thisWeekSales,
            'sum_this_week' => $sumOfSoldThisWeek,
            'last_week_sales' => $lastWeekSales,
            'sum_last_week' => $sumOfSoldLastWeek,
            'profit_analytics' => $profitAnalytics,
            'quantity_analytics' => $quantityAnalytics
        ];
    }


    private function getDiscardedProductAnalytics()
    {
        $today = now();
        $startOfThisWeek = $today->copy()->subDays(6)->startOfDay(); // This week = last 7 days
        $startOfLastWeek = $startOfThisWeek->copy()->subDays(7)->startOfDay();
        $endOfLastWeek = $startOfThisWeek->copy()->subDay()->endOfDay();

        $thisWeekDiscarded = DailyStockActivity::whereBetween('created_at', [$startOfThisWeek, $today])
            ->where('activity_type', 'discarded')
            ->selectRaw('product_id, SUM(quantity) as total_discarded')
            ->groupBy('product_id')
            ->get();

        $lastWeekDiscarded = DailyStockActivity::whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])
            ->where('activity_type', 'discarded')
            ->selectRaw('product_id, SUM(quantity) as total_discarded')
            ->groupBy('product_id')
            ->get();

        $analytics = [];
        $totalThisWeekDiscarded = $thisWeekDiscarded->sum('total_discarded');
        $totalLastWeekDiscarded = $lastWeekDiscarded->sum('total_discarded');

        foreach ($thisWeekDiscarded as $thisDiscard) {
            $lastDiscard = $lastWeekDiscarded->firstWhere('product_id', $thisDiscard->product_id);
            $lastDiscardedQuantity = $lastDiscard ? $lastDiscard->total_discarded : 0;
            $difference = $thisDiscard->total_discarded - $lastDiscardedQuantity;

            $percentChange = $lastDiscardedQuantity > 0
                ? ($difference / $lastDiscardedQuantity) * 100
                : ($thisDiscard->total_discarded > 0 ? 100 : 0);

            $trend = $difference > 0 ? 'increase' : ($difference < 0 ? 'decrease' : 'no_change');

            $analytics[] = [
                'product' => ProductsList::where('product_id', $thisDiscard->product_id)->first(),
                'this_week_discarded' => $thisDiscard->total_discarded,
                'last_week_discarded' => $lastDiscardedQuantity,
                'difference' => $difference,
                'percent_change' => round($percentChange, 2),
                'trend' => $trend
            ];
        }

        $totalDifference = $totalThisWeekDiscarded - $totalLastWeekDiscarded;
        $totalPercentChange = $totalLastWeekDiscarded > 0
            ? ($totalDifference / $totalLastWeekDiscarded) * 100
            : ($totalThisWeekDiscarded > 0 ? 100 : 0);

        return [
            'date_range_this_week' => [$startOfThisWeek->toDateString(), $today->toDateString()],
            'date_range_last_week' => [$startOfLastWeek->toDateString(), $endOfLastWeek->toDateString()],
            'total_this_week_discarded' => $totalThisWeekDiscarded,
            'total_last_week_discarded' => $totalLastWeekDiscarded,
            'total_difference' => $totalDifference,
            'total_percent_change' => round($totalPercentChange, 2),
            'analytics' => $analytics,
        ];
    }


    private function getPerProductAnalytics(){
        return ProductsList::all();
    }
}
