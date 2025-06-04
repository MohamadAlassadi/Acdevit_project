<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Notification;
use App\Models\Account;
use App\Models\FcmToken;
use App\Services\FcmService;

class NotificationController extends Controller
{
    protected $fcm;

    public function __construct(FcmService $fcm)
    {
        $this->fcm = $fcm;
    }


    // حفظ إشعار جديد + إرسال FCM
    public function registerf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dest_id' => 'required|exists:accounts,Account_id',
            'source_id' => 'required|exists:accounts,Account_id',
            'type' => 'required|string',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $not = new Notification();
        $not->dest_id = $request->dest_id;
        $not->source_id = $request->source_id;
        $not->type = $request->type;
        $not->content = $request->content;
        $not->save();

        // إرسال إشعار خارجي عبر FCM
        $this->fcm->sendNotificationToAccount(
            $request->dest_id,
            ucfirst($request->type),
            $request->content
        );

        return response()->json([
            'message' => 'Notification registered and sent successfully',
        ]);
    }

    // حفظ FCM Token للمستخدم
    public function saveFcmToken(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,Account_id',
            'token' => 'required|string',
        ]);

        FcmToken::updateOrCreate(
            ['account_id' => $request->account_id],
            ['token' => $request->token]
        );

        return response()->json([
            'message' => 'FCM Token saved successfully',
        ]);
    }

    // جلب الإشعارات حسب dest_id
    public function getbydest($dest_id)
    {
        $notifications = Notification::where('dest_id', $dest_id)
            ->orderBy('Date_added', 'desc')
            ->get();

        return response()->json([
            'message' => 'Notifications fetched successfully',
            'data' => $notifications
        ], 200);
    }

    // حذف إشعار معين
    public function deletenotification($itemId)
    {
        $not = Notification::findOrFail($itemId);
        $not->delete();

        return response()->json([
            'message' => 'Notification deleted successfully',
        ]);
    }
}
