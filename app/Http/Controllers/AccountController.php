<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Account;

class AccountController extends Controller
{
    public function getAccountById($id)
{
    $account = \App\Models\Account::find($id);

    if (!$account) {
        return response()->json(['message' => 'الحساب غير موجود'], 404);
    }

    return response()->json($account);
}

    public function searchByName2($term)
{
    // البحث في First_Name أو Last_Name بدون شرط Role_id
    $accounts = Account::where(function ($query) use ($term) {
        $query->where('First_Name', 'LIKE', $term . '%')
              ->orWhere('Last_Name', 'LIKE', $term . '%');
    })->get();

    return response()->json($accounts);
}

    public function searchByName($term)
    {
        // البحث في الاسم الأول أو الأخير بشرط Role_id = 2
        $accounts = Account::where('Role_id', 2)
            ->where(function ($query) use ($term) {
                $query->where('First_Name', 'LIKE', $term . '%')
                      ->orWhere('Last_Name', 'LIKE', $term . '%');
            })
            ->get();

        return response()->json($accounts);
    }

    public function resetPasswordWithOtp(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:accounts,Email',
        'password' => 'required|min:6|confirmed',
    ]);

    $account = Account::where('Email', $request->email)->first();
    $account->Password = bcrypt($request->password);
    $account->save();

    return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح']);
}

    public function verifyOtp(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code' => 'required',
    ]);

    $otp = Otp::where('email', $request->email)
              ->where('code', $request->code)
              ->first();

    if (!$otp) {
        return response()->json(['message' => 'رمز غير صحيح'], 400);
    }

    if (Carbon::now()->gt($otp->expires_at)) {
        return response()->json(['message' => 'انتهت صلاحية الرمز'], 400);
    }

    $otp->delete(); // حذف الرمز بعد التحقق

    return response()->json(['message' => 'تم التحقق بنجاح']);
}

public function sendOtp(Request $request)
{
    \Log::info('Request Data: ', $request->all());

    $request->validate([
        'email' => 'required|email|exists:accounts,Email',
    ]);

    $code = rand(100000, 999999);
    $expiresAt = Carbon::now()->addMinutes(5);

    Otp::where('email', $request->email)->delete();

    Otp::create([
        'email' => $request->email,
        'code' => $code,
        'expires_at' => $expiresAt,
    ]);

    Mail::raw("رمز التحقق الخاص بك هو: $code", function ($message) use ($request) {
        $message->to($request->email)
                ->subject('رمز التحقق OTP');
    });

    return response()->json(['message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني']);
}

    public function register(Request $request)
{
    // التحقق من صحة المدخلات
    $validator = Validator::make($request->all(), [
        'First_Name' => 'required|string|max:255',
        'Last_Name' => 'required|string|max:255',//انسخها وغير الاسم ليزور نايم 
        'Email' => 'required|email|unique:accounts',
        'Password' => 'required|string|min:6',
        'Phone_Number' => 'required|numeric',

    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 400);
    }

    // إنشاء حساب جديد
    $account = new Account();
    $account->First_Name = $request->First_Name;
    $account->Last_Name = $request->Last_Name;
    $account->Email = $request->Email;
    $account->Password = bcrypt($request->Password); // تشفير كلمة المرور
    $account->Phone_Number = $request->Phone_Number;
    $account->save();

    // إنشاء توكن باستخدام Sanctum
    $token = $account->createToken('AppName')->plainTextToken;

    return response()->json([
        'message' => 'Registration successful',
        'token' => $token,
        'user_id' => $account->Account_id,
    ]);
}
public function getDoctors()
{
    $doctors = Account::where('role_id', 2)
                      ->where('Ststus', 1) // تأكد أن هذا الاسم هو نفسه في قاعدة البيانات
                      ->orderBy('created_at', 'desc')
                      ->get();

    return response()->json([
        'status' => 'success',
        'data' => $doctors
    ]);
}
//updatepassword 
public function updatePassword(Request $request, $id)
{
    // التحقق من صحة الإدخالات
    $validator = Validator::make($request->all(), [
        'Password' => 'required|string|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 400);
    }

    $account = Account::find($id);

    if (!$account) {
        return response()->json([
            'message' => 'Account not found',
        ], 404);
    }

    $account->Password = bcrypt($request->Password);
    $account->save();

    return response()->json([
        'message' => 'Password updated successfully',
    ]);
}
public function regd(Request $request)
{
    // التحقق من صحة المدخلات
    $validator = Validator::make($request->all(), [
        'First_Name' => 'required|string|max:255',
        'Last_Name' => 'required|string|max:255',
        'Email' => 'required|email|unique:accounts',
        'Password' => 'required|string|min:6',
        'Phone_Number' => 'required|numeric',
        'Role_id' => 'required|integer',
        'D_Experince_years' => 'nullable|integer',
        'D_Partial_certificate' => 'nullable|string|max:255',
        'Address' => 'nullable|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 400);
    }

    // رفع الصورة إن وُجدت
    $imagePath = null;
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->move(public_path('uploads/images'), $imageName);
        $imagePath = 'uploads/images/' . $imageName;
    }

    // إنشاء حساب جديد
    $account = new Account();
    $account->First_Name = $request->First_Name;
    $account->Last_Name = $request->Last_Name;
    $account->Email = $request->Email;
    $account->Password = bcrypt($request->Password);
    $account->Phone_Number = $request->Phone_Number;
    $account->Role_id = $request->Role_id;
    $account->D_Experince_years = $request->D_Experince_years;
    $account->D_Partial_certificate = $request->D_Partial_certificate;
    $account->Address = $request->Address;
    $account->image = $imagePath;
    $account->save();

    // إنشاء توكن باستخدام Sanctum
    $token = $account->createToken('AppName')->plainTextToken;

    return response()->json([
        'message' => 'Registration successful',
        'token' => $token,
        'account' => $account
        
    ]);
}
}
