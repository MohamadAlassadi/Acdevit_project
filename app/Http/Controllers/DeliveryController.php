<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Delivery; // ← هذا هو المطلوب إضافته
use App\Models\Orders;
use App\Models\Order;
use App\Models\invoice;


class DeliveryController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_id'       => 'required|exists:accounts,Account_id',
            'client_id'       => 'required|exists:accounts,Account_id',
            'order_id'        => 'required|exists:orders,id',
            'adress'          => 'required|string',
            'status'          => 'required|string',
            'expected_hours'  => 'nullable|integer|min:1|max:168', // أسبوع كحد أقصى
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $delivery = Delivery::create($request->all());

        return response()->json(['message' => 'Delivery created successfully', 'data' => $delivery], 201);
    }
    public function show($id)
{
    $deliveries = Delivery::where('driver_id', $id)->get();
    return response()->json($deliveries);
}
public function updateDeliveryStatus(Request $request, $id)
{
    // نبحث عن التوصيل بالـ id
    $delivery = Delivery::find($id);

    if (!$delivery) {
        return response()->json(['message' => 'Delivery not found'], 404);
    }

    // نحدث الحالة (مثلاً status)
    // لازم تأكد إن الـ request فيها قيمة 'status'
    if (!$request->has('status')) {
        return response()->json(['message' => 'Status field is required'], 400);
    }

    $delivery->status = $request->input('status');
    $delivery->save();

    return response()->json([
        'message' => 'Delivery status updated successfully',
        'delivery' => $delivery
    ]);
}
public function getInvoiceByDelivery($deliveryId)
{
    $delivery = Delivery::find($deliveryId);
    if (!$delivery) {
        return response()->json(['message' => 'Delivery not found'], 404);
    }

    // جلب الطلب (order) يدوياً لأن العلاقة قد لا تعمل بسبب الخطأ في الاسم
    $order = \App\Models\Order::find($delivery->order_id);
    if (!$order) {
        return response()->json(['message' => 'Order not found for this delivery'], 404);
    }

    // جلب الفاتورة المرتبطة بالطلب
    $invoice = Invoice::where('order_id', $order->id)->first();
    if (!$invoice) {
        return response()->json(['message' => 'Invoice not found for this order'], 404);
    }

    return response()->json(['invoice' => $invoice]);

}

public function getDeliveryByOrderId($orderId)
    {
        // التحقق من وجود الطلب أولاً
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // البحث عن التوصيل المرتبط بهذا الطلب
        $delivery = Delivery::where('order_id', $orderId)->first();

        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found for this order'], 404);
        }

        return response()->json([
            'message' => 'Delivery retrieved successfully',
            'delivery' => $delivery
        ]);
    }


    public function updateDeliveryStatusByOrderId(Request $request, $orderId)
{
    // البحث عن التوصيل باستخدام order_id
    $delivery = Delivery::where('order_id', $orderId)->first();

    if (!$delivery) {
        return response()->json(['message' => 'Delivery not found for this order'], 404);
    }

    // التحقق من وجود حقل الحالة في الطلب
    if (!$request->has('status')) {
        return response()->json(['message' => 'Status field is required'], 400);
    }

    // تحديث حالة التوصيل
    $delivery->status = $request->input('status');
    $delivery->save();

    return response()->json([
        'message' => 'Delivery status updated successfully',
        'delivery' => $delivery
    ]);
}

public function index()
{
    $deliveries = Delivery::all();
    return response()->json([
        'message' => 'All deliveries retrieved successfully',
        'data' => $deliveries
    ]);
}

}