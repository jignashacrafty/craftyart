<?php

use App\Events\PrivateUserEvent;
use App\Events\TestPusherEvent;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AutomationConfigController;
use App\Http\Controllers\Api\CaricatureCategoryController;
use App\Http\Controllers\Api\CaricatureController;
use App\Http\Controllers\Api\PhonePePaymentApiController;
use App\Http\Controllers\Api\PhonePePaymentApiController2;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PReviewController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SitemapController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\BroadcastAuthController;
use App\Http\Controllers\BroadcastAuthController2;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Jobs\ExportDesignCampaignController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\CategoryTemplatesApiController;
use App\Http\Controllers\Api\Colors\ColorAPIController;
use App\Http\Controllers\Api\Interest\InterestAPIController;
use App\Http\Controllers\Api\Language\LanguageAPIController;
use App\Http\Controllers\Api\Religion\ReligionAPIController;
use App\Http\Controllers\Api\Frame\FrameApiController;
use App\Http\Controllers\Api\PageApiController;
use App\Http\Controllers\Api\Size\SizeAPIController;
use App\Http\Controllers\Api\Style\StyleAPIController;
use App\Http\Controllers\Api\TemplateApiController;
use App\Http\Controllers\Api\Theme\ThemeAPIController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\VirtualCategoryController;
use App\Http\Controllers\Revenue\RevenueController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});


// start of userApi
//Route::post('V3/createUser', [UserController::class, 'createUser']);
//Route::post('V3/getUser', [UserController::class, 'userExist']);
//Route::post('V3/updateUser', [UserController::class, 'updateUser']);
//Route::post('V3/deleteUser', [UserController::class, 'deleteUser']);
//Route::post('V3/getCoinTransaction', [UserController::class, 'getCoinTransaction']);
//Route::post('/logout', [UserController::class, 'logout']);


//Route::any('broadcasting/auth', function (Illuminate\Http\Request $request) {
//    Log::info('Auth attempt', [
//        'headers' => $request->headers->all(),
//        'user' => $request->user(),
//    ]);
//
//    if (!$request->user()) {
//        return response()->json(['message' => 'Unauthorized'], 403);
//    }
//    return response()->json(['message' => 'Unauthorized'], 403);
//})->middleware('auth:api');

Route::post('/broadcasting/auth', function (Request $request) {
    try {
        return app(BroadcastAuthController::class)->authenticate($request);
    } catch (Exception) {
        return response()->json(['error' => 'Authentication failed'], 403);
    }
});

