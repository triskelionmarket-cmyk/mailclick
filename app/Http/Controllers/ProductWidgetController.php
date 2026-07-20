<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;

class ProductWidgetController extends Controller
{
    //
}


use Illuminate\Support\Facades\DB;

public function list()
{
    $products = DB::table('mlck_products')
        ->select('id', 'title', 'price', 'source_item_id as sku')
        ->where('status', 'active') // dacă ai câmp status
        ->orderByDesc('created_at')
        ->limit(50)
        ->get();

    return response()->json($products);
}
