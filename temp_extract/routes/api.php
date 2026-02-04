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
Route::post('getReviews', [PReviewController::class, 'getReviews']);
Route::any('get_portfolio', [UserApiController::class, 'getPortfolio']);


Route::any("send-whatsapp",[WhatsAppController::class,'send']);

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

Route::any('phonepe/payment', [PhonePePaymentApiController::class, 'payment']);
Route::any('phonepe/status/{transaction_id}', [PhonePePaymentApiController::class, 'checkPaymentStatus']);
Route::any('payment/webhook', [PhonePePaymentApiController::class, 'paymentWebhook']);

// JSON file handling APIs
Route::post('/json/save', [CaricatureController::class, 'saveJson']);
Route::post('/json/delete-multiple', [CaricatureController::class, 'deleteMultipleJson']);


Route::any('getOfferPopUp', [PlanController::class, 'getOfferPopUp']);



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

// routes/api.php

