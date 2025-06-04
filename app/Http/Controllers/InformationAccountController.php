<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Account;

class InformationAccountController extends Controller
{
    public function index()
    {
        $accounts = DB::table('accounts')->get();

        return response()->json($accounts);
    }

    public function getByEmail(Request $request)
    {
        $email = $request->input('email');

        $account = \DB::table('accounts')->where('Email', $email)->first();

        if ($account) {
            return response()->json($account);
        } else {
            return response()->json(['message' => 'Account not found'], 404);
        }
    }

    // إضافة تابع تحديث البيانات بناءً على الـ id
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'User_Name' => 'nullable|string|max:50|unique:accounts,User_Name,' . $id . ',Account_id',
                'Email' => 'required|string|email|max:50|unique:accounts,Email,' . $id . ',Account_id',
                'Password' => 'nullable|string|min:8',
                'Address' => 'nullable|string|max:50',
                'Phone_Number' => 'nullable|string|max:20',
                'Status' => 'nullable|integer',
                'First_Name' => 'nullable|string|max:50',
                'Last_Name' => 'nullable|string|max:50',
                'D_Experince_years' => 'nullable|integer',
                'D_Partial_certificate' => 'nullable|string|max:50',
                'Birth_date' => 'nullable|date',
                'Role_id' => 'nullable|integer',
                'CreatedBy' => 'nullable|integer',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
    
            $account = Account::find($id);
    
            if (!$account) {
                return response()->json(['message' => 'Account not found.'], 404);
            }
    
            // ✅ تخزين الصورة الجديدة إن وُجدت
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
    
                // تأكد إنو مجلد uploads موجود
                $destinationPath = public_path('uploads');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
    
                $destinationPath = public_path('uploads/images');
if (!file_exists($destinationPath)) {
    mkdir($destinationPath, 0755, true);
}

$image->move($destinationPath, $imageName);
$validated['image'] = 'uploads/images/' . $imageName;
            }
    
            // ✅ التحديث
            $account->update($validated);
    
            return response()->json([
                'message' => 'Account updated successfully!',
                'data' => $account
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function updateStatus(Request $request, $id)
    {
        try {
            // تحقق من صحة القيمة
            $request->validate([
                'Ststus' => 'required|integer|in:0,1' // فقط 0 أو 1
            ]);
    
            // إيجاد الحساب حسب ID
            $account = Account::find($id);
    
            if (!$account) {
                return response()->json(['message' => 'Account not found.'], 404);
            }
    
            // تحديث الحقل
            $account->Ststus = $request->Ststus;
            $account->save();
    
            return response()->json([
                'message' => 'Status updated successfully.',
                'data' => [
                    'Account_id' => $account->Account_id,
                    'New_Ststus' => $account->Ststus
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
