<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function payInvoice(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'method' => 'required|string|in:credit,syriatel',
            'identifier' => 'required|string', // رقم البطاقة أو سيرياتيل كاش
        ]);

        $invoice = Invoice::find($request->invoice_id);

        // اختيار الحقل الصحيح حسب الطريقة
        if ($request->method === 'credit') {
            $account = Payment::where('credit_number', $request->identifier)->first();
        } else {
            $account = Payment::where('syriatel_cash', $request->identifier)->first();
        }

        if (!$account) {
            return response()->json(['error' => 'الحساب غير موجود'], 404);
        }

        if ($account->balance < $invoice->totalPrice) {
            return response()->json(['error' => 'الرصيد غير كافٍ'], 400);
        }

        // خصم المبلغ
        $account->balance -= $invoice->totalPrice;
        $account->save();

        // تحديث حالة الدفع
        $invoice->payment_status = 'paid';
        $invoice->payment_method = $request->method;
        $invoice->save();

        return response()->json([
            'message' => 'تم الدفع بنجاح',
            'new_balance' => $account->balance,
            'invoice_status' => $invoice->payment_status,
        ]);
    }
}

