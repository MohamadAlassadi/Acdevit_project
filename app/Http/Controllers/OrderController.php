<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\ShoppingCartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockLevel;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
public function getOrdersWithProducts(Request $request)
{
    try {
        // جلب جميع الطلبات مرتبة حسب التاريخ (من الأحدث إلى الأقدم)
        $orders = Order::with(['items.product'])
            ->orderBy('Date_added', 'desc')
            ->get();

        // تحضير هيكل البيانات النهائي
        $result = [
            'success' => true,
            'orders' => $orders->map(function ($order) {
                return [
                    'order_id' => $order->id,
                    'client_id' => $order->Client_id,
                    'cart_id' => $order->Cart_id,
                    'order_date' => $order->Date_added,
                    'order_status' => $order->Order_status,
                    'is_checked_out' => $order->IsCheckedOut,
                    'items' => $order->items->map(function ($item) {
                        $product = $item->product;

                        // تحقق من وجود العرض واختيار السعر المناسب
                        $priceToShow = null;
                        if ($product) {
                            if ($product->offer_status == 1 && $product->offer_price !== null) {
                                $priceToShow = $product->offer_price;
                            } else {
                                $priceToShow = $product->Price;
                            }
                        }

                        return [
                            'order_item_id' => $item->OrderItem_id,
                            'product_id' => $item->Product_id,
                            'quantity' => $item->Quantity,
                            'unit_price' => $item->UnitPrice,
                            'total_price' => $item->TotalPrice,
                            'product_details' => $product ? [
                                'name' => $product->Name,
                                'description' => $product->Discription,
                                'discription2' => $product->discription2,
                                'price' => $priceToShow,  // السعر بعد التحقق
                                'stock' => $product->Stock,
                                'catigory' => $product->catigory,
                                'image_url' => $product->image
                            ] : null
                        ];
                    })
                ];
            }),
            'message' => 'Orders retrieved successfully with product details.'
        ];

        return response()->json($result, 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve orders.',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function getOrdersByDate(Request $request)
{
    // تحقق من وجود التاريخ
    if (!$request->has('date')) {
        return response()->json(['error' => 'يرجى تمرير التاريخ عبر ?date=YYYY-MM-DD'], 400);
    }

    $date = $request->input('date');

    // جلب الطلبات حسب تاريخ الإضافة
    $orders = Order::with(['account', 'cart', 'items'])
        ->whereDate('Date_added', $date)
        ->get();

    return response()->json([
        'date' => $date,
        'orders' => $orders
    ]);
}

  public function processCartToOrder(Request $request)
{
    DB::beginTransaction();

    try {
        $cartId = $request->input('cart_id');
        $clientId = $request->input('client_id');

        $cart = Cart::with('items.product')->where('id', $cartId)->where('Client_id', $clientId)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على العربة أو أنها لا تنتمي لهذا المستخدم.'
            ]);
        }

        $unavailable = [];
        $insufficient = [];

        foreach ($cart->items as $item) {
            $product = $item->product;
            $requested = $item->Quantity;

            if (!$product || $product->Stock === 0) {
                $unavailable[] = $product ? $product->Name : 'منتج غير معروف';
            } elseif ($product->Stock < $requested) {
                $insufficient[] = [
                    'product' => $product->Name,
                    'requested' => $requested,
                    'available' => $product->Stock
                ];
            }
        }

        if (!empty($unavailable) || !empty($insufficient)) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'تعذر تنفيذ الطلب بسبب نقص في المنتجات.',
                'unavailable' => $unavailable,
                'insufficient' => $insufficient
            ]);
        }

        $order = Order::create([
            'Client_id' => $clientId,
            'Cart_id' => $cartId,
            'Date_added' => now(),
            'Order_status' => 'تم استلام طلبك',
            'IsCheckedOut' => 0,
        ]);

        foreach ($cart->items as $item) {
            $product = $item->product;
            $unitPrice = $product->offer_status == 1 && $product->offer_price !== null
                ? $product->offer_price
                : $product->Price;

            OrderItem::create([
                'OrderID' => $order->id,
                'Product_id' => $item->Product_id,
                'Quantity' => $item->Quantity,
                'UnitPrice' => $unitPrice,
                'TotalPrice' => $item->Quantity * $unitPrice
            ]);
        }

        foreach ($cart->items as $item) {
            $product = $item->product;
            $productId = $item->Product_id;
            $quantityNeeded = $item->Quantity;

            $unitPrice = $product->offer_status == 1 && $product->offer_price !== null
                ? $product->offer_price
                : $product->Price;

            $batches = ProductBatch::where('product_id', $productId)
                ->whereHas('stockLevel', fn($q) => $q->where('current_quantity', '>', 0))
                ->with('stockLevel')
                ->orderBy('expiry_date')
                ->get();

            foreach ($batches as $batch) {
                $stock = $batch->stockLevel;
                if ($stock->current_quantity <= 0) continue;

                $deduct = min($quantityNeeded, $stock->current_quantity);
                $stock->current_quantity -= $deduct;
                $stock->save();

                $batch->transactions()->create([
                    'type' => 'OUT',
                    'quantity' => $deduct,
                    'price' => $unitPrice,
                    'totalPrice' => $unitPrice * $deduct,
                    'transaction_date' => now(),
                    'party' => 'Order #' . $order->id,
                    'notes' => 'خصم من دفعة لتلبية الطلب'
                ]);

                $quantityNeeded -= $deduct;
                if ($quantityNeeded <= 0) break;
            }
        }

        foreach ($cart->items as $item) {
            $productId = $item->Product_id;
            $totalStock = StockLevel::whereIn(
                'batch_id',
                ProductBatch::where('product_id', $productId)->pluck('id')
            )->sum('current_quantity');

            $item->product->Stock = $totalStock;
            $item->product->save();
        }

        $cart->items()->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الطلب وتحديث المخزون وحذف محتويات العربة.',
            'order_id' => $order->id,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'خطأ: ' . $e->getMessage()
        ]);
    }
}

