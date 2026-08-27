<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $movements = StockMovement::with('product')->get();

        if ($request->ajax()) {
            return response()->json($movements);
        }

        return view('products.stockmovements');
    }
}
