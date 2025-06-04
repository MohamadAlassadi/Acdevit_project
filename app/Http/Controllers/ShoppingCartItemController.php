<?php

namespace App\Http\Controllers;

use App\Models\ShoppingCartItem;
use App\Models\Cart;
use App\Models\Product;  // تأكد من إضافة استيراد موديل المنتج
use Illuminate\Http\Request;

class ShoppingCartItemController extends Controller
{
    // دالة لإضافة عنصر إلى السلة
    public function addItemToCart(Request $request)
    {
        // التحقق من صحة البيانات المدخلة
        $validated = $request->validate([
            'CartID' => 'required|integer|exists:carts,id',  // تأكد من أن السلة موجودة
            'Product_id' => 'required|integer|exists:products,id', // تأكد من أن المنتج موجود
            'Quantity' => 'required|integer|min:1', // تأكد من أن الكمية أكبر من 0
            'Price' => 'required|numeric|min:0', // التأكد من أن السعر تم إدخاله يدويًا
        ]);

        // التحقق من وجود السلة
        $cart = Cart::findOrFail($validated['CartID']);  // باستخدام findOrFail

        // التحقق من وجود المنتج
        $product = Product::where('id', $validated['Product_id'])->first();
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // استخدام السعر المدخل يدويًا بدلاً من السعر المخزن في المنتج
        $price = $validated['Price'];

        // حساب السعر الإجمالي (سعر المنتج المدخل يدويًا * الكمية)
        $totalPrice = $price * $validated['Quantity'];

        // إنشاء السجل في جدول shopping_cart_items
        $cartItem = new ShoppingCartItem();
        $cartItem->CartID = $validated['CartID'];  // تأكد من استخدام CartID
        $cartItem->Product_id = $validated['Product_id'];
        $cartItem->Quantity = $validated['Quantity'];
        $cartItem->UnitPrice = $price;  // استخدام السعر المدخل يدويًا
        $cartItem->TotalPrice = $totalPrice;  // حساب الـ TotalPrice بناءً على الكمية

        // حفظ السجل في قاعدة البيانات
        if ($cartItem->save()) {
            return response()->json(['message' => 'Item added to cart successfully!', 'cart_item' => $cartItem], 201);
        } else {
            return response()->json(['message' => 'Failed to add item to cart'], 500);
        }
    }
    // عرض كل العناصر في عربة معينة
public function getCartItems($cartId)
{
    $cart = Cart::with('items.product')->findOrFail($cartId);
    return response()->json($cart->items);
}

// تعديل عنصر في العربة (كمية أو سعر)
public function updateCartItem(Request $request, $itemId)
{
    $cartItem = ShoppingCartItem::findOrFail($itemId);

    $validated = $request->validate([
        'Quantity' => 'nullable|integer|min:1',
        'UnitPrice' => 'nullable|numeric|min:0',
    ]);

    if (isset($validated['Quantity'])) {
        $cartItem->Quantity = $validated['Quantity'];
    }

    if (isset($validated['UnitPrice'])) {
        $cartItem->UnitPrice = $validated['UnitPrice'];
    }

    $cartItem->TotalPrice = $cartItem->Quantity * $cartItem->UnitPrice;
    $cartItem->save();

    return response()->json(['message' => 'Cart item updated', 'cart_item' => $cartItem]);
}

// حذف عنصر من العربة
public function deleteCartItem($itemId)
{
    $cartItem = ShoppingCartItem::findOrFail($itemId);
    $cartItem->delete();

    return response()->json(['message' => 'Cart item deleted successfully']);
}

}