public function cancelOrder($orderId)
{
    DB::beginTransaction();

    try {
        $order = Order::with('items.product')->findOrFail($orderId);

        if ($order->Order_status === 'ملغي') {
            return response()->json([
                'success' => false,
                'message' => 'الطلب ملغي بالفعل.'
            ]);
        }

        foreach ($order->items as $item) {
            $productId = $item->Product_id;
            $quantityToReturn = $item->Quantity;

            // خذ السعر من العنصر (تأكد أنه موجود)
            $price = $item->price ?? ($item->product->price ?? 0);

            // إرجاع الكميات إلى الدفعات بترتيب عكسي
            $batches = ProductBatch::where('product_id', $productId)
                ->with('stockLevel')
                ->orderByDesc('expiry_date')
                ->get();

            foreach ($batches as $batch) {
                if (!$batch->stockLevel) continue;

                $batch->stockLevel->current_quantity += $quantityToReturn;
                $batch->stockLevel->save();

                $batch->transactions()->create([
                    'type' => 'IN',
                    'quantity' => $quantityToReturn,
                    'price' => $price,
                    'totalPrice' => $price * $quantityToReturn,
                    'transaction_date' => now(),
                    'party' => 'إلغاء الطلب #' . $order->id,
                    'notes' => 'إرجاع الكمية بسبب إلغاء الطلب'
                ]);

                break; // بعد إضافة الكمية لدفعة واحدة نخرج
            }

            // تحديث كمية المخزون الإجمالية للمنتج
            $totalStock = StockLevel::whereIn(
                'batch_id',
                ProductBatch::where('product_id', $productId)->pluck('id')
            )->sum('current_quantity');

            $item->product->Stock = $totalStock;
            $item->product->save();
        }

        $order->Order_status = 'ملغي';
        $order->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء الطلب وإرجاع الكميات بنجاح.'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'فشل في الإلغاء: ' . $e->getMessage()
        ]);
    }
}

