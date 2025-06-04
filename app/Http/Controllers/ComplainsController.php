<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\complains;


class ComplainsController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'source_id' => 'required|exists:accounts,Account_id',
            'dest_id' => 'required|exists:accounts,Account_id',
            'content' => 'nullable|string',
        ]);

        // توليد كود عشوائي

        // إنشاء الكوبون وتمرير الكود الصحيح
        $complain = complains::create([
            'source_id' => $validated['source_id'],
            'dest_id' => $validated['dest_id'],
            'content' => $validated['content'] ,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'complain created successfully.',
            'data'=>$complain
        ], 201);}
        public function getcomplain()
        {
            $complains=complains::get();
            if(!$complains){
                return response()->json('لا يوجد شكاوي ');

            }
            return response()->json(['Complains'=>$complains]);
        }
                public function getcomplainbyuser($id)
        {
            $complains=complains::where('source_id',$id)->get();
            if(!$complains){
                return response()->json('لا يوجد شكاوي لهذا المستخدم  ');

            }
            return response()->json(['Complains'=>$complains]);
        }
}
