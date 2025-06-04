<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class SelectProduct extends Controller
{
   public function index(): JsonResponse
{
    $products = Product::with('creator')
        ->where('status', 1)
        ->get();

    return response()->json($products);
}
   public function offerproduct(): JsonResponse
{
    $products = Product::with('creator')
        ->where('status', 1)->where('offer_status',1)
        ->get();

    return response()->json($products);
}

public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $validated = $request->validate([
            'Name' => 'required|string|max:255',
            'Discription' => 'required|string',
            'Price' => 'required|numeric|min:0',
            'Stock' => 'sometimes|numeric|min:0',
            'discription2' => 'nullable|string',
            'catigory' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
    
        try {
            // إنشاء المنتج الجديد
            $product = new Product();
            $product->Name = $validated['Name'];
            $product->Discription = $validated['Discription'];
            $product->Price = $validated['Price'];
            $product->Stock = $validated['Stock'] ?? 0; // قيمة افتراضية إذا لم يتم تقديمها
            $product->discription2 = $validated['discription2'] ?? null;
            $product->catigory = $validated['catigory'];
            
            // تعيين المستخدم المنشئ إذا كان النظام يستخدم المصادقة
            $product->CreatedBy = auth()->id() ?? null;
    
            // رفع الصورة إن وجدت
            if ($request->hasFile('image')) {
                Log::info('بدأ رفع صورة المنتج');
                
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $imageName = Str::slug($validated['Name']) . '_' . time() . '.' . $extension;
                
                // حفظ الصورة في المجلد المخصص
                $image->move(public_path('uploads/products'), $imageName);
                
                $product->image = 'uploads/products/' . $imageName;
                Log::info('تم حفظ صورة المنتج: ' . $product->image);
            }
    
            // حفظ المنتج
            $product->save();
            Log::info('تم حفظ المنتج بنجاح - ID: ' . $product->id);
    
            return response()->json([
                'message' => 'تم إنشاء المنتج بنجاح',
                'product' => [
                    'id' => $product->id,
                    'Name' => $product->Name,
                    'Discription' => $product->Discription,
                    'Price' => $product->Price,
                    'Stock' => $product->Stock,
                    'discription2' => $product->discription2,
                    'catigory' => $product->catigory,
                    'image' => $product->image ? asset($product->image) : null,
                    'CreatedBy' => $product->CreatedBy,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ],
            ], 201);
    
        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء المنتج: ' . $e->getMessage());
            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء المنتج',
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ], 500);
        }
    }
}