public function getByClientId($client_id)
{
    try {
        // جلب طلبات العميل مع العناصر والمنتجات المرتبطة
        $orders = Order::with(['items.product'])
            ->where('Client_id', $client_id)
            ->orderBy('Date_added', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد طلبات لهذا العميل'
            ], 404);
        }

        // تحضير هيكل البيانات النهائي
        $result = [
            'success' => true,
            'orders' => $orders->map(function ($order) {
                return [
                    'order_id' => $order->id,
                    'client_id' => $order->Client_id,
                    'cart_id' => $order->Cart_id,
                    'order_date' => $order->Date_added,
                    'order_status' => $order->Order_status,
                    'is_checked_out' => $order->IsCheckedOut,
                    'items' => $order->items->map(function ($item) {
                        $product = $item->product;
                        return [
                            'order_item_id' => $item->OrderItem_id,
                            'product_id' => $item->Product_id,
                            'quantity' => $item->Quantity,
                            'unit_price' => $item->UnitPrice,
                            'total_price' => $item->TotalPrice,
                            'product_details' => $product ? [
                                'name' => $product->Name,
                                'description' => $product->Discription,
                                'discription2' => $product->discription2,
                                'price' => $product->Price,
                                'stock' => $product->Stock,
                                'catigory' => $product->catigory,
                                'image_url' => $product->image
                            ] : null
                        ];
                    })
                ];
            }),
            'message' => 'تم استرجاع طلبات العميل مع تفاصيل المنتجات بنجاح'
        ];

        return response()->json($result, 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'فشل في استرجاع طلبات العميل',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function getByDriverId($driver_id)
{
    try {
        // جلب الطلبات المرتبطة بالسائق من خلال جدول الفواتير
        $orders = Order::with([
                'items.product',
                'invoice.Drivers' // استخدم العلاقة كما هي معرّفة في الموديل
            ])
            ->whereHas('invoice', function($query) use ($driver_id) {
                $query->where('driver_id', $driver_id); // لاحظ أن الحقل هو driver_id وليس Driver_id
            })
            ->orderBy('Date_added', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد طلبات مرتبطة بهذا السائق'
            ], 404);
        }

        // تحضير هيكل البيانات النهائي
        $result = [
            'success' => true,
            'orders' => $orders->map(function ($order) {
                $invoice = $order->invoice;
                $driver = $invoice->Drivers ?? null; // استخدم العلاقة Drivers بدلاً من driver
                
                return [
                    'order_id' => $order->id,
                    'client_id' => $order->Client_id,
                    'cart_id' => $order->Cart_id,
                    'order_date' => $order->Date_added,
                    'order_status' => $order->Order_status,
                    'is_checked_out' => $order->IsCheckedOut,
                    'invoice_info' => $invoice ? [
                        'invoice_id' => $invoice->id,
                        'driver_id' => $invoice->driver_id,
                        'order_price' => $invoice->orderPrice,
                        'delivery_price' => $invoice->deliveryPrice,
                        'total_price' => $invoice->totalPrice,
                        'payment_status' => $invoice->payment_status,
                        'payment_method' => $invoice->payment_method
                    ] : null,
                    'items' => $order->items->map(function ($item) {
                        $product = $item->product;
                        return [
                            'order_item_id' => $item->OrderItem_id,
                            'product_id' => $item->Product_id,
                            'quantity' => $item->Quantity,
                            'unit_price' => $item->UnitPrice,
                            'total_price' => $item->TotalPrice,
                            'product_details' => $product ? [
                                'name' => $product->Name,
                                'description' => $product->Discription,
                                'discription2' => $product->discription2,
                                'price' => $product->Price,
                                'stock' => $product->Stock,
                                'catigory' => $product->catigory,
                                'image_url' => $product->image
                            ] : null
                        ];
                    })
                ];
            }),
            'message' => 'تم استرجاع طلبات السائق مع تفاصيل المنتجات بنجاح'
        ];

        return response()->json($result, 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'فشل في استرجاع طلبات السائق',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
