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

    private function getProductSoldProductAnalytics(){

        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        $todaySales = SalesHistory::whereDate('created_at', $today)
            ->selectRaw('product_id, SUM(quantity_sold) as total_sold')
            ->groupBy('product_id')
            ->get();
        $todaySalesQuantity = DailyStockActivity::whereDate('created_at', $today)
            ->where('activity_type', 'sold')
            ->selectRaw('product_id, SUM(quantity) as total_sold')
            ->groupBy('product_id')
            ->get();    

        $yesterdaySales = SalesHistory::whereDate('created_at', $yesterday)
            ->selectRaw('product_id, SUM(quantity_sold) as total_sold')
            ->groupBy('product_id')
            ->get();
        $yesterdaySalesQuantity = DailyStockActivity::whereDate('created_at', $yesterday)
            ->where('activity_type', 'sold')
            ->selectRaw('product_id, SUM(quantity) as total_sold')
            ->groupBy('product_id')
            ->get();

        $profitAnalytics = [];
        foreach ($todaySales as $todaySale) {
            $yesterdaySale = $yesterdaySales->firstWhere('product_id', $todaySale->product_id);
            $yesterdaySold = $yesterdaySale ? $yesterdaySale->total_sold : 0;
            $difference = $todaySale->total_sold - $yesterdaySold;

            if ($yesterdaySold > 0) {
                $percentChange = ($difference / $yesterdaySold) * 100;
            } else {
                $percentChange = $todaySale->total_sold > 0 ? 100 : 0;
            }

            $trend = $difference > 0 ? 'increase' : ($difference < 0 ? 'decrease' : 'no_change');

            $profitAnalytics[] = [
                'product' => ProductsList::where('product_id', $todaySale->product_id)->first(),
                'today_sold' => $todaySale->total_sold,
                'yesterday_sold' => $yesterdaySold,
                'difference' => $difference,
                'percent_change' => round($percentChange, 2),
                'trend' => $trend
            ];
        }

        $quantityAnalytics = [];
        foreach ($todaySalesQuantity as $todaySale) {
            $yesterdaySale = $yesterdaySalesQuantity->firstWhere('product_id', $todaySale->product_id);
            $yesterdaySold = $yesterdaySale ? $yesterdaySale->total_sold : 0;
            $difference = $todaySale->total_sold - $yesterdaySold;

            if ($yesterdaySold > 0) {
                $percentChange = ($difference / $yesterdaySold) * 100;
            } else {
                $percentChange = $todaySale->total_sold > 0 ? 100 : 0;
            }

            $trend = $difference > 0 ? 'increase' : ($difference < 0 ? 'decrease' : 'no_change');

            $quantityAnalytics[] = [
                'product' => ProductsList::where('product_id', $todaySale->product_id)->first(),
                'today_sold' => $todaySale->total_sold,
                'yesterday_sold' => $yesterdaySold,
                'difference' => $difference,
                'percent_change' => round($percentChange, 2),
                'trend' => $trend
            ];
        }

        return [
            'today' => $today,
            // 'today_sales' => $todaySales,
            'yesterday' => $yesterday,
            // 'yesterday_sales' => $yesterdaySales,
            'profit_analytics' => $profitAnalytics,
            'quantity_analytics' => $quantityAnalytics
        ];
    }

    private function getDiscardedProductAnalytics(){
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        $todayDiscarded = DailyStockActivity::whereDate('created_at', $today)
            ->where('activity_type', 'discarded')
            ->selectRaw('product_id, SUM(quantity) as total_discarded')
            ->groupBy('product_id')
            ->get();
        $yesterdayDiscarded = DailyStockActivity::whereDate('created_at', $yesterday)
            ->where('activity_type', 'discarded')
            ->selectRaw('product_id, SUM(quantity) as total_discarded')
            ->groupBy('product_id')
            ->get();

        $analytics = [];
        $totalTodayDiscarded = 0;
        $totalYesterdayDiscarded = 0;

        foreach ($todayDiscarded as $todayDiscard) {
            $yesterdayDiscard = $yesterdayDiscarded->firstWhere('product_id', $todayDiscard->product_id);
            $yesterdayDiscardedQuantity = $yesterdayDiscard ? $yesterdayDiscard->total_discarded : 0;
            $difference = $todayDiscard->total_discarded - $yesterdayDiscardedQuantity;

            if ($yesterdayDiscardedQuantity > 0) {
                $percentChange = ($difference / $yesterdayDiscardedQuantity) * 100;
            } else {
                $percentChange = $todayDiscard->total_discarded > 0 ? 100 : 0;
            }

            $trend = $difference > 0 ? 'increase' : ($difference < 0 ? 'decrease' : 'no_change');

            $analytics[] = [
                'product' => ProductsList::where('product_id', $todayDiscard->product_id)->first(),
                'today_discarded' => $todayDiscard->total_discarded,
                'yesterday_discarded' => $yesterdayDiscardedQuantity,
                'difference' => $difference,
                'percent_change' => round($percentChange, 2),
                'trend' => $trend
            ];

            $totalTodayDiscarded += $todayDiscard->total_discarded;
        }

        // Sum up all yesterday's discarded quantities (including products not discarded today)
        foreach ($yesterdayDiscarded as $yesterdayDiscard) {
            $totalYesterdayDiscarded += $yesterdayDiscard->total_discarded;
        }

        $totalDifference = $totalTodayDiscarded - $totalYesterdayDiscarded;
        if ($totalYesterdayDiscarded > 0) {
            $totalPercentChange = ($totalDifference / $totalYesterdayDiscarded) * 100;
        } else {
            $totalPercentChange = $totalTodayDiscarded > 0 ? 100 : 0;
        }

        return [
            'today' => $today,
            'yesterday' => $yesterday,
            'total_today_discarded' => $totalTodayDiscarded,
            'total_yesterday_discarded' => $totalYesterdayDiscarded,
            'total_difference' => $totalDifference,
            'total_percent_change' => round($totalPercentChange, 2),
            'analytics' => $analytics,
        ];
    }

    private function getPerProductAnalytics(){
        return ProductsList::all();
    }
}
