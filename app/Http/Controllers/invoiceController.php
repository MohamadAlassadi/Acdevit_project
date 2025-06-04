<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Coupon;
use App\Http\Controllers\Reaquest;

class invoiceController extends Controller
{

    public function  det_discounts_Amount(Request $request)
    {
    $request->validate([
        'from'=>'required|date',
        'to'=>'required|date|after_or_equal:from',
    ]);
    $order=Order::whereBetween('Date_added',[$request->from,$request->to])->get();
    if($order->isEmpty()){
        return response()->json('no orders in this time');
    }
    $orderIds=$order->pluck('id');
    $invoice=Invoice::whereIn('order_id',$orderIds)->where('discount_status',1)->whereIn('discount_type',['order','invoice'])->get();
    $discountAmount=$invoice->sum('discount_amount');
    return response()->json([
    'invoices between ' . $request->from . ' and ' . $request->to => $invoice,
    'discountAmount' => $discountAmount
]);
    }



public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'client_id'      => 'required|exists:accounts,Account_id',
        'driver_id'      => 'required|exists:accounts,Account_id',
        'order_id'       => 'required|exists:orders,id',
        'orderPrice'     => 'required|numeric|min:0',
        'deliveryPrice'  => 'required|numeric|min:0',
        'totalPrice'     => 'required|numeric|min:0',
        'payment_status' => 'required|string',
        'payment_method' => 'required|string',
        'coupon_code'    => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $request->all();

    // معالجة الكوبون إذا تم إدخاله
    if (!empty($data['coupon_code'])) {
        $coupon = \App\Models\Coupon::where('code', $data['coupon_code'])
            ->where('status', 'active')
            ->first();

        if (!$coupon) {
            return response()->json(['error' => 'رمز الكوبون غير صحيح أو غير مفعل'], 400);
        }

        // تحقق من أن الكوبون يخص هذا المستخدم
        if ($coupon->dest_id != $data['client_id']) {
            return response()->json(['error' => 'هذا الكوبون غير مخصص لهذا المستخدم'], 403);
        }

        // تحقق من تاريخ الصلاحية
        $today = now()->toDateString();
        if ($coupon->expiry_date && $coupon->expiry_date < $today) {
            return response()->json(['error' => 'انتهت صلاحية الكوبون'], 400);
        }

        // تطبيق الخصم حسب نوع الكوبون
        $discountPercent = $coupon->discount_percent / 100;

        switch ($coupon->type) {
            case 'order':
                $discountAmount = $data['orderPrice'] * $discountPercent;
                $data['orderPrice'] -= $discountAmount;
                break;

            case 'delivery':
                $discountAmount = $data['deliveryPrice'] * $discountPercent;
                $data['deliveryPrice'] -= $discountAmount;
                break;

            case 'invoice':
                $discountAmount = $data['totalPrice'] * $discountPercent;
                break;

            default:
                return response()->json(['error' => 'نوع الكوبون غير معروف'], 400);
        }

        // إعادة حساب السعر الكلي
        if ($coupon->type === 'invoice') {
            $data['totalPrice'] -= $discountAmount;
        } else {
            $data['totalPrice'] = $data['orderPrice'] + $data['deliveryPrice'];
        }

        // إعداد قيم الخصم في الفاتورة
        $data['discount_status'] = 1;
        $data['discount_type'] = $coupon->type;
        $data['discount_amount'] = $discountAmount;
        $data['coupon_status'] = 'yes'; // اختياري
    } else {
        // في حال لم يتم استخدام كوبون
        $data['discount_status'] = 0;
        $data['discount_type'] = null;
        $data['discount_amount'] = null;
        $data['coupon_status'] = 'no'; // اختياري
    }

    $invoice = \App\Models\Invoice::create($data);

    return response()->json([
        'message' => 'تم إنشاء الفاتورة بنجاح',
        'data' => $invoice
    ], 201);
}

    public function updatePaymentStatus($invoiceId, Request $request)
{
    // تحقق من وجود الفاتورة
    $invoice = Invoice::find($invoiceId);
    if (!$invoice) {
        return response()->json(['message' => 'Invoice not found'], 404);
    }

    // تأكد من وجود حالة الدفع في الطلب
    $request->validate([
        'payment_status' => 'required|string',
    ]);

    // عدل حالة الدفع
    $invoice->payment_status = $request->payment_status;
    $invoice->save();

    return response()->json([
        'message' => 'Payment status updated successfully',
        'invoice' => $invoice,
    ]);
}


public function getByOrderId($order_id)
{
    $invoice = invoice::where('order_id', $order_id)->first();

    if (!$invoice) {
        return response()->json(['message' => 'لم يتم العثور على فاتورة لهذا الطلب'], 404);
    }

    return response()->json(['invoice' => $invoice]);
}
public function index(Request $request)
{
    try {
        $invoices = Invoice::query();
        
        // إمكانية التصفية حسب حالة الدفع
        if ($request->has('payment_status')) {
            $invoices->where('payment_status', $request->payment_status);
        }
        
        return response()->json(['invoices' => $invoices->get()]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to retrieve invoices',
            'error' => $e->getMessage()
        ], 500);
    }
}
}