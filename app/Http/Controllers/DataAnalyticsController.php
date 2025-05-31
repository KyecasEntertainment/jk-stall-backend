<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataAnalyticsController extends Controller
{
    public function calculateAnalytics(){
        $soldProductAnalytics = $this->getProductSoldProductAnalytics();
        $discardedProductAnalytics = $this->getDiscardedProductAnalytics();

        return response()->json([
            'sold_product_analytics' => $soldProductAnalytics,
            'discarded_product_analytics' => $discardedProductAnalytics
        ]);
    }

    private function getProductSoldProductAnalytics(){
        
    }

    private function getDiscardedProductAnalytics(){

    }
}
