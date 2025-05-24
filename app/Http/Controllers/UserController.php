<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductList;


class UserController extends Controller
{
    public function login(Request $request)
    {
        return response()->json([
            'message' => 'Login page',
            'status' => 'success'
        ]);
    }
    public function test(){
        return view('welcome',);
    }



}
