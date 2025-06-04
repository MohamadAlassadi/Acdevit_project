<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ProductTransaction;
use App\Models\ProductBatch;
use App\Models\Product;
use App\Models\Order;
use App\Models\invoice;
class product_transactionsController extends Controller
{
    public function batchcounter($number)
    {
        $batch=ProductBatch::where('batch_number',$number)->first();
        if(!$batch){
            return response()->jsone('ni batche in this number');
        }
        $trans=ProductTransaction::where('batch_id',$batch->id)->get();
        if ($trans->isEmpty())
        {
        return response()->jsone('ni transaction in this number');  
        }
        return response()->json(['الدفعة الواردة '=>$batch,'التصدير لهذه الدفعة ']);
    }
    public function Profit_and_loss(Request $request)
    {
        
            $request->validate([
        'from' => 'required|date',
        'to' => 'required|date|after_or_equal:from',
    ]);

    $trans=ProductTransaction::where('type','OUT')->whereBetween('transaction_date',[$request->from,$request->to])->get();
 
        $totaloutQuantity = $trans->sum('quantity');
    $totaloutPrice = $trans->sum('totalPrice');

    //
        $batche = ProductBatch::whereBetween('manufacture_date', [$request->from, $request->to])->get();
    $totalinQuantity = $batche->sum('quantity');
    $totalinPrice = $batche->sum('totalPrice');
        $order=Order::whereBetween('Date_added',[$request->from,$request->to])->get();

    $orderIds=$order->pluck('id');
    $invoice=Invoice::whereIn('order_id',$orderIds)->where('discount_status',1)->whereIn('discount_type',['order','invoice'])->get();
    $discountAmount=$invoice->sum('discount_amount');
    $netProfit = $totaloutPrice - ($totalinPrice + $discountAmount);
$netProfitPercentage = $totaloutPrice > 0 ? ($netProfit / $totaloutPrice) * 100 : 0;

        return response()->json(['عدد الواردات'=>$totalinQuantity,'تكلفة الواردات '=>$totalinPrice,
        'عدد الصادرات'=>$totaloutQuantity,'تكلفة الصادرات '=>$totaloutPrice,'نواقص الحسومات'=>$discountAmount,'الارباح'=>$netProfit,
    'نسبة الارباح'=>($netProfitPercentage)
    ]);
    }

    public function getallout(Request $request):JsonResponse
    {
    $trans=ProductTransaction::where('type','OUT')->get();
    if($trans->isEmpty()){
        return response()->json("no transactions out found");
    }
        $totalQuantity = $trans->sum('quantity');
    $totalPrice = $trans->sum('totalPrice');
        return response()->json(['Transactions'=>$trans,'Quantity'=>$totalQuantity,'Total Price'=>$totalPrice,"Unit PRice"=>$totalPrice/$totalQuantity]);

    }

            public function getalloutbydate(Request $request):JsonResponse
    {
            $request->validate([
        'from' => 'required|date',
        'to' => 'required|date|after_or_equal:from',
    ]);

    $trans=ProductTransaction::where('type','OUT')->whereBetween('transaction_date',[$request->from,$request->to])->get();
    if($trans->isEmpty()){
        return response()->json("no transactions out found");
    }
        $totalQuantity = $trans->sum('quantity');
    $totalPrice = $trans->sum('totalPrice');
        return response()->json(['Transactions'=>$trans,'Quantity'=>$totalQuantity,'Total Price'=>$totalPrice,"Unit PRice"=>$totalPrice/$totalQuantity]);

    }

    //
        public function getoutbydate(Request $request,$product_id):JsonResponse
    {
            $request->validate([
        'from' => 'required|date',
        'to' => 'required|date|after_or_equal:from',
    ]);
        $batch=ProductBatch::with('product')->where('product_id',$product_id)->get();
    if ($batch->isEmpty()) {
        return response()->json([
            'message' => 'No batches found .',
            'data' => [],
        ], 404);
    }
       $batchIds = $batch->pluck('id');
    $trans=ProductTransaction::whereIn('batch_id',$batchIds)->where('type','OUT')->whereBetween('transaction_date',[$request->from,$request->to])->get();
    if($trans->isEmpty()){
        return response()->json("no transactions out found");
    }
        $totalQuantity = $trans->sum('quantity');
    $totalPrice = $trans->sum('totalPrice');
        return response()->json(['Transactions'=>$trans,'Quantity'=>$totalQuantity,'Total Price'=>$totalPrice,"Unit PRice"=>$totalPrice/$totalQuantity]);

    }
    //
    public function getout($product_id):JsonResponse
    {
        $batch=ProductBatch::with('product')->where('product_id',$product_id)->get();
    if ($batch->isEmpty()) {
        return response()->json([
            'message' => 'No batches found .',
            'data' => [],
        ], 404);
    }
       $batchIds = $batch->pluck('id');
    $trans=ProductTransaction::whereIn('batch_id',$batchIds)->where('type','OUT')->get();
    if($trans->isEmpty()){
        return response()->json("no transactions out found");
    }
        $totalQuantity = $trans->sum('quantity');
    $totalPrice = $trans->sum('totalPrice');
        return response()->json(['Transactions'=>$trans,'Quantity'=>$totalQuantity,'Total Price'=>$totalPrice,"Unit PRice"=>$totalPrice/$totalQuantity]);

    }
       public function INtransaction(): JsonResponse
{
    $product_transaction = ProductTransaction::with('batch')
        ->where('type', 'IN')
        ->get();

    return response()->json($product_transaction);
}
       public function OUTtransaction(): JsonResponse
{
    $product_transaction = ProductTransaction::with('batch')
        ->where('type', 'OUT')
        ->get();

    return response()->json($product_transaction);
}
}
