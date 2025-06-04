<?php

namespace App\Http\Controllers;

use App\Models\ConsultationReply;
use App\Models\Consultation; // تأكد من استيراد الموديل الخاص بالاستشارات
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConsultationReplyController extends Controller
{
    // إضافة رد على استشارة
    public function store(Request $request)
    {
        // التحقق من البيانات المدخلة
       // التحقق من البيانات المدخلة
$validator = Validator::make($request->all(), [
    'Consulation_id' => 'required|exists:consultations,Consulation_id', // تحقق من وجود الاستشارة
    'doctor_id' => 'required|exists:accounts,Account_id',
    'client_id' => 'required|exists:accounts,Account_id',
    'reply_text' => 'required|string',
]);

        // إذا كانت البيانات المدخلة غير صحيحة، أرجع رسالة خطأ
        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة.',
                'errors' => $validator->errors()
            ], 422);
        }

        // التحقق من أن الاستشارة موجودة
        $consultation = Consultation::find($request->Consulation_id);
        
        if (!$consultation) {
            // إذا كانت الاستشارة غير موجودة
            return response()->json([
                'message' => 'الاستشارة المطلوبة غير موجودة.'
            ], 404);
        }

        // تخزين الملف المرفق إذا كان موجودًا
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        // إنشاء رد جديد
        $reply = ConsultationReply::create([
            'Consulation_id' => $request->Consulation_id,
            'doctor_id' => $request->doctor_id,
            'client_id' => $request->client_id,
            'reply_text' => $request->reply_text,
            'attachment' => $attachmentPath,
        ]);

        // إرجاع الاستجابة بنجاح مع الرد الجديد
        return response()->json([
            'message' => 'تم إضافة الرد بنجاح!',
            'data' => $reply
        ], 201);
    }
}
