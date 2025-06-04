<?php

namespace App\Http\Controllers;

use App\Models\Account; // استيراد موديل الحساب
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // وظيفة لتسجيل الدخول
    public function login(Request $request)
    {
        // التحقق من المدخلات
        $request->validate([
            'password' => 'required|string',
            'email' => 'nullable|email',
            'username' => 'nullable|string',
        ]);

        $account = null;

        // البحث عن الحساب باستخدام البريد الإلكتروني أو اسم المستخدم
        if ($request->has('email')) {
            $account = Account::where('Email', $request->email)->first();
        } elseif ($request->has('username')) {
            $account = Account::where('User_Name', $request->username)->first();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'يجب إدخال البريد الإلكتروني أو اسم المستخدم.'
            ], 400);
        }

        // إذا لم يكن الحساب موجودًا
        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'الحساب غير موجود.'
            ], 401);
        }

        // التحقق من كلمة المرور (إذا كانت مشفرة باستخدام Hash)
        if (!Hash::check($request->password, $account->Password)) {
            return response()->json([
                'success' => false,
                'message' => 'كلمة المرور غير صحيحة.'
            ], 401);
        }

        // إذا تم التحقق من جميع الأمور بنجاح
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => [
                'id' => $account->Account_id,
                'email' => $account->Email,
                'username' => $account->User_Name,
                'role_id' => $account->Role_id, // تأكد من وجود هذا الحقل في الجدول
                'ststus' => $account->Ststus, // إضافة حقل الـ Status من قاعدة البيانات
            ]
        ]);
    }

     //password
     public function checkEmail(Request $request)
     {
         // التحقق من صحة المدخلات
         $request->validate([
             'email' => 'required|email',
         ]);
     
         // البحث عن الحساب باستخدام البريد الإلكتروني
         $account = Account::where('Email', $request->email)->first();
     
         if (!$account) {
             return response()->json([
                 'success' => false,
                 'message' => 'البريد الإلكتروني غير موجود.'
             ], 404);
         }
     
         return response()->json([
             'success' => true,
             'message' => 'تم التحقق من البريد الإلكتروني بنجاح.',
             'user_id' => $account->Account_id,
         ]);
     }
}
