<?php

use App\Http\Controllers\AI\AiCreditTransactionController;
use App\Http\Controllers\AiCreditController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\AppCategoryController;
use App\Http\Controllers\AudioCategoryController;
use App\Http\Controllers\AudioItemController;
use App\Http\Controllers\Automation\AutomationConfigController;
use App\Http\Controllers\Automation\AutomationReportController;
use App\Http\Controllers\Automation\AutomationTestController;
use App\Http\Controllers\Automation\CampaignController;
use App\Http\Controllers\Automation\EmailTemplateController;
use App\Http\Controllers\Automation\WhatsAppTemplateController;
use App\Http\Controllers\BgCatController;
use App\Http\Controllers\BgItemController;
use App\Http\Controllers\BroadcastAuthController;
use App\Http\Controllers\Caricature\AttireController;
use App\Http\Controllers\Caricature\CaricatureCategoryController;
use App\Http\Controllers\Caricature\CaricatureHistoryController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContectUsWebControlller;
use App\Http\Controllers\CustomDataExporter;
use App\Http\Controllers\DensityCheckerController;
use App\Http\Controllers\EditableModesController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Excel\UsersExport;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\FontController;
use App\Http\Controllers\FontFamilyController;
use App\Http\Controllers\FontListController;
use App\Http\Controllers\FrameCategoryController;
use App\Http\Controllers\FrameItemController;
use App\Http\Controllers\GifCategoryControllers;
use App\Http\Controllers\GifItemControllers;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InAppMessageController;
use App\Http\Controllers\InterestController;
use App\Http\Controllers\JsonController;
use App\Http\Controllers\JsonPageController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\Lottie\VideoCatController;
use App\Http\Controllers\Lottie\VideoTemplateController;
use App\Http\Controllers\NewCategoryController;
use App\Http\Controllers\NewSearchTagController;
use App\Http\Controllers\NoIndexController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfferPopUpController;
use App\Http\Controllers\OrderUserController;
use App\Http\Controllers\PageSlugHistoryController;
use App\Http\Controllers\PaymentConfigController;
use App\Http\Controllers\PendingTaskController;
use App\Http\Controllers\PhonePeAutoPayTestController;
use App\Http\Controllers\PlanMetaDetailsController;
use App\Http\Controllers\PReviewController;
use App\Http\Controllers\Pricing\BonusPackageController;
use App\Http\Controllers\Pricing\OfferPackageController;
use App\Http\Controllers\Pricing\PlanCategoryFeatureController;
use App\Http\Controllers\Pricing\PlanDurationController;
use App\Http\Controllers\Pricing\PlanFeatureController;
use App\Http\Controllers\Pricing\PlanUserDiscountController;
use App\Http\Controllers\Pricing\PricePlanController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\RawDatasController;
use App\Http\Controllers\RecentExpireController;
use App\Http\Controllers\RelegionController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\SearchTagController;
use App\Http\Controllers\SeoErrorListController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\SpecialPagesController;
use App\Http\Controllers\StickerCatController;
use App\Http\Controllers\StickerItemController;
use App\Http\Controllers\StyleController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TemplateRateController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManageSubscriptionController;
use App\Http\Controllers\UserManageTemplateProductController;
use App\Http\Controllers\UserManageVideoProductController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\VectorCategoryController;
use App\Http\Controllers\VectorItemController;
use App\Http\Controllers\VideoCategoryControllers;
use App\Http\Controllers\VideoItemControllers;
use App\Http\Controllers\VirtualCategoryController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsAdminOrDesignerManager;
use App\Http\Middleware\IsAdminOrManager;
use App\Http\Middleware\isAdminOrSeoManger;
use App\Http\Middleware\IsSalesAccess;
use App\Http\Middleware\IsSalesManagerAccess;
use App\Http\Middleware\IsSeoAccess;
use App\Models\Template;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

//Route::post('/broadcasting/auth', function (Request $request) {
//    try {
//        return app(BroadcastAuthController::class)->authenticate($request);
//    } catch (\Exception $e) {
//        return response()->json(['error' => 'Authentication failed'], 403);
//    }
//})->withoutMiddleware(['verify.csrf']);

//Route::post('/broadcasting/auth', function (Request $request) {
//    $controller = new App\Http\Controllers\BroadcastAuthController();
//    return $controller->authenticate($request);
//})->withoutMiddleware(['verify.csrf']);
Route::post('/broadcasting/auth', [BroadcastAuthController::class, 'authenticate'])
    ->withoutMiddleware(['verify.csrf']);


