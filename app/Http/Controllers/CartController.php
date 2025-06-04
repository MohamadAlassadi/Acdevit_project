<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function store(Request $request)
    {
        // التحقق من البيانات الواردة
        $validator = Validator::make($request->all(), [
            'Client_id' => 'required|integer|exists:accounts,Account_id',
            'Date_added' => 'required|date',
            'IsCheckedOut' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // إنشاء عربة جديدة
        $cart = Cart::create([
            'Client_id' => $request->Client_id,
            'Date_added' => $request->Date_added,
            'IsCheckedOut' => $request->IsCheckedOut,
        ]);

        return response()->json(['message' => 'Cart created successfully', 'data' => $cart], 201);
    }
    public function getCartByClientId($client_id)
{
    $cart = Cart::where('Client_id', $client_id)
                ->where('IsCheckedOut', 0)
                ->latest() // لجلب أحدث عربة
                ->first();

    if ($cart) {
        return response()->json([
            'status' => 'success',
            'cart' => $cart
        ]);
    } else {
        return response()->json([
            'status' => 'not_found',
            'message' => 'لم يتم العثور على عربة نشطة لهذا العميل.'
        ], 404);
    }
}
}
