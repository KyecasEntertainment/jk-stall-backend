<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class ProductController extends Controller
{
    public function createProduct(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:1',
        ]);

        $batchId = 'batch_' . uniqid();

        return response()->json([
            'batch_id' => $batchId,
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
