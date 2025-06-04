<?php
namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function searchByName($term)
    {
        // البحث عن المنتجات التي يبدأ اسمها بـ $term
        $products = Product::where('Name', 'LIKE', $term . '%')->get();

        return response()->json($products);
    }

    public function applyOffer(Request $request, $id)
{
    $request->validate([
        'discount_percent' => 'required|numeric|min:0|max:100',
    ]);

    $product = Product::find($id);
    if (!$product) {
        return response()->json([
            'message' => 'المنتج غير موجود.',
        ], 404);
    }
    $discount = $request->input('discount_percent');
    $originalPrice = $product->Price;

    $discountAmount = ($discount / 100) * $originalPrice;
    $offerPrice = $originalPrice - $discountAmount;

    $product->offer_status = 1;
    $product->offer_price = round($offerPrice, 2);
    $product->save();

    return response()->json([
        'message' => 'Offer applied successfully.',
        'product' => $product
    ]);

}
public function removeOffer($id): JsonResponse
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json([
            'message' => 'المنتج غير موجود.'
        ], 404);
    }

    $product->offer_status = 0;
    $product->offer_price = null;
    $product->save();

    return response()->json([
        'message' => 'تم إلغاء العرض بنجاح.',
        'product' => $product
    ], 200);
}


public function update(Request $request, $id) 
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json(['message' => 'المنتج غير موجود'], 404);
    }

    // تحقق من صحة البيانات
$validated = $request->validate([
    'Name' => 'sometimes|string|max:255',
    'Discription' => 'nullable|string',
    'discription2' => 'nullable|string',
    'Price' => 'sometimes|numeric|min:0',
    'catigory' => 'sometimes|string|max:255',
    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    'offer_status' => 'sometimes|numeric|min:0|max:1', // أو حسب القيم المتاحة عندك
    'offer_price' => 'sometimes|numeric|min:0',
    'status' => 'sometimes|numeric|min:0|max:1',
    'Stock' => 'sometimes|integer|min:0',
    'CreatedBy' => 'sometimes|integer|exists:users,id', // افتراضًا مرتبط بجدول المستخدمين
]);


    // إذا تم رفع صورة جديدة
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads/images'), $imageName);
        $validated['image'] = 'uploads/images/' . $imageName;
    }

    // تحديث بيانات المنتج
    $product->update($validated);

    return response()->json([
        'message' => 'تم تحديث المنتج بنجاح',
        'product' => $product
    ]);
}

    public function destroy($id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json(['message' => 'المنتج غير موجود'], 404);
    }

    $product->delete();

    return response()->json(['message' => 'تم حذف المنتج بنجاح']);
}
}
