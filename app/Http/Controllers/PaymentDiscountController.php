<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PaymentDiscount;
use App\Models\Coupon;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PaymentDiscountController extends Controller
{
    public function getbyid($id)
{
    $discounts = PaymentDiscount::where('id',$id)->get()->first();
    if (!$discounts) {
        return response()->json([
            'message' => 'العرض غير موجود'
        ], 404);
    }
    return response()->json(['data'=>$discounts
]  );
}
public function index()
{
    $discounts = PaymentDiscount::all();

    return response()->json([
        'message' => 'قائمة العروض',
        'data' => $discounts
    ]);
}
public function status()
{
    $discounts = PaymentDiscount::all()->where('status','active');

    return response()->json([
        'message' => 'قائمة العروض',
        'data' => $discounts
    ]);
}

    // 🟢 إنشاء عرض جديد
public function store(Request $request)
{
    $request->validate([
        'createdBy' => 'required|exists:accounts,Account_id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'type' => 'required|in:order,delivery,invoice',
        'discount_percent' => 'required|numeric|min:0|max:100',
        'valid_days' => 'required|integer|min:1',
        'price' => 'required|numeric|min:0',
    ]);

    $discount = PaymentDiscount::create([
        'title' => $request->title,
        'description' => $request->description,
        'type' => $request->type,
        'discount_percent' => $request->discount_percent,
        'valid_days' => $request->valid_days,
        'price' => $request->price,
        'status' => 'active',
        'createdBy' =>$request->createdBy,
    ]);

    return response()->json(['message' => 'تم إنشاء العرض بنجاح', 'data' => $discount]);
}


    // 🟡 تعديل عرض
    public function update(Request $request, $id)
    {
        $discount = PaymentDiscount::findOrFail($id);

        $discount->update($request->only([
            'title', 'type', 'discount_percent', 'valid_days', 'price', 'description', 'status'
        ]));

        return response()->json(['message' => 'تم تحديث العرض', 'data' => $discount]);
    }

   public function purchase(Request $request)
{
    $request->validate([
        'discount_id' => 'required|exists:payment_discounts,id',
        'method' => 'required|in:card,syriatel',
        'credit_number' => 'required_if:method,card',
        'syriatel_cash' => 'required_if:method,syriatel',
        'dest_id' => 'required|exists:accounts,Account_id',
    ]);

    $discount = PaymentDiscount::findOrFail($request->discount_id);

    DB::beginTransaction();

    try {
        // 🔍 البحث عن وسيلة الدفع بدون التحقق من user_id
        $paymentQuery = DB::table('payment')
            ->where('method', $request->method);

        if ($request->method === 'card') {
            $paymentQuery->where('credit_number', $request->credit_number);
        } else {
            $paymentQuery->where('syriatel_cash', $request->syriatel_cash);
        }

        $payment = $paymentQuery->lockForUpdate()->first();

        if (!$payment) {
            return response()->json(['message' => 'بيانات الدفع غير موجودة'], 404);
        }

        if ($payment->balance < $discount->price) {
            return response()->json(['message' => 'الرصيد غير كافٍ'], 400);
        }

        // ➖ خصم الرصيد
        DB::table('payment')
            ->where('id', $payment->id)
            ->update([
                'balance' => $payment->balance - $discount->price
            ]);

        // 🎁 إنشاء الكوبون
        $coupon = Coupon::create([
            'source_id' => $discount->createdBy,
            'dest_id' => $request->dest_id, // الاعتماد على user_id من الدفع
            'code' => strtoupper(Str::random(10)),
            'type' => $discount->type,
            'discount_percent' => $discount->discount_percent,
            'status' => 'active',
            'payment_status' => 'paid',
            'content' => 'تم شراء العرض: ' . $discount->title,
            'expiry_date' => now()->addDays($discount->valid_days),
            'payment_discount_id' => $discount->id,
        ]);

        DB::commit();

        return response()->json([
            'message' => 'تمت عملية الشراء بنجاح، وتم إنشاء الكوبون',
            'coupon' => $coupon
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'فشل الشراء',
            'error' => $e->getMessage()
        ], 500);
    }
}
}