Auth::routes(['reset' => true, 'register' => false]);
Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
Route::post('/trigger-event', [EventController::class, 'sendPrivateEvent']);
Route::get('/dashboard/{manager?}', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

Route::get('show_fonts', [FontController::class, 'show'])->name('show_fonts');
Route::get('delete_font/{id}', [FontController::class, 'destroy']);
Route::get('create_font', [FontController::class, 'create'])->name('create_font');
Route::post('submit_font', [FontController::class, 'store']);
Route::get('edit_font/{id}', [FontController::class, 'edit'])->name('edit_font');
Route::post('update_font/{id}', [FontController::class, 'update'])->name('font.update');

Route::get('font_families', [FontFamilyController::class, 'show'])->name('font_families');
Route::post('get_font_family', [FontFamilyController::class, 'get'])->name('get_font_family');
Route::post('submit_font_family', [FontFamilyController::class, 'add'])->name('font_family.create');
Route::post('update_font_family', [FontFamilyController::class, 'update'])->name('font_family.update');
Route::post('delete_font_family', [FontFamilyController::class, 'delete'])->name('font_family.delete');

Route::get('font_list', [FontListController::class, 'show'])->name('font_list');
Route::post('get_fontlist', [FontListController::class, 'get'])->name('get_fontlist');
Route::post('submit_list', [FontListController::class, 'add'])->name('font_list.create');
Route::post('update_list', [FontListController::class, 'update'])->name('font_list.update');
Route::post('delete_list', [FontListController::class, 'delete'])->name('font_list.delete');

Route::get('show_v_cat', [VideoCatController::class, 'show'])->name('show_v_cat');
Route::get('delete_v_cat/{id}', [VideoCatController::class, 'destroy']);
Route::get('create_v_cat', [VideoCatController::class, 'create'])->name('create_v_cat');
Route::post('submit_v_cat', [VideoCatController::class, 'store'])->name('v_cat.store');
Route::get('edit_v_cat/{id}', [VideoCatController::class, 'edit'])->name('edit_v_cat');
Route::post('update_v_cat/{id}', [VideoCatController::class, 'update'])->name('v_cat.update');
Route::post('v_cat_imp/{id}', [VideoCatController::class, 'imp_update'])->name('v_cat.imp');
Route::post('sendVideoCategoryNotification/{id}', 'App\Http\Controllers\Api\NotificationController@sendVideoCategoryNotification')->name('v_cat.notification');

Route::get('show_v_item', [VideoTemplateController::class, 'show'])->name('show_v_item');
Route::post('delete_v_item/{id}', [VideoTemplateController::class, 'destroy'])->name('v_item.delete');
Route::post('create_v_item', [VideoTemplateController::class, 'create'])->name('create_v_item');
Route::post('submit_v_item', [VideoTemplateController::class, 'store']);
Route::get('edit_v_item/{id}', [VideoTemplateController::class, 'edit'])->name('edit_v_item');
Route::post('update_v_item/{id}', [VideoTemplateController::class, 'update'])->name('v_item.update');
Route::get('edit_seo_v_item/{id}', [VideoTemplateController::class, 'editSeo'])->name('edit_seo_v_item');
Route::post('update_seo_v_item/{id}', [VideoTemplateController::class, 'updateSeo'])->name('v_item_seo.update');

Route::get('show_cat', [CategoryController::class, 'show'])->name('show_cat');
Route::get('delete_cat/{id}', [CategoryController::class, 'destroy']);
Route::get('create_cat', [CategoryController::class, 'create'])->name('create_cat');
Route::post('submit_cat', [CategoryController::class, 'store']);
Route::get('edit_cat/{id}', [CategoryController::class, 'edit'])->name('edit_cat');
Route::post('update_cat/{id}', [CategoryController::class, 'update'])->name('cat.update');



Route::get('show_new_cat', [NewCategoryController::class, 'show'])->name('show_new_cat');
Route::get('delete_new_cat/{id}', [NewCategoryController::class, 'destroy']);
Route::get('create_new_cat', [NewCategoryController::class, 'create'])->name('create_new_cat');
Route::post('submit_new_cat', [NewCategoryController::class, 'store']);
Route::get('edit_new_cat/{id}', [NewCategoryController::class, 'edit'])->name('edit_new_cat');
Route::post('update_new_cat/{id}', [NewCategoryController::class, 'update'])->name('new_cat.update');
Route::post('cat_imp/{id}', [NewCategoryController::class, 'imp_update'])->name('cat.imp');
Route::get('{mode}/new_cat/{id}', [NewCategoryController::class, 'preview'])->name('preview_new_cat');

Route::get('show_virtual_cat', [VirtualCategoryController::class, 'index'])->name('show_virtual_cat');
Route::get('create_virtual_cat', [VirtualCategoryController::class, 'create'])->name('create_virtual_cat');
Route::post('submit_virtual_cat', [VirtualCategoryController::class, 'store'])->name('submit_virtual_cat');
Route::get('edit_virtual_cat/{id}', [VirtualCategoryController::class, 'edit'])->name('edit_virtual_cat');
Route::post('update_virtual_cat/{id}', [VirtualCategoryController::class, 'update'])->name('new_virtual_cat.update');
Route::post('virtual', [VirtualCategoryController::class, 'getVirtual'])->name('virtual');

Route::get('show_sub_cat', [SubCategoryController::class, 'show_sub_cat'])->name('show_sub_cat');
Route::post('submit_sub_cat', [SubCategoryController::class, 'addSubCat']);
Route::post('update_sub_cat/{id}', [SubCategoryController::class, 'updateSubCat'])->name('subCat.update');
Route::post('delete_sub_cat/{id}', [SubCategoryController::class, 'deleteSubCat'])->name('subCat.delete');

Route::post('submit_style', [StyleController::class, 'submitStyle']);
Route::get('show_style', [StyleController::class, 'show_style'])->name('show_style');
Route::post('delete_style/{id}', [StyleController::class, 'deleteStyle'])->name('style.delete');

Route::get('show_keyword', [KeywordController::class, 'show'])->name('show_keyword');
Route::get('create_keyword', [KeywordController::class, 'create'])->name('create_keyword');
Route::get('edit_keyword/{id?}', [KeywordController::class, 'edit'])->name('edit_keyword');
Route::post('get_keyword', [KeywordController::class, 'get']);
Route::post('submit_keyword', [KeywordController::class, 'add']);
Route::post('update_keyword/{id}', [KeywordController::class, 'update'])->name('keyword.update');
Route::post('delete_keyword/{id}', [KeywordController::class, 'delete'])->name('keyword.delete');

Route::get('page_slug_history', [PageSlugHistoryController::class, 'show'])->name('page_slug_history')->middleware(isAdminOrSeoManger::class); // v2update
Route::post('create_page_slug', [PageSlugHistoryController::class, 'add'])->name('create_page_slug')->middleware(isAdminOrSeoManger::class); // v2update
Route::post('edit_page_slug/{id}', [PageSlugHistoryController::class, 'update'])->name('edit_page_slug')->middleware(isAdminOrSeoManger::class); // v2update
// theme
Route::get('show_theme', [ThemeController::class, 'show_theme'])->name('show_theme');
Route::post('submit_theme', [ThemeController::class, 'submitTheme']);
Route::post('delete_theme/{id}', [ThemeController::class, 'deleteTheme'])->name('theme.delete');
// releted tag
Route::get('show_search_tag', [SearchTagController::class, 'show_search_tag'])->name('show_search_tag');
Route::post('submit_search_tag', [SearchTagController::class, 'submitSearchTag']);
Route::post('delete_search_tag/{id}', [SearchTagController::class, 'deleteSearchTag'])->name('searchTag.delete');
// interest
Route::get('show_interest', [InterestController::class, 'showInterest'])->name('show_interest');
Route::post('interest_store_or_update', [InterestController::class, 'storeOrUpdate'])->name('interest_store_or_update');
Route::post('delete_interest/{id}', [InterestController::class, 'deleteInterest'])->name('interest.delete');
// languag
Route::get('show_lang', [LangController::class, 'showLang'])->name('show_lang');
Route::post('store_or_update_lang', [LangController::class, 'storeOrUpdateLang'])->name('store_or_update_lang');
Route::post('delete_lang/{id}', [LangController::class, 'deleteLang'])->name('lang.delete');

Route::get('create_editable_mode', [EditableModesController::class, 'create'])->name('create_editable_mode');
Route::post('submit_editable_mode', [EditableModesController::class, 'store']);
Route::post('update_editable_mode/{id}', [EditableModesController::class, 'update'])->name('editable_mode.update');
Route::post('delete_editable_mode/{id}', [EditableModesController::class, 'delete'])->name('editable_mode.delete');

Route::get('show_attire_item', [AttireController::class,'show'])->name('show_attire_item');
Route::post('create_attire', [AttireController::class,'store'])->name('create_attire');
Route::get('add_attire', [AttireController::class,'add'])->name('add_attire');
Route::get('edit_seo_attire/{id}', [AttireController::class, 'edit_seo'])->name('edit_seo_attire');
Route::post('update_seo_attire/{id}', [AttireController::class, 'update_seo'])->name('update_seo_attire');
Route::post('update_caricature_category', [AttireController::class, 'updateCategory'])->name('update_caricature_category');
Route::post('attire.assign.newcategory', [AttireController::class, 'assignNewCategory'])->name('attire.assign.newcategory');
Route::post('attire.assign-seo', [AttireController::class, 'assignSeo'])->name('attire.assign-seo');
Route::post('attire.premium.update', [AttireController::class, 'updateAttirePremium'])->name('attire.premium.update');
Route::post('attire_pinned/{id}', [AttireController::class, 'pinned_update'])->name('attire.pinned');
Route::post('attire.editor.choice', [AttireController::class, 'editorChoiceUpdate'])->name('attire.editor.choice');
Route::post('attire_status/{id}', [AttireController::class, 'status_update'])->name('attire.status');
Route::post('delete_attire/{id}', [AttireController::class, 'destroy'])->name('attire.delete');
Route::get('/edit_attire/{id}', [AttireController::class, 'edit'])->name('attire.edit');
Route::post('/update_attire/{id}', [AttireController::class, 'update'])->name('attire.update');

Route::get('show_cari_cat', [CaricatureCategoryController::class, 'show'])->name('show_cari_cat');
Route::get('delete_cari_cat/{id}', [CaricatureCategoryController::class, 'destroy']);
Route::get('create_cari_cat', [CaricatureCategoryController::class, 'create'])->name('create_cari_cat');
Route::post('submit_cari_cat', [CaricatureCategoryController::class, 'store']);
Route::get('edit_cari_cat/{id}', [CaricatureCategoryController::class, 'edit'])->name('edit_cari_cat');
Route::post('update_cari_cat/{id}', [CaricatureCategoryController::class, 'update'])->name('update_cari_cat');

Route::get('caricature_history/payment/{payment_id}', [CaricatureHistoryController::class, 'showByPaymentId'])
    ->name('caricature_history.showByPaymentId')
    ->middleware(IsAdmin::class);
Route::resource('caricature_history', CaricatureHistoryController::class)->middleware(IsAdmin::class);

Route::get('show_item/{isInActive?}', [TemplateController::class, 'show'])->name('show_item');
Route::post('delete_item/{id}', [TemplateController::class, 'destroy'])->name('item.delete');
Route::post('create_item', [TemplateController::class, 'create'])->name('create_item');
Route::post('submit_item', [TemplateController::class, 'store']);
Route::post('update_item/{id}', [TemplateController::class, 'update'])->name('item.update');
Route::post('update_seo_item/{id}', [TemplateController::class, 'update_seo'])->name('item.update_seo');
Route::post('update_temp_category', [TemplateController::class, 'updateTempCategory'])->name('update.temp_category');
Route::get('edit_item/{id}', [TemplateController::class, 'edit'])->name('edit_item');
Route::get('edit_seo_item/{id}', [TemplateController::class, 'edit_seo'])->name('edit_seo_item');
Route::post('get_custom_item_data/getCustomData', [TemplateController::class, 'getCustomData'])->name('item.custom_data');
Route::post('reset_date/{id}', [TemplateController::class, 'reset_date'])->name('reset.date');
Route::post('reset_creation/{id}', [TemplateController::class, 'reset_creation'])->name('reset.creation');
Route::post('temp_status/{id}', [TemplateController::class, 'status_update'])->name('temp.status');
Route::post('temp_pinned/{id}', [TemplateController::class, 'pinned_update'])->name('temp.pinned');
Route::post('temp.assign-seo', [TemplateController::class, 'assignSeo'])->name('temp.assign-seo');
Route::post('/design/assign-newcategory', [TemplateController::class, 'assignNewCategory'])->name('design.assign.newcategory');
Route::post('temp.editor.choice', [TemplateController::class, 'editorChoiceUpdate'])->name('temp.editor.choice');
Route::post('temp.premium.update', [TemplateController::class, 'updateTemplatePremium'])->name('temp.premium.update');

Route::resource('raw_datas', RawDatasController::class);
Route::get('edit_rawdata/{id}', [RawDatasController::class, 'edit'])->name('edit_rawdata');

Route::resource('show_sticker_cat', StickerCatController::class);
Route::resource('sticker_item', StickerItemController::class);

Route::post('stk_status/{id}', [StickerItemController::class, 'status_update'])->name('stk.status');
Route::post('stk_premium/{id}', [StickerItemController::class, 'premium_update'])->name('stk.premium');

Route::resource('vector_categories', VectorCategoryController::class);
Route::resource('vector_items', VectorItemController::class);
Route::post('updateVectorItem', [VectorItemController::class, 'updateVectorItemPremium'])->name('vectorItem.premium');

Route::resource('audio_cat', AudioCategoryController::class);
Route::resource('audio_items', AudioItemController::class);
Route::post('updateAudioItem', [AudioItemController::class, 'updateAudioItemPremium'])->name('audioItem.premium');

Route::resource('show_bg_cat', BgCatController::class);
Route::resource('show_bg_item', BgItemController::class);
Route::post('updatebackgroundItem', [BgItemController::class, 'updatebackgroundItemPremium'])->name('backgroundItem.premium');

Route::resource('video_cat', VideoCategoryControllers::class);
Route::resource('video_item', VideoItemControllers::class);
Route::post('updatevideoItem', [VideoItemControllers::class, 'updatevideoItemPremium'])->name('videoItem.premium');

Route::resource('gif_categories', GifCategoryControllers::class);
Route::resource('gif_items', GifItemControllers::class);
Route::post('updategifItem', [GifItemControllers::class, 'updategifItemPremium'])->name('gifItem.premium');

Route::resource('special_page', SpecialPagesController::class);
Route::get('create_pages', [SpecialPagesController::class, 'create'])->name('create_pages');
Route::get('edit_pages/{id}', [SpecialPagesController::class, 'create'])->name('edit_pages');
Route::post('submit_pages', [SpecialPagesController::class, 'addUpdatePage']);
Route::post('/page/add-update-pages', [SpecialPagesController::class, 'addUpdatePage'])->name('add.update.form');



Route::get('import_json', [JsonController::class, 'create'])->name('import_json');
Route::post('submit_json', [JsonController::class, 'store']);
Route::post('import_page', [JsonPageController::class, 'import_page'])->name('import_page');

Route::post('sendPosterNotification/{id}', 'App\Http\Controllers\Api\NotificationController@sendPosterNotification')->name('poster.notification');
Route::post('sendCategoryNotification/{id}', 'App\Http\Controllers\Api\NotificationController@sendCategoryNotification')->name('cat.notification');
Route::post('sendCustomNotification', 'App\Http\Controllers\Api\NotificationController@sendCustomNotification')->name('custom.notification');

Route::post('/check-density-by-slug', [DensityCheckerController::class, 'checkFromSlug'])->name('density.check.slug');
Route::post('/density-checker/primary-check', [DensityCheckerController::class, 'checkPrimaryKeyword'])->name('density-checker.primary-check');

Route::post('check_n_i', [NoIndexController::class, 'checkNoindex'])->name('check_n_i')->middleware(isAdminOrSeoManger::class); // v2update

Route::get('show_messages', [InAppMessageController::class, 'show'])->name('show_messages')->middleware(IsSalesManagerAccess::class);
Route::post('submit_message', [InAppMessageController::class, 'add'])->middleware(IsSalesManagerAccess::class);
Route::post('update_message/{id}', [InAppMessageController::class, 'update'])->name('message.update')->middleware(IsSalesManagerAccess::class);
Route::post('delete_message/{id}', [InAppMessageController::class, 'delete'])->name('message.delete')->middleware(IsSalesManagerAccess::class);

Route::get('show_app', [AppCategoryController::class, 'show'])->name('show_app')->middleware(IsAdmin::class);
Route::get('delete_app/{id}', [AppCategoryController::class, 'destroy'])->middleware(IsAdmin::class);
Route::get('create_app', [AppCategoryController::class, 'create'])->middleware(IsAdmin::class);
Route::post('submit_app', [AppCategoryController::class, 'store'])->middleware(IsAdmin::class);
Route::get('edit_app/{id}', [AppCategoryController::class, 'edit'])->middleware(IsAdmin::class);
Route::post('update_app/{id}', [AppCategoryController::class, 'update'])->name('app.update')->middleware(IsAdmin::class);

Route::get('show_employee', [EmployeeController::class, 'show'])->name('show_employee')->middleware(IsAdminOrDesignerManager::class);
Route::post('create_employee', [EmployeeController::class, 'create'])->middleware(IsAdminOrManager::class);
Route::post('update_employee/{id}', [EmployeeController::class, 'update'])->name('employee.update')->middleware(IsAdminOrManager::class);
Route::post('reset_employee/{id}', [EmployeeController::class, 'resetPassword'])->name('employee.reset')->middleware(IsAdminOrManager::class);
Route::post('delete_employee/{id}', [EmployeeController::class, 'destroy'])->name('employee.delete')->middleware(IsAdminOrManager::class);
Route::get('employee/routes/{userId}', [EmployeeController::class, 'getRouteSelection'])->name('employee.routes');
Route::get('employee/role/routes/{roleId}', [EmployeeController::class, 'getRouteSelectionByRole'])->name('employee.role.routes');

Route::get('show_users', [UserController::class, 'show'])->name('show_users')->middleware(IsAdmin::class);
Route::get('user_detail/{id}', [UserController::class, 'user_detail'])->middleware(IsAdmin::class);
Route::get('/user/personal-details/{uid}', [UserController::class, 'showPersonalDetails'])->name('user.personal_details')->middleware(IsAdmin::class);
Route::post('/user/personal-details/{uid}', [UserController::class, 'updatePersonalDetails'])->name('user.update_personal_details')->middleware(IsAdmin::class);


Route::get('show_packages', [SubscriptionController::class, 'show_package'])->name('show_packages')->middleware(IsAdmin::class);
Route::post('submit_package', [SubscriptionController::class, 'addPackage'])->middleware(IsAdmin::class);
Route::post('update_package/{id}', [SubscriptionController::class, 'updatePackage'])->name('package.update')->middleware(IsAdmin::class);
Route::post('delete_package/{id}', [SubscriptionController::class, 'deletePackage'])->name('delete.update')->middleware(IsAdmin::class);
Route::get('payment_setting', [SubscriptionController::class, 'showPaymentSetting'])->name('payment_setting')->middleware(IsAdmin::class);
Route::post('update_payment/{id}', [SubscriptionController::class, 'updatePaymentSetting'])->name('payment.update')->middleware(IsAdmin::class);
Route::get('transcation_logs', [SubscriptionController::class, 'showTranscation'])->name('transcation_logs')->middleware(IsAdmin::class);
Route::get('purchases', [SubscriptionController::class, 'showPurchases'])->name('purchases')->middleware(IsAdmin::class);

Route::get('credit_transaction_logs', [AiCreditTransactionController::class, 'index'])->name('credit_transaction_logs')->middleware(IsAdmin::class);

Route::any('transactions/refund', [SubscriptionController::class, 'processRefund'])->name('transactions.refund')->middleware(IsAdmin::class);

Route::post('customTranscation', 'App\Http\Controllers\Api\SubscriptionController@customTranscation')->name('custom.transcation')->middleware(IsAdmin::class);
Route::get('show_orders', 'App\Http\Controllers\CustomOrder\CustomOrderController@show')->name('show_orders')->middleware(IsAdmin::class);

Route::resource('automation_report', AutomationReportController::class)->middleware(IsAdmin::class);
Route::get('/automation_report/failed-logs/{log_id}', [AutomationReportController::class, 'failedLogs'])->name('automation_report.failed_logs');
Route::resource('automation_config', AutomationConfigController::class)->middleware(IsAdmin::class);

// Automation Testing Routes
Route::get('/automation/test', [AutomationTestController::class, 'index'])->name('automation.test.index')->middleware(IsAdmin::class);
Route::post('/automation/test/email', [AutomationTestController::class, 'testEmail'])->name('automation.test.email')->middleware(IsAdmin::class);
Route::post('/automation/test/whatsapp', [AutomationTestController::class, 'testWhatsApp'])->name('automation.test.whatsapp')->middleware(IsAdmin::class);
Route::post('/automation/test/both', [AutomationTestController::class, 'testBoth'])->name('automation.test.both')->middleware(IsAdmin::class);
Route::get('/automation/test/template-preview', [AutomationTestController::class, 'getTemplatePreview'])->name('automation.test.template-preview')->middleware(IsAdmin::class);

// PhonePe AutoPay Testing Routes
Route::get('/phonepe/autopay/test', [PhonePeAutoPayTestController::class, 'index'])->name('phonepe.autopay.test.index')->middleware(IsAdmin::class);
Route::post('/phonepe/autopay/test/create', [PhonePeAutoPayTestController::class, 'createTestSubscription'])->name('phonepe.autopay.test.create')->middleware(IsAdmin::class);
Route::get('/phonepe/autopay/test/status', [PhonePeAutoPayTestController::class, 'checkSubscriptionStatus'])->name('phonepe.autopay.test.status')->middleware(IsAdmin::class);
Route::post('/phonepe/autopay/test/predebit', [PhonePeAutoPayTestController::class, 'triggerPreDebitNotification'])->name('phonepe.autopay.test.predebit')->middleware(IsAdmin::class);
Route::post('/phonepe/autopay/test/debit', [PhonePeAutoPayTestController::class, 'triggerAutoDebit'])->name('phonepe.autopay.test.debit')->middleware(IsAdmin::class);
Route::get('/phonepe/autopay/test/list', [PhonePeAutoPayTestController::class, 'getAllSubscriptions'])->name('phonepe.autopay.test.list')->middleware(IsAdmin::class);
Route::post('/phonepe/autopay/test/delete', [PhonePeAutoPayTestController::class, 'deleteTestSubscription'])->name('phonepe.autopay.test.delete')->middleware(IsAdmin::class);

// PhonePe AutoPay Callback & Webhook Routes (without middleware for PhonePe to access)
Route::any('/phonepe/autopay/callback', [PhonePeAutoPayTestController::class, 'handleCallback'])->name('phonepe.autopay.callback')->withoutMiddleware([\App\Http\Middleware\Authenticate::class]);
Route::any('/phonepe/autopay/webhook', [PhonePeAutoPayTestController::class, 'handleWebhook'])->name('phonepe.autopay.webhook')->withoutMiddleware([\App\Http\Middleware\Authenticate::class]);

// PhonePe Simple Payment Test (sends immediate payment request to UPI)
Route::get('/phonepe/simple-payment-test', function() {
    return view('phonepe_simple_payment_test');
})->name('phonepe.simple_payment_test')->middleware(IsAdmin::class);

Route::post('/phonepe/send-payment-request', [App\Http\Controllers\PhonePeSimplePaymentTestController::class, 'sendPaymentRequest'])->name('phonepe.send_payment_request')->middleware(IsAdmin::class);
Route::post('/phonepe/check-subscription-status', [App\Http\Controllers\PhonePeSimplePaymentTestController::class, 'checkSubscriptionStatus'])->name('phonepe.check_subscription_status')->middleware(IsAdmin::class);
Route::post('/phonepe/send-predebit', [App\Http\Controllers\PhonePeSimplePaymentTestController::class, 'sendPreDebitNotification'])->name('phonepe.send_predebit')->middleware(IsAdmin::class);
Route::post('/phonepe/trigger-autodebit', [App\Http\Controllers\PhonePeSimplePaymentTestController::class, 'triggerAutoDebit'])->name('phonepe.trigger_autodebit')->middleware(IsAdmin::class);
Route::post('/phonepe/simulate-autodebit', [App\Http\Controllers\PhonePeSimplePaymentTestController::class, 'simulateAutoDebit'])->name('phonepe.simulate_autodebit')->middleware(IsAdmin::class);
Route::get('/phonepe/get-history', [App\Http\Controllers\PhonePeSimplePaymentTestController::class, 'getHistory'])->name('phonepe.get_history')->middleware(IsAdmin::class);

// PhonePe Dashboard & Management
Route::get('/phonepe/dashboard', [App\Http\Controllers\PhonePeDashboardController::class, 'index'])->name('phonepe.dashboard')->middleware(IsAdmin::class);

Route::get('/phonepe/transactions', [App\Http\Controllers\PhonePeTransactionController::class, 'index'])->name('phonepe.transactions.index')->middleware(IsAdmin::class);
Route::get('/phonepe/transactions/{id}', [App\Http\Controllers\PhonePeTransactionController::class, 'show'])->name('phonepe.transactions.show')->middleware(IsAdmin::class);
Route::get('/phonepe/transactions/{id}/notifications', [App\Http\Controllers\PhonePeTransactionController::class, 'notifications'])->name('phonepe.transactions.notifications')->middleware(IsAdmin::class);
Route::post('/phonepe/transactions/{id}/check-status', [App\Http\Controllers\PhonePeTransactionController::class, 'checkStatus'])->name('phonepe.transactions.check_status')->middleware(IsAdmin::class);

Route::get('/phonepe/notifications', [App\Http\Controllers\PhonePeNotificationController::class, 'index'])->name('phonepe.notifications.index')->middleware(IsAdmin::class);
Route::get('/phonepe/notifications/{id}', [App\Http\Controllers\PhonePeNotificationController::class, 'show'])->name('phonepe.notifications.show')->middleware(IsAdmin::class);

// PhonePe Webhook (public endpoint for PhonePe to call)
Route::any('/api/phonepe/webhook', [App\Http\Controllers\PhonePeWebhookController::class, 'handleWebhook'])->name('phonepe.webhook');

Route::get('notification_setting', [NotificationController::class, 'showNotificationSetting'])->name('notification_setting')->middleware(IsAdmin::class);
Route::post('update_notification/{id}', [NotificationController::class, 'updateNotificationSetting'])->name('notification.update')->middleware(IsAdmin::class);
Route::post('update_ip', [NotificationController::class, 'updateIpSetting'])->name('ip.update')->middleware(IsAdmin::class);

Route::post('update_cache_ver', [HomeController::class, 'update_cache_ver'])->middleware(IsAdmin::class);

Route::get('show_feedbacks', [FeedbackController::class, 'showFeedbacks'])->name('show_feedbacks')->middleware(IsSalesManagerAccess::class);
Route::get('show_contacts', [FeedbackController::class, 'showContacts'])->name('show_contacts')->middleware(IsSalesManagerAccess::class);
Route::post('user/getChatData', [FeedbackController::class, 'getChatData'])->name('user.getChatData')->middleware(IsSalesManagerAccess::class);
Route::post('send_reply', [FeedbackController::class, 'send_reply'])->middleware(IsSalesManagerAccess::class);
Route::get('getFeedback/{id}', [FeedbackController::class, 'getFeedback'])->middleware(IsSalesManagerAccess::class);
Route::get('getContact/{id}', [FeedbackController::class, 'getContact'])->middleware(IsSalesManagerAccess::class);

Route::get('/contact_us_web', [ContectUsWebControlller::class, 'index'])->name('contact_us_web')->middleware(IsSalesManagerAccess::class);

Route::resource('promocode', PromoCodeController::class)->middleware(IsAdmin::class);
Route::get('/get_users_by_email', [PromoCodeController::class, 'getUsersByEmail'])->name('get_users_by_email')->middleware(IsAdmin::class);
Route::get('/get_users_by_ids', [PromoCodeController::class, 'getUsersByIds'])->name('get_users_by_ids')->middleware(IsAdmin::class);

Route::get('show_pending_task', [PendingTaskController::class, 'show'])->name('show_pending_task');
Route::post('/pending-task/approve', [PendingTaskController::class, 'approve'])->name('pending-task.approve');
Route::post('/pending-task/reject', [PendingTaskController::class, 'reject'])->name('pending-task.reject');
Route::get('/pending-task/preview/{id}', [PendingTaskController::class, 'preview'])->name('pending-task.preview');
Route::get('rejecte_task', [PendingTaskController::class, 'rejecteTask'])->name('rejecte_task');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::get('/uploadedFiles/thumb_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'thumb_file']);
Route::get('/uploadedFiles/fab_jsons/{file}', [App\Http\Controllers\Api\DownloadController::class, 'designs']);
Route::get('/campaigns/whatsapp/{file}', [App\Http\Controllers\Api\DownloadController::class, 'campaignImg']);
// Route::get('/uploadedFiles/v/{file}', [App\Http\Controllers\Api\DownloadController::class, 'v']);
// Route::get('/uploadedFiles/video_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'video_file']);
// Route::get('/uploadedFiles/vCatThumb/{file}', [App\Http\Controllers\Api\DownloadController::class, 'vCatThumb']);
// Route::get('/uploadedFiles/vThumb_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'vThumb_file']);
// Route::get('/uploadedFiles/vZip_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'vZip_file']);
// Route::get('/uploadedFiles/bg_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'bg_file']);
// Route::get('/uploadedFiles/sticker_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'sticker_file']);
// Route::get('/uploadedFiles/catThumb/{file}', [App\Http\Controllers\Api\DownloadController::class, 'catThumb']);
// Route::get('/uploadedFiles/parse_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'parse_file']);
// Route::get('/uploadedFiles/sticker_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'sticker_file']);
// Route::get('/uploadedFiles/font_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'font_file']);
// Route::get('/uploadedFiles/font_thumb/{file}', [App\Http\Controllers\Api\DownloadController::class, 'font_thumb']);
// Route::get('/uploadedFiles/user_dp/{file}', [App\Http\Controllers\Api\DownloadController::class, 'user_dp']);
// Route::get('/uploadedFiles/message_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'message_file']);
// Route::get('/uploadedFiles/contact_ss/{file}', [App\Http\Controllers\Api\DownloadController::class, 'contact_ss']);
// Route::get('/uploadedFiles/notifi_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'notifi_file']);
// Route::get('/uploadedFiles/customOrder/{folder}/{file}', [App\Http\Controllers\Api\DownloadController::class, 'customOrder']);
// Route::get('/uploadedFiles/brandKit/{file}', [App\Http\Controllers\Api\DownloadController::class, 'brandKit']);
// Route::get('/uploadedFiles/zip_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'zip_file']);
// Route::get('/uploadedFiles/d_zip_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'd_zip_file']);
// Route::get('/uploadedFiles/crafty_assets/{file}', [App\Http\Controllers\Api\DownloadController::class, 'crafty_assets']);
// Route::get('/uploadedFiles/frame_thumb/{file}', [App\Http\Controllers\Api\DownloadController::class, 'frame_thumb']);
// Route::get('/uploadedFiles/frame_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'frame_file']);
// Route::get('/uploadedFiles/designs/{file}', [App\Http\Controllers\Api\DownloadController::class, 'designs']);
// Route::get('/uploadedFiles/fab_designs/{file}', [App\Http\Controllers\Api\DownloadController::class, 'fab_designs']);
// Route::get('/uploadedFiles/draftTb/{file}', [App\Http\Controllers\Api\DownloadController::class, 'draftTb']);
// Route::get('/uploadedFiles/drafts/{file}', [App\Http\Controllers\Api\DownloadController::class, 'drafts']);
// Route::get('/uploadedFiles/{file}', [App\Http\Controllers\Api\DownloadController::class, 'uploadedFiles']);
// Route::get('/uploadedFiles', [App\Http\Controllers\Api\DownloadController::class, 'uploadedFiles']);
// Route::get('/uploadedFiles/cta_images/{file}', [App\Http\Controllers\Api\DownloadController::class, 'cta_images']);

Route::get('/caricature/category/{folder}/{type}/{file}', [DownloadController::class, 'serveCaricatureFile']);
Route::get('/caricature/category/{folder}/{file}', [DownloadController::class, 'getCaricatureCategoryFile']);
Route::get('/caricature/attire/{folder}/{type}/{file}', [DownloadController::class, 'serveAttireFile']);
Route::get('/caricature/attire/{folder}/{file}', [DownloadController::class, 'getAttireCategoryFile']);

Route::resource('templateRate', TemplateRateController::class)->middleware(IsAdmin::class);
Route::resource('caricatureRate', TemplateRateController::class)->middleware(IsAdmin::class);
Route::post('downloadImages', [App\Http\Controllers\Api\DownloadController::class, 'downloadImages'])->middleware(IsAdmin::class);

Route::resource('sizes', SizeController::class);
// Route::resource('colors', ColorController::class)->middleware(IsAdmin::class);
Route::resource('religions', RelegionController::class);
Route::post('religions/submit', [RelegionController::class, 'submit'])->name('religions.submit');

Route::resource('ai_credits', AiCreditController::class)->middleware(IsAdmin::class);
Route::post('ai_credits/submit', [AiCreditController::class, 'submit'])->name('ai_credits.submit')->middleware(IsAdmin::class);

Route::middleware(IsAdmin::class)->prefix('payment_configuration')->group(function () {
    Route::get('/', [PaymentConfigController::class, 'index'])->name('payment_configuration.index');
    Route::post('/store', [PaymentConfigController::class, 'store'])->name('payment.config.store');
    Route::post('/add-gateway', [PaymentConfigController::class, 'addNewGateway'])->name('payment.config.add-gateway');
    Route::get('/{id}/get', [PaymentConfigController::class, 'getGateway'])->name('payment.config.get');
    Route::post('/{id}/update', [PaymentConfigController::class, 'updateGateway'])->name('payment.config.update');
    Route::post('/{id}/activate', [PaymentConfigController::class, 'activate'])->name('payment.config.activate');
    Route::delete('/{id}', [PaymentConfigController::class, 'destroy'])->name('payment.config.destroy');
});


// plan
Route::resource('planduration', PlanDurationController::class)->middleware(IsAdmin::class);
Route::resource('plans', PricePlanController::class)->middleware(IsAdmin::class);
//Route::any('create', [PricePlanController::class,'create'])->name('plan.create')->middleware(IsAdmin::class);
Route::resource('categoryFeatures', PlanCategoryFeatureController::class)->middleware(IsAdmin::class);
Route::resource('features', PlanFeatureController::class)->middleware(IsAdmin::class);

Route::prefix('plan/plan-discount')->group(function () {
    Route::get('/', [PlanUserDiscountController::class, 'index'])->name('plan_discount.index');
    Route::post('/store', [PlanUserDiscountController::class, 'store'])->name('plan_discount.store');
    Route::get('/edit/{id}', [PlanUserDiscountController::class, 'edit'])->name('plan_discount.edit');
    Route::delete('/delete/{id}', [PlanUserDiscountController::class, 'destroy'])->name('plan_discount.delete');
});

// new bounce 
Route::resource('bonus-package', BonusPackageController::class)->middleware(IsAdmin::class);
Route::resource('offer-package', OfferPackageController::class)->middleware(IsAdmin::class);
Route::get('/get-durations/{plan_id}', [OfferPackageController::class, 'getDurations'])->name('offer-package.getDurations');

Route::resource('planMetaFeatures', PlanMetaDetailsController::class)->middleware(IsAdmin::class);
Route::resource('frame_categories', FrameCategoryController::class);
Route::resource('frame_items', FrameItemController::class);
Route::post('updateFrameItem', [FrameItemController::class, 'updateFrameItemPremium'])->name('frameItem.premium');

// new_search_tags
Route::resource('new_search_tags', NewSearchTagController::class);
Route::post('new_search_tags/submit', [NewSearchTagController::class, 'storeOrUpdate'])->name('new_search_tags.submit');

/* User Subscription Mannual Setting  */
Route::get('show_manage_subscription/{userId?}', [UserManageSubscriptionController::class, 'showManageSubscription'])->name('manage_subscription.show')->middleware(IsAdmin::class);
Route::post('show_manage_subscription_submit', [UserManageSubscriptionController::class, 'saveManageSubscription'])->name('manage_subscription.submit')->middleware(IsAdmin::class);
Route::post('show_manage_subscription_update', [UserManageSubscriptionController::class, 'updateManageSubscription'])->name('manage_subscription.update')->middleware(IsAdmin::class);
Route::post('manage_subscription_delete/{sub_package_id?}', [UserManageSubscriptionController::class, 'deleteManageSubscription'])->name('manage_subscription.delete')->middleware(IsAdmin::class);

Route::get('manage_template_product/{userId?}', [UserManageTemplateProductController::class, 'manageTemplateProductShow'])->name('manage_template_product.show')->middleware(IsAdmin::class);
Route::post('manage_template_product_submit', [UserManageTemplateProductController::class, 'saveTemplateProduct'])->name('manage_template_product.submit')->middleware(IsAdmin::class);
Route::post('manage_template_product_update', [UserManageTemplateProductController::class, 'updateTemplateProduct'])->name('manage_template_product.update')->middleware(IsAdmin::class);
Route::post('manage_template_product_delete/{template_product_id?}', [UserManageTemplateProductController::class, 'deleteTemplateProduct'])->name('manage_template_product.delete')->middleware(IsAdmin::class);

Route::get('manage_video_product/{userId?}', [UserManageVideoProductController::class, 'manageVideoProductShow'])->name('manage_video_product.show')->middleware(IsAdmin::class);
Route::post('manage_video_product_submit', [UserManageVideoProductController::class, 'saveVideoProduct'])->name('manage_video_product.submit')->middleware(IsAdmin::class);
Route::post('manage_video_product_update', [UserManageVideoProductController::class, 'updateVideoProduct'])->name('manage_video_product.update')->middleware(IsAdmin::class);
Route::post('manage_video_product_delete/{template_product_id?}', [UserManageVideoProductController::class, 'deleteVideoProduct'])->name('manage_video_product.delete')->middleware(IsAdmin::class);

Route::get('/users_export', [UserController::class, 'export'])->name('users.export')->middleware(IsAdmin::class);
Route::get('/active_subscription_export', [UserController::class, 'exportActiveSubscribers'])->name('active_subscription.export')->middleware(IsAdmin::class);
Route::get('/expired_subscription_export', [UserController::class, 'exportExpiredSubscribers'])->name('expired_subscription.export')->middleware(IsAdmin::class);

Route::get('template_transcation_logs', [SubscriptionController::class, 'showTemplateTranscation'])->name('template_transcation_logs')->middleware(IsAdmin::class);
Route::get('video_transcation_logs', [SubscriptionController::class, 'showVideoTranscation'])->name('video_transcation_logs')->middleware(IsAdmin::class);

Route::resource('reviews', ReviewsController::class);
Route::post('review_status', [ReviewsController::class, 'reviewStatus'])->name('review.status');
Route::resource('p_reviews', PReviewController::class);
Route::post('/p-reviews/status', [PReviewController::class, 'reviewStatus'])->name('p_reviews.reviewStatus');
Route::get('/p-reviews/page-data', [PReviewController::class, 'getSelectedPageData'])->name('get_selected_page_data');
Route::get('/p-review/page-title', [PReviewController::class, 'getSelectedPageTitle'])->name('get_selected_page_title');

// Route::get('/export-users', [UsersExport::class, 'sheets'])->middleware(IsAdmin::class);
Route::any('export-datas', [CustomDataExporter::class, 'getDatas'])->middleware(IsAdmin::class);
Route::any('export-sub-datas', [CustomDataExporter::class, 'getSubDatas'])->middleware(IsAdmin::class);

Route::get('/export-users', function () {
    return Excel::download(new UsersExport, 'users.xlsx');
})->middleware(IsAdmin::class);

Route::post('getNewSearchTag', [TemplateController::class, "getNewSearchTag"])->name('getNewSearchTag');
Route::get('editIntrest/{id}', [InterestController::class, 'editIntrest'])->name('interest.edit');
Route::get('themeEdit/{id}', [ThemeController::class, 'themeEdit'])->name('theme.edit');
Route::post('getSizeList', [SizeController::class, 'getSizeList'])->name('getSizeList');
Route::post('getThemeList', [ThemeController::class, 'getThemeList'])->name('getThemeList');
Route::post('getInterestList', [InterestController::class, 'getInterestList'])->name('getInterestList');



//Route::get('email-report', [EmailTemplateController::class, 'report'])->name('email_report.view');
//Route::get('/email-template/{view}', [EmailTemplateController::class, 'viewTemplate'])->name('email_template.view')->middleware('auth');
//Route::get('/email-report/failed-logs/{log_id}', [EmailTemplateController::class, 'failedLogs'])->name('email_report.failed_logs');
//Route::post('email-template/resend-failed-all/{log_id}', [EmailTemplateController::class, 'triggerResendJob'])
//    ->name('email_template.resend_failed_all');
//Route::post('/email-template/resend-single', [EmailTemplateController::class, 'resendSingleFailed'])->name('email_template.resend_single');
//Route::post('/email-report/retry/{id}', [EmailTemplateController::class, 'retry'])->name('email_report.retry');


//Route::post('/email-report/{id}/stop', [EmailTemplateController::class, 'stop'])->name('email_report.stop');
//Route::post('/email-report/{id}/pause', [EmailTemplateController::class, 'pause'])->name('email_report.pause'); // ✅ Added
//Route::post('/email-report/{id}/resume', [EmailTemplateController::class, 'resume'])->name('email_report.resume');
//Route::post('/email-report/{id}/toggle-auto-resume', [EmailTemplateController::class, 'toggleAutoResume'])
//    ->name('email_report.toggle_auto_resume');



Route::middleware(IsAdmin::class)->group(function () {
//    Route::get('whatsapp-report', [WhatsAppTemplateController::class, 'report'])->name('whatsapp_report.view');
//    Route::get('whatsapp-campaign', [WhatsAppTemplateController::class, 'templateIndex'])->name('whatsapp_campaign.view');
//    Route::post('whatsapp-campaign-start', [WhatsAppTemplateController::class, 'startCampaign'])->name('start_wp_campaign');
//    Route::get('/whatsapp-report/failed-logs/{log_id}', [WhatsAppTemplateController::class, 'failedLogs'])->name('whatsapp_report.failed_logs');
//    Route::post('/whatsapp-campaign/{id}/stop', [whatsappTemplateController::class, 'stop'])->name('whatsapp_campaign.stop');
//    Route::post('/whatsapp-campaign/{id}/pause', [whatsappTemplateController::class, 'pause'])->name('whatsapp_campaign.pause');
//    Route::post('/whatsapp-campaign/{id}/resume', [whatsappTemplateController::class, 'resume'])->name('whatsapp_campaign.resume');
//    Route::post('/whatsapp-campaign/{id}/toggle-auto-resume', [whatsappTemplateController::class, 'toggleAutoResume'])
//        ->name('whatsapp_template.toggle_auto_resume');
});

//Route::middleware(IsAdmin::class)->group(function (){
//    Route::get('campaign',[CampaignController::class,'index'])->name('campaign.index');
//    Route::post('combined-campaign-start', [CampaignController::class, 'startCampaign'])->name('start_combined_campaign');
//    Route::post('/campaign/{id}/stop', [CampaignController::class, 'stop'])->name('campaign.stop');
//    Route::post('/campaign/{id}/pause', [CampaignController::class, 'pause'])->name('campaign.pause');
//    Route::post('/campaign/{id}/resume', [CampaignController::class, 'resume'])->name('campaign.resume');
//    Route::post('/whatsapp-campaign/{id}/toggle-auto-resume', [CampaignController::class, 'toggleAutoResume'])
//        ->name('whatsapp_template.toggle_auto_resume');
//});

Route::middleware(IsAdmin::class)->group(function (){

    Route::get('get_email_tmp', [EmailTemplateController::class, 'getEmailTmp'])->name('get_email_tmp')->middleware(IsAdmin::class);
    Route::resource('email_template', EmailTemplateController::class)->middleware(IsAdmin::class);
    Route::post('/email-template/store/{id?}', [EmailTemplateController::class, 'storeTemplate'])->name('email_template.storeTemplate');
    Route::get('/email-template/{id}/edit', [EmailTemplateController::class, 'editTemplate'])->name('email_template.editTemplate');
    Route::delete('/email-template/{id}/delete', [EmailTemplateController::class, 'deleteTemplate'])->name('email_template.deleteTemplate');
    Route::get('email-template/preview/{id}', [EmailTemplateController::class, 'preview'])
        ->name('email_template.preview');
    Route::get('create_email_template', [EmailTemplateController::class, 'createEmailTemplate'])->name('create_email_template');


    Route::get('whatsapp_template', [WhatsAppTemplateController::class, 'index'])->name('whatsapp_template.index');
    Route::post('whatsapp_template', [WhatsAppTemplateController::class, 'storeTemplate'])->name('whatsapp_template.store');
    Route::get('whatsapp_template/{id}/edit', [WhatsAppTemplateController::class, 'edit'])->name('whatsapp_template.edit');
    Route::post('whatsapp_template/{id}', [WhatsAppTemplateController::class, 'update'])->name('whatsapp_template.update');
    Route::delete('whatsapp_template/{id}', [WhatsAppTemplateController::class, 'destroy'])->name('whatsapp_template.destroy');

    Route::get('campaign', [CampaignController::class, 'index'])->name('campaign.index');
    Route::get('campaign/report', [CampaignController::class, 'report'])->name('campaign.report');
    Route::get('campaign/failed-logs/{log_id}', [CampaignController::class, 'failedLogs'])->name('campaign.failed_logs');
    Route::post('combined-campaign-start', [CampaignController::class, 'startCampaign'])->name('start_combined_campaign');
    Route::post('/campaign/{id}/stop', [CampaignController::class, 'stop'])->name('campaign.stop');
    Route::post('/campaign/{id}/pause', [CampaignController::class, 'pause'])->name('campaign.pause');
    Route::post('/campaign/{id}/resume', [CampaignController::class, 'resume'])->name('campaign.resume');
    Route::post('/campaign/resend-single', [CampaignController::class, 'resendSingleFailed'])->name('campaign.resend_single');
    Route::post('/campaign/{id}/toggle-auto-resume', [CampaignController::class, 'toggleAutoResume'])
        ->name('campaign.toggle_auto_resume');
    Route::post('campaign/resend-failed-all/{log_id}', [CampaignController::class, 'triggerResendCampaignJob'])
        ->name('campaign.resend_failed_all');
    Route::post('campaign/resend-failed-email/{log_id}', [CampaignController::class, 'triggerResendEmailJob'])
        ->name('campaign.resend_failed_email');
    Route::post('campaign/resend-failed-whatsapp/{log_id}', [CampaignController::class, 'triggerResendWhatsAppJob'])
        ->name('campaign.resend_failed_whatsapp');
});

Route::resource('offer-popup', OfferPopUpController::class)->middleware(IsAdmin::class);
Route::post('/offer-popup/{id}/set-enable', [OfferPopUpController::class, 'setEnableOffer'])->name('offer-popup.set-enable');

Route::get('email-template/preview-order/{id}', [\App\Http\Controllers\Api\AutomationConfigController::class, 'index']);

Route::resource('recent_expire', RecentExpireController::class)->middleware(IsSalesAccess::class);
Route::post('/recent-expire/followup-update', [RecentExpireController::class, 'followupUpdate'])->name('recent_expire.followupUpdate');

// Payment Link Public Routes (No Authentication Required - Accessible by customers)
Route::get('/payment-link/callback', [OrderUserController::class, 'paymentLinkCallback'])
    ->name('payment_link.callback')
    ->withoutMiddleware([\App\Http\Middleware\Authenticate::class]);
    
Route::get('/payment-link/phonepe-callback', [OrderUserController::class, 'phonePePaymentLinkCallback'])
    ->name('payment_link.phonepe_callback')
    ->withoutMiddleware([\App\Http\Middleware\Authenticate::class]);
    
Route::post('/payment-link/phonepe-callback', [OrderUserController::class, 'phonePePaymentLinkCallback'])
    ->name('payment_link.phonepe_callback_post')
    ->withoutMiddleware([\App\Http\Middleware\Authenticate::class]);
    
Route::get('/payment-success', [OrderUserController::class, 'paymentSuccess'])
    ->name('payment.success')
    ->withoutMiddleware([\App\Http\Middleware\Authenticate::class]);
    
Route::get('/payment-failed', [OrderUserController::class, 'paymentFailed'])
    ->name('payment.failed')
    ->withoutMiddleware([\App\Http\Middleware\Authenticate::class]);

// Order User Routes (Requires Sales Access)
Route::resource('order_user', OrderUserController::class)->middleware(IsSalesAccess::class);
Route::post('/order-user/followup-update', [OrderUserController::class, 'followupUpdate'])->name('order_user.followupUpdate');
Route::get('/order-user/get-user-usage', [OrderUserController::class, 'getUserUsage'])->name('order_user.get_user_usage');
Route::get('/order-user/purchase-history/{userId}', [OrderUserController::class, 'getPurchaseHistory'])->name('order_user.purchase_history');
Route::get('/order-user/get-plans', [OrderUserController::class, 'getPlans'])->name('order_user.get_plans');
Route::post('/order-user/validate-email', [OrderUserController::class, 'validateEmail'])->name('order_user.validate_email');
Route::post('/order-user/create-payment-link', [OrderUserController::class, 'createPaymentLink'])->name('order_user.create_payment_link');

// Payment status check APIs (for testing/admin)
Route::get('/order-user/check-phonepe-status/{merchantOrderId}', [OrderUserController::class, 'checkPhonePeStatusApi'])->name('order_user.check_phonepe_status');
Route::get('/order-user/check-razorpay-status/{paymentLinkId}', [OrderUserController::class, 'checkRazorpayStatusApi'])->name('order_user.check_razorpay_status');

// PhonePe custom payment page routes
Route::get('/phonepe-payment/{referenceId}', [OrderUserController::class, 'showPhonePePaymentPage'])->name('phonepe.payment_page');
Route::post('/phonepe-payment/initiate', [OrderUserController::class, 'initiatePhonePePayment'])->name('phonepe.initiate_payment');


// API route for polling new orders (real-time updates)
// API route for polling new orders (real-time updates)
Route::get('/api/get-new-orders', [App\Http\Controllers\Api\OrderApiController::class, 'getNewOrders'])->name('api.get_new_orders');

// API route for checking order status changes (real-time updates)
Route::post('/api/check-order-status', [App\Http\Controllers\Api\OrderApiController::class, 'checkOrderStatusChanges'])->name('api.check_order_status');

// Combined API for syncing orders (new + status changes in one call)
Route::post('/api/sync-orders', [App\Http\Controllers\Api\OrderApiController::class, 'syncOrders'])->name('api.sync_orders');

Route::get('get-options/{table}/{idColumn}/{nameColumn}', function ($table, $idColumn, $nameColumn) {
    return DB::table($table)->select($idColumn, $nameColumn)->get();
})->middleware('auth');

Route::any("showTranscation",[HomeController::class,'showTranscation'])->name('showTranscation')->middleware(IsAdmin::class);

Route::get('/get-storage-link', function (Request $request) {
    $src = $request->query('src');
    return response()->json(['url' => ContentManager::getStorageLink($src)]);
})->middleware('auth');

Route::get('/get-dependent-value/{table}/{dependentColumn}/{dependentColumnId}/{id}', function ($table, $dependentColumn, $dependentColumnId, $id) {
    $value = DB::table($table)
        ->where($dependentColumnId, $id)
        ->value($dependentColumn);
    // return response()->json(['data' => $value ?? ""]);
    return response()->json($value ?? "");
})->middleware('auth');

Route::get('get-unique-options/{table}/{column}', function ($table, $column) {
    $results = DB::table($table)->select($column)->distinct()->get();

    $options = [];
    foreach ($results as $result) {
        // Remove brackets and double quotes, then split by comma
        $cleaned = str_replace(['[', ']', '"'], '', $result->$column);
        $tags = explode(',', $cleaned);
        foreach ($tags as $tag) {
            $trimmedTag = trim($tag);
            if ($trimmedTag !== '') { // Ensure empty values are skipped, but not '0'
                $options[] = ['value' => $trimmedTag, 'text' => $trimmedTag];
            }
        }
    }

    return response()->json($options);
})->middleware('auth');

Route::get("/free_exports",[SubscriptionController::class,'freeExports'])->name('free_exports');

Route::get('/clear-cache', function () {
    Artisan::call('optimize');
    Artisan::call('route:cache');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    return '<h1>Cache facade value cleared</h1>';
})->middleware(IsAdmin::class);

Route::get('/clear-trending', function () {
    Template::query()->update(array('trending_views' => 0));
    return '<h1>Trending cleared</h1>';
})->middleware(IsAdmin::class);

Route::get('seo_error_list', [SeoErrorListController::class, 'index'])->name('seo_error_list')->middleware(IsSeoAccess::class);
// SSE endpoint for real-time order updates
Route::get('/order-updates-stream', function() {
    return response()->stream(function() {
        // Set headers for SSE
        echo "data: " . json_encode(['type' => 'connected', 'message' => 'SSE Stream connected']) . "\n\n";
        ob_flush();
        flush();
        
        $lastOrderId = (int) request()->get('last_id', 0);
        
        while (true) {
            try {
                // Check for new orders
                $newOrders = \App\Models\Order::where('id', '>', $lastOrderId)
                    ->whereIn('status', ['pending', 'failed'])
                    ->where('is_deleted', 0)
                    ->orderBy('id', 'asc')
                    ->limit(10)
                    ->get();
                
                if ($newOrders->count() > 0) {
                    foreach ($newOrders as $order) {
                        // Get user data safely
                        $user = $order->user;
                        
                        $orderData = [
                            'type' => 'new_order',
                            'order' => [
                                'id' => $order->id,
                                'user_id' => $order->user_id ?? 'unknown',
                                'user_name' => $user ? $user->name : '-',
                                'email' => $user ? $user->email : '-',
                                'contact_no' => $order->contact_no ?? ($user ? $user->contact_no : '-'),
                                'amount' => $order->amount ?? '0',
                                'amount_with_symbol' => '₹' . ($order->amount ?? '0'),
                                'currency' => $order->currency ?? 'INR',
                                'status' => $order->status ?? 'pending',
                                'type' => $order->type ?? 'old_sub',
                                'plan_items' => '-',
                                'is_subscription_active' => false,
                                'created_at' => $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                                'email_template_count' => $order->email_template_count ?? 0,
                                'whatsapp_template_count' => $order->whatsapp_template_count ?? 0,
                                'from_where' => '-',
                                'followup_call' => $order->followup_call ?? 0,
                                'follow_by' => '-',
                                'emp_id' => $order->emp_id ?? 0,
                            ]
                        ];
                        
                        echo "data: " . json_encode($orderData) . "\n\n";
                        $lastOrderId = $order->id;
                        
                        \Log::info('SSE: Sent new order', ['order_id' => $order->id]);
                    }
                    
                    ob_flush();
                    flush();
                }
                
                // Sleep for 1 second before checking again
                sleep(1);
                
                // Check if connection is still alive
                if (connection_aborted()) {
                    \Log::info('SSE: Connection aborted');
                    break;
                }
                
            } catch (\Exception $e) {
                \Log::error('SSE Error', ['error' => $e->getMessage()]);
                echo "data: " . json_encode(['type' => 'error', 'message' => $e->getMessage()]) . "\n\n";
                ob_flush();
                flush();
                break;
            }
        }
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
        'X-Accel-Buffering' => 'no',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'Cache-Control',
    ]);
});