<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultation;
use Illuminate\Support\Facades\Validator;

class ConsultationController extends Controller
{
    public function store(Request $request)
    {
        try {
            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'Doctor_id' => 'required|integer',
                'Client_id' => 'required|integer',
                'Consulation_date' => 'required|date',
                'type' => 'required|string',
                'age' => 'required|integer',
                'weight' => 'required|integer',
                'prev_illness' => 'nullable|string',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'Follow_update' => 'nullable|date',
                'doctor_replay' => 'nullable|string',
            
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 422);
            }
    
            // رفع الصورة إن وجدت
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('uploads/images');
    
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
    
                $image->move($destinationPath, $imageName);
                $imagePath = 'uploads/images/' . $imageName;
            }
    
            // حفظ الاستشارة
            $consultation = Consultation::create([
                'Doctor_id' => $request->Doctor_id,
                'Client_id' => $request->Client_id,
                'Consulation_date' => $request->Consulation_date,
                'type' => $request->type,
                'age' => $request->age,
                'weight' => $request->weight,
                'prev_illness' => $request->prev_illness,
                'description' => $request->description,
                'Follow_update' => $request->Follow_update,
                'image' => $imagePath,
            ]);
    
            return response()->json([
                'message' => 'تم حفظ الاستشارة بنجاح',
                'consultation' => $consultation
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getByDoctor($id)
    {
        $consultations = Consultation::where('Doctor_id', $id)->get();
    
        return response()->json([
            'status' => 'success',
            'data' => $consultations
        ]);
    }
    public function getByClient($id)
    {
        $consultations = Consultation::where('Client_id', $id)->get();
    
        return response()->json([
            'status' => 'success',
            'data' => $consultations
        ]);
    }
    public function getByConsultationId($id)
    {
        // البحث باستخدام Consulation_id بدلاً من id
        $consultation = Consultation::where('id', $id)->first();
    
        if ($consultation) {
            return response()->json([
                'status' => 'success',
                'data' => $consultation
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'استشارة غير موجودة'
            ], 404);
        }
    }
    public function update(Request $request, $id)
{
    try {
        // العثور على الاستشارة
        $consultation = Consultation::findOrFail($id);

        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'Doctor_id' => 'sometimes|integer',
            'Client_id' => 'sometimes|integer',
            'Consulation_date' => 'sometimes|date',
            'type' => 'sometimes|string',
            'age' => 'sometimes|integer',
            'weight' => 'sometimes|integer',
            'prev_illness' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'Follow_update' => 'nullable|date',
            'doctor_replay' => 'nullable|string',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }

        // إذا تم رفع صورة جديدة
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/images');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);
            $consultation->image = 'uploads/images/' . $imageName;
        }

        // تحديث الحقول التي أُرسلت فقط
        $consultation->fill($request->except('image'))->save();

        return response()->json([
            'message' => 'تم تعديل الاستشارة بنجاح',
            'consultation' => $consultation
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'حدث خطأ: ' . $e->getMessage()
        ], 500);
    }
}

}