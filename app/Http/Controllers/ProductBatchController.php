<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductTransaction;
use App\Models\StockLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBatchController extends Controller
{
        public function calculateTotalCost(Request $request)
{

    $batches = ProductBatch::get();

    if ($batches->isEmpty()) {
        return response()->json([
            'message' => 'No batches found in the given date range.',
            'data' => [],
        ], 404);
    }

    $totalQuantity = $batches->sum('quantity');
    $totalPrice = $batches->sum('totalPrice');

    return response()->json([
        'from' => $request->from,
        'to' => $request->to,
        'total_quantity' => $totalQuantity,
        'total_price' => $totalPrice,
    ]);
}

    public function calculateTotalCostBetweenDates(Request $request)
{
    $request->validate([
        'from' => 'required|date',
        'to' => 'required|date|after_or_equal:from',
    ]);

    $batches = ProductBatch::whereBetween('manufacture_date', [$request->from, $request->to])->get();

    if ($batches->isEmpty()) {
        return response()->json([
            'message' => 'No batches found in the given date range.',
            'data' => [],
        ], 404);
    }

    $totalQuantity = $batches->sum('quantity');
    $totalPrice = $batches->sum('totalPrice');

    return response()->json([
        'from' => $request->from,
        'to' => $request->to,
        'total_quantity' => $totalQuantity,
        'total_price' => $totalPrice,
    ]);
}

    public function getBatchesBetweenDates(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'from' => 'required|date',
        'to' => 'required|date|after_or_equal:from',
    ]);
    $sort = $request->get('sort', 'asc'); // default to ascending

    $batches = ProductBatch::where('product_id', $request->product_id)
        ->whereBetween('manufacture_date', [$request->from, $request->to])->orderBy('Price', $sort) // ترتيب حسب السعر

        ->get();

    if ($batches->isEmpty()) {
        return response()->json([
            'message' => 'No batches found for this product in the given date range.',
            'data' => [],
        ], 404);
    }

    $totalQuantity = $batches->sum('quantity');
    $totalPrice = $batches->sum('totalPrice');

    return response()->json([
        'product_id' => $request->product_id,
        'from' => $request->from,
        'to' => $request->to,
        'total_quantity' => $totalQuantity,
        'total_price' => $totalPrice,
        'batches' => $batches->map(function ($batch) {
            return [
                'batch_number' => $batch->batch_number,
                'quantity' => $batch->quantity,
                'total_price' => $batch->totalPrice,
                'unit_price' => $batch->Price,
                'expiry_date' => $batch->expiry_date,
            ];
        }),
    ]);
}

public function getbyproduct($id)
{
    $batches = ProductBatch::with('product')->where('product_id', $id)->get();

    if ($batches->isEmpty()) {
        return response()->json(['message' => 'No Batches for this product'], 404);
    }

    // جمع البيانات التي نريد عرضها
    $result = $batches->map(function ($batch) {
        return [
            'batch_number' => $batch->batch_number,
            'quantity' => $batch->quantity,
            'total_price' => $batch->totalPrice,
            'unit_price' => $batch->Price,
        ];
    });

    return response()->json($result);
}

   
    public function index(Request $request)
    {
        try {
            // يمكنك إضافة فلترة أو بحث هنا إذا لزم الأمر
            $batches = ProductBatch::with(['product', 'stockLevel'])
                ->orderBy('created_at', 'desc')
                ->get();
    
            return response()->json([
                'success' => true,
                'data' => $batches,
                'message' => 'Product batches retrieved successfully.'
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product batches.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Validate the incoming request
      // Validate the incoming request
$validatedData = $request->validate([
    'product_id' => 'required|exists:products,id',
    'batch_number' => 'required|string|max:255',
    'manufacture_date' => 'nullable|date',
    'expiry_date' => 'required|date',
    'quantity' => 'required|integer|min:1',
    'price' => 'required|numeric|min:0',  // أضف السعر كحقل مطلوب
]);

DB::beginTransaction();

try {
    $totalPrice = $validatedData['price'] * $validatedData['quantity'];

    $productBatch = ProductBatch::create([
        'product_id' => $validatedData['product_id'],
        'batch_number' => $validatedData['batch_number'],
        'manufacture_date' => $validatedData['manufacture_date'],
        'expiry_date' => $validatedData['expiry_date'],
        'quantity' => $validatedData['quantity'],
        'Price' => $validatedData['price'],      // السعر للقطعة الواحدة
        'totalPrice' => $totalPrice,             // السعر الكلي = السعر * الكمية
    ]);

            ProductTransaction::create([
                'batch_id' => $productBatch->id,
                'type' => 'IN',
                'quantity' => $validatedData['quantity'],
                'transaction_date' => now(),
                'party' => 'Supplier', // يمكنك تغيير هذا حسب الحالة
                'notes' => 'Initial batch addition',
                'price' => $validatedData['price'], // ✅ سعر القطعة
                'totalPrice' => $validatedData['price'] * $validatedData['quantity'], // ✅ السعر الإجمالي
            ]);

            // Step 3: Update the stock level in stock_levels table
            $stockLevel = StockLevel::firstOrCreate(
                ['batch_id' => $productBatch->id],
                ['current_quantity' => $validatedData['quantity']]
            );

            // Step 4: Update the product's stock by calculating the total stock from all batches
            $totalStock = ProductBatch::where('product_id', $validatedData['product_id'])
                ->sum('quantity'); // جمع الكميات لجميع الدفعات الخاصة بهذا المنتج

            // Step 5: Update the product's stock with the new total
            $product = Product::find($validatedData['product_id']);
            $product->stock = $totalStock; // تحديث المخزون ليكون مجموع الكميات لجميع الدفعات
            $product->save();

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Product batch added and stock updated successfully!',
                'product_batch' => $productBatch,
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollback();

            return response()->json([
                'message' => 'Failed to add product batch!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
