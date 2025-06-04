<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Coupon;

class CouponController extends Controller
{
    // جلب كل الكوبونات
    public function index()
    {
        $coupons = Coupon::with(['sourceAccount', 'destAccount'])->get();
        return response()->json(['success' => true, 'data' => $coupons]);
    }
    public function getbydest($dest_id)
    {
        $coupons = Coupon::with(['sourceAccount', 'destAccount'])->where("dest_id",$dest_id)->get();
        return response()->json(['success' => true, 'data' => $coupons]);
    }
    private function generateCouponCode()
    {
        return strtoupper(Str::random(8)); // مثال: HGT2X7KL
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_id' => 'required|exists:accounts,Account_id',
            'dest_id' => 'required|exists:accounts,Account_id',
            'type' => 'required|in:order,delivery,invoice',
            'discount_percent' => 'required|integer|min:0|max:100',
            'expiry_date' => 'required|date|after:today',
            'content' => 'nullable|string',
        ]);

        // توليد كود عشوائي
        $code = $this->generateCouponCode();

        // إنشاء الكوبون وتمرير الكود الصحيح
        $coupon = Coupon::create([
            'source_id' => $validated['source_id'],
            'dest_id' => $validated['dest_id'],
            'type' => $validated['type'],
            'discount_percent' => $validated['discount_percent'],
            'expiry_date' => $validated['expiry_date'],
            'code' => $code,  // هنا نرسل كود صحيح
            'status' => 'active',
            'content' => $validated['content'] ?? 'Auto-generated coupon',
            'payment_status' => null,  // تأكد أن العمود يقبل null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon created successfully.',
            'data' => $coupon
        ], 201);
    }

    public function validateCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'user_id' => 'required|exists:accounts,Account_id',
        ]);

        $coupon = Coupon::where('code', $validated['code'])
            ->where('dest_id', $validated['user_id'])
            ->where('status', 'active')
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->first();

        if ($coupon) {
            return response()->json([
                'valid' => true,
                'message' => 'Coupon is valid.',
                'discount_percent' => $coupon->discount_percent,
                'type' => $coupon->type,
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => 'Coupon is invalid, expired, or not assigned to this user.',
        ], 404);
    }
}