Route::get('/test-user-message', function (Request $request) {
    try {
        $userId = $request->get('user');
        $message = $request->get('message', 'Test message for user channel');

        Log::info('🎬 TEST API CALLED', [
            'user_id' => $userId,
            'message' => $message,
            'all_params' => $request->all(),
            'url' => $request->fullUrl()
        ]);

        if (!$userId) {
            Log::warning('❌ TEST API FAILED - Missing user ID');
            return response()->json(['error' => 'User ID is required'], 400);
        }

        // Create event instance
        $event = new UserTestMessage($userId, $message);

        Log::info('📝 EVENT INSTANCE CREATED', [
            'event_class' => get_class($event),
            'channel' => 'user-' . $userId,
            'broadcast_event_name' => $event->broadcastAs()
        ]);

        // Broadcast the event
        event($event);

        Log::info('✅ BROADCAST FUNCTION CALLED', [
            'user_id' => $userId,
            'channel' => 'user-' . $userId,
            'timestamp' => now()->toISOString()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test message sent successfully',
            'data' => [
                'user_id' => $userId,
                'message' => $message,
                'channel' => 'user-' . $userId,
                'event' => 'user.test.message',
                'timestamp' => now()->toISOString()
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('💥 TEST API ERROR', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => $userId ?? 'unknown'
        ]);

        return response()->json([
            'error' => 'Failed to send test message',
            'details' => $e->getMessage()
        ], 500);
    }
});

//Route::any("sendTestMessage",[AuthController::class,'sendTestMessage']);
//Route::any("sendTestMessage2",[AuthController::class,'sendTestMessage2']);
//public function sendTestMessage2(Request $request): array|string
//{
//    $orderId = $request->get('order');
////        EmailControllerAlias::sendPurchaseDropoutEmail(Order::whereId($orderId)->first());
//    EmailControllerAlias::sendPurchaseDropoutEmail(Order::whereId(1)->first());
////        EmailControllerAlias::sendPurchaseDropoutEmail(Order::whereId(2)->first());
//    return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Test Message Event"));
//}

Route::any('instantSend/{id}', [ExportDesignCampaignController::class, 'instantSend']);
Route::any('instantTemplatePurchase/{id}', [EmailController::class, 'sendInstantTemplatePurchaseMessage']);

// start of userApi
Route::any('user', [UserController::class, 'createUser']);
Route::post('V3/getUser', [UserController::class, 'userExist']);
Route::post('V3/updateUser', [UserController::class, 'updateUser']);
Route::post('V3/deleteUser', [UserController::class, 'deleteUser']);
Route::post('V3/getCoinTransaction', [UserController::class, 'getCoinTransaction']);
Route::post('/logout', [UserController::class, 'logout']);

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/signup', [AuthController::class, 'signup']);
Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::post('auth/user', [AuthController::class, 'getUser']);
Route::post('auth/googleSignIn', [AuthController::class, 'handleGoogleSignIn']);

// Admin/Staff Simple Login (No device tracking - for Postman/API testing)
Route::post('admin/login', [App\Http\Controllers\Api\AdminAuthController::class, 'login']);
Route::post('admin/logout', [App\Http\Controllers\Api\AdminAuthController::class, 'logout']);
Route::middleware('auth:api')->group(function () {
    Route::get('admin/me', [App\Http\Controllers\Api\AdminAuthController::class, 'me']);
});


Route::post('createOrder', [AuthController::class, 'createOrder']);

// end of userApi

// start of verification
Route::post('V3/sendVerificationOTP', 'App\Http\Controllers\Api\VerificationController@sendVerificationOTP');
Route::post('V3/verifyOTP', 'App\Http\Controllers\Api\VerificationController@verifyOTP');
// end of verification

// start of userFeedback
Route::post('V3/sendFeedback', 'App\Http\Controllers\Api\FeedbackController@sendFeedback');
Route::post('V3/sendMessage', 'App\Http\Controllers\Api\FeedbackController@sendMessage');
Route::post('V3/getChatList', 'App\Http\Controllers\Api\FeedbackController@getChatList');
// end of userFeedback

// start of Fonts
Route::post('V3/getApiFont', 'App\Http\Controllers\Api\FontController@getApiFont');
Route::post('getEditorFont', 'App\Http\Controllers\Api\FontController@getEditorFont');
// end of Fonts

// start of videos
Route::post('getVideos', 'App\Http\Controllers\Api\VideoApiController@getAll');
Route::post('getVideo', 'App\Http\Controllers\Api\VideoApiController@getTemplate');
//end of videos

// start of posterData
Route::post('V3/getAll', 'App\Http\Controllers\Api\PosterController@getAll');
Route::post('V3/getCategoryPosters', 'App\Http\Controllers\Api\PosterController@getCategoryPosters');
Route::post('V3/getPosterDetail', 'App\Http\Controllers\Api\PosterController@getPosterDetail');
Route::post('V3/updatePoster', 'App\Http\Controllers\Api\PosterController@updatePoster');

Route::get('generateUsernamesForAll', [UserApiController::class, 'generateUsernamesForAll']);


Route::any('tmp/k', [TemplateApiController::class, 'getKeyTemplates']);
Route::any('tmp/page', [TemplateApiController::class, 'getPosterPage']);
Route::any('tmp/s', [TemplateApiController::class, 'getSpecialTemplates']);
Route::any('catall', [CategoryTemplatesApiController::class, 'getAllCategoriesList']);
Route::any('dashboard', [CategoryTemplatesApiController::class, 'getDashboardDatas']);
Route::any('cat', [CategoryTemplatesApiController::class, 'getCategories']);
Route::any('page', [PageApiController::class, 'getPage']);
Route::any('promocode', [CategoryTemplatesApiController::class, 'applyPromoCode']);
Route::post('getPlanData', [PlanController::class, 'getPlanData']);
Route::post('getplanDetails', [PlanController::class, 'getplanDetails']);
Route::post('getplanDetails2', [PlanController::class, 'getplanDetails2']);
Route::post('getAdditionalUserPlan', [PlanController::class, 'getAdditionalUserPlan']);
Route::post('getReviews', [PReviewController::class, 'getReviews']);
Route::any('get_portfolio', [UserApiController::class, 'getPortfolio']);
Route::any('checkSubscriptionDetails', [PlanController::class, 'checkSubscriptionDetails']);

Route::any("send-whatsapp", [WhatsAppController::class, 'send']);

//Route::any('sitemap', [SitemapController::class, 'sitemap']);
Route::get('new-sitemap.xml', [SitemapController::class, 'sitemapIndex']);
Route::get('new-sitemap/categoriesV1.xml', [SitemapController::class, 'categoriesSitemap']);
Route::get('new-sitemap/others.xml', [SitemapController::class, 'otherSitemap']);
Route::get('new-sitemap/categoriesV2.xml', [SitemapController::class, 'newCategoriesSitemap']);
Route::get('new-sitemap/categoriesV2/{parent}.xml', [SitemapController::class, 'parentSitemap']);
Route::get('new-sitemap/categoriesV2/{parent}/{child}.xml', [SitemapController::class, 'childSitemap']);


Route::get('getTemplateRates', [TemplateApiController::class, 'getTmpRates']);

Route::get('getVideoRates', [TemplateApiController::class, 'getVideoRates']);
Route::post('change_email_subscribe', [UserApiController::class, 'changeEmailsubscribe']);
Route::post('email_subscribe_status', [UserApiController::class, 'getSubscribeStatus']);



Route::get('/is-template', [PlanController::class, 'is_template']);
Route::get('/template-limit', [PlanController::class, 'template_limit']);
Route::post('/setPlanLimitBySubPlanId', [PlanController::class, 'setPlanLimitBySubPlanId']);


Route::post('/sendEmailTemplate', [AutomationConfigController::class, 'sendEmailTemplate']);
Route::post('/sendWhatsappTemplateMessage', [AutomationConfigController::class, 'sendWhatsappTemplateMessage']);
Route::post('/sendAutomationFromConfig', [AutomationConfigController::class, 'sendAutomationFromConfig']);
Route::post('/sendWhatsappTemplateMessage2', [AutomationConfigController::class, 'sendWhatsappTemplateMessage2']);

//Route::post('/caricature/images', [CaricatureController::class, 'storeMultipleImages']);

// Image handling APIs
Route::post('/images/save-multiple', [CaricatureController::class, 'saveMultipleImages']);
Route::post('/images/delete-multiple', [CaricatureController::class, 'deleteMultipleImages']);


//Route::any('cat', [CaricatureController::class, 'getCategories']);

//Route::post("/caricature/category/addOrUpdate",[CaricatureController::class,"categoryAddOrUpdate"]);
//Route::post("/caricature/category/getCat",[CaricatureController::class,"getCaricatureCategory"]);
//Route::post("/caricature/category/getCat/{id}",[CaricatureController::class,"getCaricatureCategoryById"]);
//Route::post("/caricature/category/change-status",[CaricatureController::class,"changeStatusCaricatureCategory"]);

//Route::post("/caricature/attire/add",[CaricatureController::class,"attireAdd"]);
//Route::post("/caricature/attire/updateSeo",[CaricatureController::class,"updateSeoAttire"]);

//Route::post('/caricature/attire/get', [CaricatureController::class, 'getAttireData']);
//Route::post('/caricature/attire/get/{id}', [CaricatureController::class, 'getAttireDataById']);
//Route::post('/caricature/attire/change-status', [CaricatureController::class, 'changeStatusAttire']);

Route::any('caricatures', [CaricatureController::class, 'getCategories']);
Route::any('caricature', [CaricatureController::class, 'getCategory']);
Route::any('caricature/attire', [CaricatureController::class, 'getAttire']);

Route::any('phonepe/payment', [PhonePePaymentApiController2::class, 'payment']);
Route::post('phonepe/status/{transaction_id}', [PhonePePaymentApiController2::class, 'checkPaymentStatus']);
Route::any('payment/webhook', [PhonePePaymentApiController2::class, 'webhook']);
Route::any('payment/createAutopayMandate', [PhonePePaymentApiController2::class, 'createAutopayMandate']);
Route::get('phonepe/order-status/{merchantOrderId}', [PhonePePaymentApiController2::class, 'checkOrderStatus']);
Route::get('phonepe/refund', [PhonePePaymentApiController2::class, 'refundPhonePePayment']);

// PhonePe AutoPay API Routes (No CSRF token required)
Route::any('phonepe/autopay/setup', [App\Http\Controllers\Api\PhonePeAutoPayController::class, 'setupSubscription']);
Route::get('phonepe/autopay/status/{merchantSubscriptionId}', [App\Http\Controllers\Api\PhonePeAutoPayController::class, 'getSubscriptionStatus']);
Route::post('phonepe/autopay/redeem', [App\Http\Controllers\Api\PhonePeAutoPayController::class, 'triggerManualRedemption']);
Route::post('phonepe/autopay/cancel', [App\Http\Controllers\Api\PhonePeAutoPayController::class, 'cancelSubscription']);
Route::post('phonepe/autopay/generate-qr', [App\Http\Controllers\Api\PhonePeAutoPayController::class, 'generateQRCode']);
Route::post('phonepe/autopay/validateUpi', [App\Http\Controllers\Api\PhonePeAutoPayController::class, 'validateUpi']);
Route::any('phonepe/autopay/webhook', [App\Http\Controllers\Api\PhonePeAutoPayController::class, 'handleWebhook']); // Webhook for automatic status updates

// JSON file handling APIs
Route::post('/json/save', [CaricatureController::class, 'saveJson']);
Route::post('/json/delete-multiple', [CaricatureController::class, 'deleteMultipleJson']);


Route::any('getOfferPopUp', [PlanController::class, 'getOfferPopUp']);

// Order User Management APIs
// Order User Authentication (Simple JSON responses, no encryption)
Route::prefix('order-user-auth')->middleware('api')->group(function () {
    Route::post('/login', [App\Http\Controllers\Api\OrderUserAuthController::class, 'login']);
    Route::post('/verify-token', [App\Http\Controllers\Api\OrderUserAuthController::class, 'verifyToken']);
    Route::post('/logout', [App\Http\Controllers\Api\OrderUserAuthController::class, 'logout']);
});

// Order User Management APIs
Route::prefix('order-user')->middleware('api')->group(function () {
    // No auth required routes
    Route::get('/followup-labels', [App\Http\Controllers\Api\OrderUserApiController::class, 'getFollowupLabels']);
    Route::get('/get-plans', [App\Http\Controllers\Api\OrderUserApiController::class, 'getPlans']);
    Route::post('/validate-email', [App\Http\Controllers\Api\OrderUserApiController::class, 'validateEmail']);

    // Optional auth routes (work without auth but better with auth)
    Route::get('/', [App\Http\Controllers\Api\OrderUserApiController::class, 'index']);
    Route::get('/get-user-usage', [App\Http\Controllers\Api\OrderUserApiController::class, 'getUserUsage']);
    Route::get('/purchase-history/{userId}', [App\Http\Controllers\Api\OrderUserApiController::class, 'getPurchaseHistory']);
    Route::get('/check-phonepe-status/{merchantOrderId}', [App\Http\Controllers\Api\OrderUserApiController::class, 'checkPhonePeStatus']);
    Route::get('/check-razorpay-status/{paymentLinkId}', [App\Http\Controllers\Api\OrderUserApiController::class, 'checkRazorpayStatus']);
    Route::get('/new-orders', [App\Http\Controllers\Api\OrderUserApiController::class, 'getNewOrders']);

    // Auth required routes (use encrypted token middleware)
    Route::middleware(\App\Http\Middleware\ValidateEncryptedToken::class)->group(function () {
        Route::post('/followup-update', [App\Http\Controllers\Api\OrderUserApiController::class, 'followupUpdate']);
        Route::post('/create-payment-link', [App\Http\Controllers\Api\OrderUserApiController::class, 'createPaymentLink']);
        Route::post('/create-order', [App\Http\Controllers\Api\OrderUserApiController::class, 'createOrder']);
        Route::post('/add-transaction-manually', [App\Http\Controllers\Api\OrderUserApiController::class, 'addTransactionManually']);
    });
});

Route::post('V4/getAll', 'App\Http\Controllers\Api\TemplateApiController@getAll');
Route::post('V4/getCategoryPosters', 'App\Http\Controllers\Api\TemplateApiController@getCategoryPosters');
Route::post('V4/getPosterDetail', 'App\Http\Controllers\Api\TemplateApiController@getPosterDetail');
Route::post('V4/getPosterPage', 'App\Http\Controllers\Api\TemplateApiController@getPosterPage');

Route::post('specialTemplates', 'App\Http\Controllers\Api\TemplateApiController@searchSpecialTemplates');
Route::post('getKeyTemplates', 'App\Http\Controllers\Api\TemplateApiController@getKeyTemplates');
Route::get('getKeyTemplates', 'App\Http\Controllers\Api\TemplateApiController@getKeyTemplates');
Route::post('V4/getAll3', 'App\Http\Controllers\Api\TemplateApiController@getAll3');
Route::post('getCats', 'App\Http\Controllers\Api\TemplateApiController@getCats');
Route::post('getDashboard', 'App\Http\Controllers\Api\TemplateApiController@getDashboardDatas');
Route::post('getDatas', 'App\Http\Controllers\Api\TemplateApiController@getAllDatasForWeb');
Route::post('getCategoryDatas', 'App\Http\Controllers\Api\TemplateApiController@getCategoryPostersForWeb');
// end of posterData

// start of stickerData
Route::post('V3/getStickers', 'App\Http\Controllers\Api\StickerController@getStickers');
Route::post('V3/getCategoryStickers', 'App\Http\Controllers\Api\StickerController@getCategoryStickers');
// end of stickerData

// start of bgData
Route::post('V3/getBgs', 'App\Http\Controllers\Api\BgController@getBgs');
Route::post('V3/getCategoryBgs', 'App\Http\Controllers\Api\BgController@getCategoryBgs');
// end of bgData

// start of searchApi
Route::post('V3/searchTemplates', 'App\Http\Controllers\Api\SearchController@searchTemplates');
Route::post('V3/searchElements', 'App\Http\Controllers\Api\SearchController@searchElements');

Route::post('V4/searchTemplates', 'App\Http\Controllers\Api\SearchApiController@searchTemplates');
Route::post('V4/searchElements', 'App\Http\Controllers\Api\SearchApiController@searchElements');
// end of searchApi

// start of suscription
Route::post('V4/getCoins', 'App\Http\Controllers\Api\SubscriptionController@getCoins');
Route::post('V3/getSubs', 'App\Http\Controllers\Api\SubscriptionController@getSubs');
Route::post('V3/webhookTranscation', 'App\Http\Controllers\Api\SubscriptionController@webhookTranscation');
// end of suscription

// start of subHistory
Route::post('V3/getCurrentPlan', 'App\Http\Controllers\Api\SubscriptionController@getCurrentPlan');
Route::post('purchases', 'App\Http\Controllers\Api\SubscriptionController@getPurchases');
Route::post('vpurchases', 'App\Http\Controllers\Api\SubscriptionController@getVideoPurchases');
// end of subHistory

// start of subHistory
Route::post('V3/sendCustomNotification', 'App\Http\Controllers\Api\NotificationController@sendCustomNotification');
// end of subHistory

// start of customOrder
Route::post('V4/getOrderSizes', 'App\Http\Controllers\CustomOrder\CustomOrderApiController@getOrderSizes');
Route::post('V4/getBasePrices', 'App\Http\Controllers\CustomOrder\CustomOrderApiController@getBasePrices');
Route::post('V4/listOrder', 'App\Http\Controllers\CustomOrder\CustomOrderApiController@listOrder');
Route::post('V4/createOrder', 'App\Http\Controllers\CustomOrder\CustomOrderApiController@createOrder');
Route::post('V4/updateOrder', 'App\Http\Controllers\CustomOrder\CustomOrderApiController@updateOrder');
Route::post('V4/cancelOrder', 'App\Http\Controllers\CustomOrder\CustomOrderApiController@cancelOrder');
Route::post('V4/deleteImage', 'App\Http\Controllers\CustomOrder\CustomOrderApiController@deleteImage');
Route::post('V4/orderTimeValidate', 'App\Http\Controllers\CustomOrder\CustomOrderApiController@orderTimeValidate');
Route::post('V4/customOrderTranscation', 'App\Http\Controllers\CustomOrder\CustomOrderApiController@customOrderTranscation');
Route::post('V4/customOrderWebhookTranscation', 'App\Http\Controllers\CustomOrder\CustomOrderApiController@customOrderWebhookTranscation');
Route::post('V4/customOrderRefund', 'App\Http\Controllers\CustomOrder\CustomOrderApiController@customOrderRefund');
// end of customOrder

// start of brandKit
Route::post('V4/updateBrandKit', 'App\Http\Controllers\BrandKit\BrandKitApiController@updateBrandKit');
Route::post('V4/getBrandKit', 'App\Http\Controllers\BrandKit\BrandKitApiController@getBrandKit');
Route::post('V4/deleteBrandImage', 'App\Http\Controllers\BrandKit\BrandKitApiController@deleteBrandImage');
// end of brandKit

// start of ReportApi
Route::post('reportBug', 'App\Http\Controllers\Api\TemplateApiController@reportBug');
Route::post('reportAssets', 'App\Http\Controllers\Api\ReportApi@reportAssets');
Route::post('getPromoCode', 'App\Http\Controllers\Api\PromoCodeController@getPromoCode');
// end of ReportApi

//start of draft
Route::get('drafts', [App\Http\Controllers\Api\DraftController::class, 'getDrafts']);
Route::post('drafts', [App\Http\Controllers\Api\DraftController::class, 'getDrafts']);

Route::get('mdraft', [App\Http\Controllers\Api\DraftController::class, 'modifiedDraft']);
Route::post('mdraft', [App\Http\Controllers\Api\DraftController::class, 'modifiedDraft']);

Route::get('data', [App\Http\Controllers\Api\DraftController::class, 'getPosterDetail']);
Route::post('data', [App\Http\Controllers\Api\DraftController::class, 'getPosterDetail']);

Route::get('sdata', [App\Http\Controllers\Api\DraftController::class, 'saveData']);
Route::post('sdata', [App\Http\Controllers\Api\DraftController::class, 'saveData']);
//end of draft

//start of uploads
Route::get('uploads', [App\Http\Controllers\Api\UploadController::class, 'getUploads']);
Route::post('uploads', [App\Http\Controllers\Api\UploadController::class, 'getUploads']);

Route::get('mupload', [App\Http\Controllers\Api\UploadController::class, 'modifiedUpload']);
Route::post('mupload', [App\Http\Controllers\Api\UploadController::class, 'modifiedUpload']);

Route::get('rupload', [App\Http\Controllers\Api\UploadController::class, 'renameUpload']);
Route::post('rupload', [App\Http\Controllers\Api\UploadController::class, 'renameUpload']);

Route::get('upload', [App\Http\Controllers\Api\UploadController::class, 'uploadImgs']);
Route::post('upload', [App\Http\Controllers\Api\UploadController::class, 'uploadImgs']);
//end of uploads

Route::get('upgradeTmp', [App\Http\Controllers\Api\UploadController::class, 'upgradeTmp']);
Route::post('upgradeTmp', [App\Http\Controllers\Api\UploadController::class, 'upgradeTmp']);
Route::post('updateTmp', [App\Http\Controllers\Api\UploadController::class, 'updateTmp']);

//start of payments
Route::get('razorpay', [App\Http\Controllers\Api\PaymentController::class, 'createRazorPayIntent']);
Route::post('razorpay', [App\Http\Controllers\Api\PaymentController::class, 'createRazorPayIntent']);

Route::get('listPm', [App\Http\Controllers\Api\PaymentController::class, 'listMethods']);
Route::post('listPm', [App\Http\Controllers\Api\PaymentController::class, 'listMethods']);

Route::get('updatepm', [App\Http\Controllers\Api\PaymentController::class, 'updatePm']);
Route::post('updatepm', [App\Http\Controllers\Api\PaymentController::class, 'updatePm']);

Route::get('detachpm', [App\Http\Controllers\Api\PaymentController::class, 'detachPm']);
Route::post('detachpm', [App\Http\Controllers\Api\PaymentController::class, 'detachPm']);

Route::get('stripe', [App\Http\Controllers\Api\PaymentController::class, 'createStripeIntent']);
Route::post('stripe', [App\Http\Controllers\Api\PaymentController::class, 'createStripeIntent']);

Route::get('webhook', [App\Http\Controllers\Api\PaymentController::class, 'webhook']);
Route::post('webhook', [App\Http\Controllers\Api\PaymentController::class, 'webhook']);

Route::post('V3/upTranscation', [App\Http\Controllers\Api\PaymentController::class, 'webhook']);

Route::get('verifyPayId', [App\Http\Controllers\Api\PaymentController::class, 'verifyStripeId']);
Route::post('verifyPayId', [App\Http\Controllers\Api\PaymentController::class, 'verifyStripeId']);
//end of payments


Route::get('download', [App\Http\Controllers\Api\DownloadController::class, 'download']);
Route::post('download', [App\Http\Controllers\Api\DownloadController::class, 'download']);
//Route::get('sitemap', [App\Http\Controllers\Api\DownloadController::class, 'sitemap']);
//Route::post('sitemap', [App\Http\Controllers\Api\DownloadController::class, 'sitemap']);
Route::get('catalog', [App\Http\Controllers\Api\DownloadController::class, 'catalog']);
Route::post('catalog', [App\Http\Controllers\Api\DownloadController::class, 'catalog']);

Route::get('keywords', [App\Http\Controllers\Api\DownloadController::class, 'keywords']);
Route::post('keywords', [App\Http\Controllers\Api\DownloadController::class, 'keywords']);

Route::post('reviews', [ReviewController::class, 'getReviews']);

//New Apis



Route::get('categories/{subcategories?}', [CategoryApiController::class, 'show'])->where('subcategories', '(.*)');
Route::post('/getAllNewCategories', [CategoryApiController::class, 'getAllNewCategories']);
Route::post('/getCategories', [CategoryApiController::class, 'getCategories']);
Route::post('/V4/specialTemplates', [CategoryApiController::class, 'searchSpecialTemplates']);
Route::post('plans/all', [CategoryApiController::class, 'getAllPricePlans']);
Route::get('/slugs', [CategoryApiController::class, 'getSlugList']);
Route::get('/template/unActiveLists', [CategoryApiController::class, 'unActiveLists']);


/* Filter API */
Route::get('/style/getList', [StyleAPIController::class, 'getList']);
Route::post('/theme/getListTheme', [ThemeAPIController::class, 'getListTheme']);
Route::get('/language/getListLanguage', [LanguageAPIController::class, 'getListLanguage']);
Route::post('/interest/listInterest', [InterestAPIController::class, 'getListInterest']);
Route::post('/sizes/list', [SizeAPIController::class, 'getSizeList']);
Route::get('/colors/list', [ColorAPIController::class, 'getColors']);
Route::get('/religions/list', [ReligionAPIController::class, 'getR      eligionList']);

/* Review API */

Route::post('/reviews/submit', [ReviewController::class, 'postReview']);
Route::post('/reviews/list', [ReviewController::class, 'getReviews']);
Route::post('/reviews/update', [ReviewController::class, 'editReview']);
Route::post('/reviews/delete', [ReviewController::class, 'deleteReview']);
Route::post('/reviews/allAnalytic', [ReviewController::class, 'allAnalyticReviews']);
Route::post('/reviews/user_review', [ReviewController::class, 'getUserReview']);

Route::post('/getFrames', [FrameApiController::class, 'getFrameData']);
Route::post('/getCatFrames', [FrameApiController::class, 'getCatFrameData']);

// Designer System APIs
use App\Http\Controllers\Api\DesignerApplicationController;
use App\Http\Controllers\Api\DesignerController;
use App\Http\Controllers\Api\DesignerWalletController;
use App\Http\Controllers\Api\DesignerHeadController;
use App\Http\Controllers\Api\SeoHeadController;
use App\Http\Controllers\Api\AdminDesignerController;
use App\Http\Controllers\Api\DesignerEnrollmentController;

// Public APIs - No Auth Required
Route::prefix('designer')->group(function () {
    Route::post('/apply', [DesignerApplicationController::class, 'apply']);
    Route::post('/check-status', [DesignerApplicationController::class, 'checkStatus']);
    Route::post('/enrollment/options', [DesignerEnrollmentController::class, 'getEnrollmentOptions']);

    // Enrollment Metadata APIs (Public - No Auth)
    Route::get('/enrollment/types', [DesignerEnrollmentController::class, 'getTypes']);
    Route::get('/enrollment/categories', [DesignerEnrollmentController::class, 'getCategories']);
    Route::get('/enrollment/goals', [DesignerEnrollmentController::class, 'getGoals']);
});

// Designer APIs - Auth Required
Route::prefix('designer')->middleware('auth:api')->group(function () {
    // Enrollment APIs
    Route::get('/enrollment/check', [DesignerEnrollmentController::class, 'checkEnrollment']);
    Route::post('/enrollment/submit', [DesignerEnrollmentController::class, 'submitEnrollment']);
    Route::post('/enrollment/choose-plan', [DesignerEnrollmentController::class, 'choosePlan']);
    Route::get('/enrollment/status', [DesignerEnrollmentController::class, 'getEnrollmentStatus']);

    // Profile & Design APIs
    Route::get('/profile', [DesignerController::class, 'getProfile']);
    Route::post('/design/submit', [DesignerController::class, 'submitDesign']);
    Route::get('/designs', [DesignerController::class, 'getDesigns']);
    Route::get('/design/{id}', [DesignerController::class, 'getDesignDetails']);

    // Wallet APIs
    Route::get('/wallet', [DesignerWalletController::class, 'getWallet']);
    Route::get('/transactions', [DesignerWalletController::class, 'getTransactions']);
    Route::post('/withdrawal/request', [DesignerWalletController::class, 'requestWithdrawal']);
    Route::get('/withdrawals', [DesignerWalletController::class, 'getWithdrawals']);
});

// Designer Head APIs - Auth Required
Route::prefix('designer-head')->middleware('auth:api')->group(function () {
    Route::get('/applications', [DesignerHeadController::class, 'getApplications']);
    Route::post('/application/{id}/approve', [DesignerHeadController::class, 'approveApplication']);
    Route::post('/application/{id}/reject', [DesignerHeadController::class, 'rejectApplication']);
    Route::get('/design-submissions', [DesignerHeadController::class, 'getDesignSubmissions']);
    Route::post('/design/{id}/approve', [DesignerHeadController::class, 'approveDesign']);
    Route::post('/design/{id}/reject', [DesignerHeadController::class, 'rejectDesign']);
});

// SEO Head APIs - Auth Required
Route::prefix('seo-head')->middleware('auth:api')->group(function () {
    Route::get('/design-submissions', [SeoHeadController::class, 'getDesignSubmissions']);
    Route::post('/design/{id}/approve', [SeoHeadController::class, 'approveDesign']);
    Route::post('/design/{id}/reject', [SeoHeadController::class, 'rejectDesign']);
    Route::post('/design/{id}/update-seo', [SeoHeadController::class, 'updateSeoDetails']);
});

// Admin Designer APIs - Auth Required
Route::prefix('admin/designer')->middleware('auth:api')->group(function () {
    Route::get('/withdrawals', [AdminDesignerController::class, 'getWithdrawals']);
    Route::post('/withdrawal/{id}/process', [AdminDesignerController::class, 'processWithdrawal']);
    Route::post('/withdrawal/{id}/reject', [AdminDesignerController::class, 'rejectWithdrawal']);

    // Designer Categories Management
    Route::get('/categories', [App\Http\Controllers\Api\DesignerCategoryApiController::class, 'index']);
    Route::get('/category/{id}', [App\Http\Controllers\Api\DesignerCategoryApiController::class, 'show']);
    Route::post('/category', [App\Http\Controllers\Api\DesignerCategoryApiController::class, 'store']);
    Route::put('/category/{id}', [App\Http\Controllers\Api\DesignerCategoryApiController::class, 'update']);
    Route::post('/category/{id}/toggle', [App\Http\Controllers\Api\DesignerCategoryApiController::class, 'toggleActive']);
    Route::delete('/category/{id}', [App\Http\Controllers\Api\DesignerCategoryApiController::class, 'destroy']);
});



// Simple Payment API - Common payment link generation
Route::prefix('payment')->group(function () {
    // Create payment link (minimal data required)
    Route::post('create-link', [App\Http\Controllers\Api\SimplePaymentController::class, 'createPaymentLink']);

    // Check payment status
    Route::get('status', [App\Http\Controllers\Api\SimplePaymentController::class, 'checkPaymentStatus']);
    Route::post('status', [App\Http\Controllers\Api\SimplePaymentController::class, 'checkPaymentStatus']);

    // Webhook handlers
    Route::any('razorpay-webhook', [App\Http\Controllers\Api\SimplePaymentController::class, 'razorpayWebhook']);
    Route::any('phonepe-webhook', [App\Http\Controllers\Api\SimplePaymentController::class, 'phonePeWebhook']);
});


Route::post('revenue/login', [RevenueController::class, 'login']);
Route::post('revenue/', [RevenueController::class, 'index']);
Route::post('revenue/logs', [RevenueController::class, 'logs']);
Route::post('revenue/analytics', [RevenueController::class, 'analytics']);
Route::post('revenue/e_mandates', [RevenueController::class, 'e_mandates']);
Route::post('revenue/new_subs', [RevenueController::class, 'new_subs']);
Route::post('revenue/top_users', [RevenueController::class, 'top_users']);