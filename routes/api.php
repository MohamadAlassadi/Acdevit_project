<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\InformationAccountController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\selectproduct;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ShoppingCartItemController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\doctors;
use App\Http\Controllers\ConsultationReplyController;
use App\Http\Controllers\ProductBatchController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\invoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CoponController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\product_transactionsController;
use App\Http\Controllers\PaymentDiscountController;
use App\Http\Controllers\ComplainsController;

Route::get('/batchcounter/{number}',[product_transactionsController::class,'batchcounter']);// جلب صادرات كل المنتجات 
Route::post('/save-fcm-token', [NotificationController::class, 'saveFcmToken']);
Route::post('/Profit_and_loss',[product_transactionsController::class,'Profit_and_loss']);// جلب صادرات كل المنتجات 
Route::post('/discounts_Amount',[InvoiceController::class,'det_discounts_Amount']);
Route::get('/ProdactallOut',[product_transactionsController::class,'getallout']);// جلب صادرات كل المنتجات 
Route::post('/ProdactallOut',[product_transactionsController::class,'getalloutbydate']);// جلب صادرات كل المنتجات بين تاريخين
Route::post('/ProdactOut/{p_id}',[product_transactionsController::class,'getoutbydate']);// جلب صادرات منتج بين تاريخين
Route::get('/ProdactOut/{p_id}',[product_transactionsController::class,'getout']);// جلب صادرات منتج 
Route::post('/total-cost-between-dates', [ProductBatchController::class, 'calculateTotalCost']);// الواردات بدون تواريخ
Route::post('/total-cost-between-dates', [ProductBatchController::class, 'calculateTotalCostBetweenDates']);// الواردات بين تاريخين
Route::post('/batches-between-dates', [ProductBatchController::class, 'getBatchesBetweenDates']);// الواردات بين تاريخين لمنتج معين 
Route::get('/payment-discounts/{id}', [PaymentDiscountController::class, 'getbyid']);//
Route::get('/product-batches/{id}', [ProductBatchController::class, 'getbyproduct']);//واردات منتج معين
Route::get('/complains/{id}', [ComplainsController::class, 'getcomplainbyuser']);
Route::get('/complains', [ComplainsController::class, 'getcomplain']);
Route::post('/complaincreate', [ComplainsController::class, 'send']);//
Route::get('/payment-discountsclient', [PaymentDiscountController::class, 'status']);//  
Route::get('/payment-discounts', [PaymentDiscountController::class, 'index']);//
Route::post('/payment-discounts', [PaymentDiscountController::class, 'store']);//
Route::put('/payment-discounts/{id}', [PaymentDiscountController::class, 'update']);//   
Route::post('/payment-discounts/purchase', [PaymentDiscountController::class, 'purchase']);// 
Route::get('/OUTtransaction', [product_transactionsController::class, 'OUTtransaction']);// عرض واردات
Route::get('/INtransaction', [product_transactionsController::class, 'INtransaction']);// عرض صادرات
Route::get('/productoffer', [selectproduct::class, 'offerproduct']);// عرض  حسم على منتجات
Route::get('/coupons', [CouponController::class, 'index']);//جلب جميع الكوبونات
Route::get('/coupons/{dest_id}', [CouponController::class, 'getbydest']);// جلب جميع الكوبونات ليوزر محدد
Route::post('/coupons', [CouponController::class, 'store']);// انشاء كوبون
Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);// التحقق من ان كوبون لمستخدم وصالح
Route::post('/sendCopon', [CoponController::class, 'store']);//اضافة كوبون 
Route::post('/products/{id}/remove-offer', [ProductController::class, 'removeOffer']);//حذف عرض
Route::post('/products/{id}/apply-offer', [ProductController::class, 'applyOffer']);// اضافة عرض 
Route::post('/deletenot/{destid}',[NotificationController::class,'deletenotification']);//حذف اشعار 
Route::get('/getnotification/{destid}',[NotificationController::class,'getbydest']);//جلب اشعارات
Route::post('/addnotification',[NotificationController::class,'registerf']);//اضافة اشعار
Route::get('/accounts/{id}', [AccountController::class, 'getAccountById']);//لجلب معلومات الحساب حسب id
Route::get('/accounts/search2/{term}', [AccountController::class, 'searchByName2']);//للبحث عن حساب 
Route::get('/accounts/search/{term}', [AccountController::class, 'searchByName']);//للبحث عن طبيب
Route::get('/products/search/{term}', [ProductController::class, 'searchByName']);//للبحث عن منتج
Route::get('/articles/search/{letter}', [ArticleController::class, 'searchByTitle']);// للبحث عن مقال
Route::post('/send-otp', [AccountController::class, 'sendOtp']);// ارسال رمز التحقق
Route::post('/verify-otp', [AccountController::class, 'verifyOtp']);// التحقق من رمز التحقق
Route::post('/reset-password-otp', [AccountController::class, 'resetPasswordWithOtp']);// اعادة تعيين كلمة السر
Route::put('/invoices/{invoiceId}/payment-status', [InvoiceController::class, 'updatePaymentStatus']);//تعديل حالة الدفع 
Route::put('/deliveries/{id}/status', [DeliveryController::class, 'updateDeliveryStatus']);//تعديل حالة التوصيل 
Route::get('/deliveries/{id}/invoice', [DeliveryController::class, 'getInvoiceByDelivery']);//جلب الفاتورة الخاصة بالتوصيل
Route::get('/deliveries/driver/{id}', [DeliveryController::class, 'show']);// جلب عمليات التوصيل الخاصة بالديليفري
Route::put('/orders/{id}/status', [OrderController::class, 'updateOrderStatus']);//تعديل حالة الطلب
Route::get('/order-items/{order_id}', [OrderController::class, 'getOrderItemsByOrderId']);//جلب عناصر الطلب حسب الطلب 
Route::get('/orders/by-date', [OrderController::class, 'getOrdersByDate']);//جلب الطلبات حسب التاريخ 
Route::get('/orders', [OrderController::class, 'getOrdersWithProducts']);//jjjjjjjjj
Route::get('/accounts/deliveries', [AccountController::class, 'getDeliveryAccounts']);//جلب حسابات الموصلين 
Route::post('/pay-invoice', [PaymentController::class, 'payInvoice']);
Route::post('/invoices', [InvoiceController::class, 'store']);//انشاء عملية فاتورة 
Route::post('/deliveries', [DeliveryController::class, 'store']);//انشاء عملية توصيل 
Route::post('/cancelOrder/{id}', [OrderController::class, 'cancelOrder']); //cancel order 
Route::post('/process-order', [OrderController::class, 'processCartToOrder']);//اضافة طلب 
Route::post('/product-batches', [ProductBatchController::class, 'store']);//اضافة دفعة 
Route::post('consultation-replies', [ConsultationReplyController::class, 'store']);// هاد لا تمد ايدك عليه 
Route::post('check-email', [AuthController::class, 'checkEmail']);
Route::put('/accounts/{id}/password', [AccountController::class, 'updatePassword']);
Route::post('/consultations', [ConsultationController::class, 'store']);// هاد اضافة استشارة
Route::post('/add-to-cart', [ShoppingCartItemController::class, 'addItemToCart']);// اضافة عنصر للسلة 
Route::get('/carts', [CartController::class, 'index']); // للحصول على جميع السلات
Route::get('/carts/{id}', [CartController::class, 'show']); // للحصول على سلة معينة
Route::get('/carts/client/{id}', [CartController::class, 'getCartByClientId']); // للحصول على سلة معينة
Route::post('/carts', [CartController::class, 'store']); // لإنشاء سلة جديدة
Route::put('/carts/{id}', [CartController::class, 'update']); // لتحديث سلة معينة
Route::delete('/carts/{id}', [CartController::class, 'destroy']); // لحذف سلة معينة
Route::get('/cart-items/{cartId}', [ShoppingCartItemController::class, 'getCartItems']); // عرض العناصر
Route::put('/cart-items/{itemId}', [ShoppingCartItemController::class, 'updateCartItem']); // تعديل عنصر
Route::delete('/cart-items/{itemId}', [ShoppingCartItemController::class, 'deleteCartItem']); // حذف عنصر
Route::get('/product', [selectproduct::class, 'index']);// عرض منتجات
Route::get('/articles', [ArticleController::class, 'index']);
Route::post('/register', [AccountController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/account-by-email', [InformationAccountController::class, 'getByEmail']);
Route::put('accounts/{id}', [InformationAccountController::class, 'update']);
Route::get('/accounts', [InformationAccountController::class, 'index']);
Route::get('/accountdoctor', [AccountController::class, 'getDoctors']);//هاد عرض الدكاترى 
Route::get('/consultations/doctor/{id}', [ConsultationController::class, 'getByDoctor']);// هاد عرض الاستشارات المرسلة لدكتور 
Route::get('consultations/client/{id}', [ConsultationController::class, 'getByClient']);
Route::get('consultations/{id}', [ConsultationController::class, 'getByConsultationId']);
Route::patch('/consultations/{id}', [ConsultationController::class, 'update']);
Route::post('/articles', [ArticleController::class, 'store']);
Route::post('/consultations/update/{id}', [ConsultationController::class, 'update']);
Route::post('/registerd', [AccountController::class, 'regd']);
Route::put('/accounts/{id}/status', [InformationAccountController::class, 'updateStatus']);
Route::post('/products', [SelectProduct::class, 'store']);
Route::get('/product-batches', [ProductBatchController::class, 'index']);
Route::get('/invoices/by-order/{order_id}', [invoiceController::class, 'getByOrderId']);//جلب الفاتورة حسب الطلب
Route::get('/deliveries/order/{orderId}', [DeliveryController::class, 'getDeliveryByOrderId']);
Route::get('/invoices', [invoiceController::class, 'index']);
Route::get('/orders/by-client/{client_id}', [OrderController::class, 'getByClientId']);
Route::get('orders/driver/{driver_id}', [OrderController::class, 'getByDriverId']);
Route::delete('/articles/{id}', [ArticleController::class, 'destroy']);
Route::get('/deliveries', [DeliveryController::class, 'index']);
Route::put('/articles/{id}', [ArticleController::class, 'update']);//تعديل مقال
Route::put('/products/{id}', [ProductController::class, 'update']);// تع\
Route::put('/deliveries/by-order/{order_id}/status', [DeliveryController::class, 'updateDeliveryStatusByOrderId']);
