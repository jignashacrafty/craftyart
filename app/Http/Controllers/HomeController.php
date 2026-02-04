<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Models\Font;
use App\Models\Design;
use App\Models\Category;
use App\Models\AppCategory;
use App\Models\StickerCategory;
use App\Models\StickerItem;
use App\Models\BgCategory;
use App\Models\BgItem;
use App\Models\Color;
use App\Models\Interest;
use App\Models\Language;
use App\Models\NewCategory;
use App\Models\TransactionLog;
use App\Models\PurchaseHistory;
use App\Models\Religion;
use App\Models\SearchTag;
use App\Models\FrameCategory;
use App\Models\FrameItem;
use App\Models\Size;
use App\Models\SpecialKeyword;
use App\Models\Theme;
use App\Models\Draft;
use App\Models\ExportTable;
use App\Models\PendingTask;
use App\Models\Video\VideoCat;
use App\Models\Video\VideoPurchaseHistory;
use App\Models\Video\VideoTemplate;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends AppBaseController
{

    public function index($isManager = null)
    {
        $currentuserid = Auth::user()->user_type;

        $idAdmin = RoleManager::isAdmin(Auth::user()->user_type);
        $isSeoExecutive = RoleManager::isSeoExecutive(Auth::user()->user_type);
        $condition = "=";
        if ($idAdmin) {
            $condition = "!=";
            $currentuserid = -1;
        } else {
            $currentuserid = Auth::user()->id;
        }

        $datas['app'] = AppCategory::count();
        $datas['app_live'] = AppCategory::where('status', '1')->count();
        $datas['app_unlive'] = AppCategory::where('status', '0')->count();

        if ($isSeoExecutive) {
            $datas['pending_task'] = PendingTask::where('emp_id', Auth::user()->id)->where('status', 2)->count();
        }


        if (isset($isManager) && $isManager == 1) {
            $datas['fonts'] = Font::count();
            $datas['fonts_live'] = Font::where('status', '1')->count();
            $datas['fonts_unlive'] = Font::where('status', '0')->count();
        } else {
            $datas['fonts'] = Font::where('emp_id', $condition, $currentuserid)->count();
            $datas['fonts_live'] = Font::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['fonts_unlive'] = Font::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['cat'] = Category::count();
            $datas['cat_live'] = Category::where('status', '1')->count();
            $datas['cat_unlive'] = Category::where('status', '0')->count();
        } else {
            $datas['cat'] = Category::where('emp_id', $condition, $currentuserid)->count();
            $datas['cat_live'] = Category::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['cat_unlive'] = Category::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['item'] = Design::count();
            $datas['item_live'] = Design::where('status', '1')->count();
            $datas['item_unlive'] = Design::where('status', '0')->count();
        } else {
            $datas['item'] = Design::where('emp_id', $condition, $currentuserid)->count();
            $datas['item_live'] = Design::where('status', '1')->where('emp_id', $condition, $currentuserid)->count();
            $datas['item_unlive'] = Design::where('status', '0')->where('emp_id', $condition, $currentuserid)->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['stk_cat'] = StickerCategory::count();
            $datas['stk_cat_live'] = StickerCategory::where('status', '1')->count();
            $datas['stk_cat_unlive'] = StickerCategory::where('status', '0')->count();
        } else {
            $datas['stk_cat'] = StickerCategory::where('emp_id', $condition, $currentuserid)->count();
            $datas['stk_cat_live'] = StickerCategory::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['stk_cat_unlive'] = StickerCategory::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['stk_item'] = StickerItem::count();
            $datas['stk_item_live'] = StickerItem::where('status', '1')->count();
            $datas['stk_item_unlive'] = StickerItem::where('status', '0')->count();
        } else {
            $datas['stk_item'] = StickerItem::where('emp_id', $condition, $currentuserid)->count();
            $datas['stk_item_live'] = StickerItem::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['stk_item_unlive'] = StickerItem::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['bg_cat'] = BgCategory::count();
            $datas['bg_cat_live'] = BgCategory::where('status', '1')->count();
            $datas['bg_cat_unlive'] = BgCategory::where('status', '0')->count();
        } else {
            $datas['bg_cat'] = BgCategory::where('emp_id', $condition, $currentuserid)->count();
            $datas['bg_cat_live'] = BgCategory::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['bg_cat_unlive'] = BgCategory::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }


        if (isset($isManager) && $isManager == 1) {
            $datas['bg_item'] = BgItem::count();
            $datas['bg_item_live'] = BgItem::where('status', '1')->count();
            $datas['bg_item_unlive'] = BgItem::where('status', '0')->count();
        } else {
            $datas['bg_item'] = BgItem::where('emp_id', $condition, $currentuserid)->count();
            $datas['bg_item_live'] = BgItem::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['bg_item_unlive'] = BgItem::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // Color
        if (isset($isManager) && $isManager == 1) {
            $datas['color_item'] = Color::count();
            $datas['color_item_live'] = Color::where('status', '1')->count();
            $datas['color_item_unlive'] = Color::where('status', '0')->count();
        } else {
            $datas['color_item'] = Color::where('emp_id', $condition, $currentuserid)->count();
            $datas['color_item_live'] = Color::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['color_item_unlive'] = Color::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }
        // size
        if (isset($isManager) && $isManager == 1) {
            $datas['size_item'] = Size::count();
            $datas['size_item_live'] = Size::where('status', '1')->count();
            $datas['size_item_unlive'] = Size::where('status', '0')->count();
        } else {
            $datas['size_item'] = Size::where('emp_id', $condition, $currentuserid)->count();
            $datas['size_item_live'] = Size::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['size_item_unlive'] = Size::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }


        // Relegion
        if (isset($isManager) && $isManager == 1) {
            $datas['religion_item'] = Religion::count();
            $datas['religion_item_live'] = Religion::where('status', '1')->count();
            $datas['religion_item_unlive'] = Religion::where('status', '0')->count();
        } else {
            $datas['religion_item'] = Religion::where('emp_id', $condition, $currentuserid)->count();
            $datas['religion_item_live'] = Religion::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['religion_item_unlive'] = Religion::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // New Category
        if (isset($isManager) && $isManager == 1) {
            $datas['new_categories_item'] = NewCategory::count();
            $datas['new_categories_item_live'] = NewCategory::where('status', '1')->count();
            $datas['new_categories_item_unlive'] = NewCategory::where('status', '0')->count();
        } else {

            $datas['new_categories_item'] = NewCategory::where('emp_id', $condition, $currentuserid)->count();
            $datas['new_categories_item_live'] = NewCategory::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['new_categories_item_unlive'] = NewCategory::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // Language
        if (isset($isManager) && $isManager == 1) {
            $datas['language_item'] = Language::count();
            $datas['language_item_live'] = Language::where('status', '1')->count();
            $datas['language_item_unlive'] = Language::where('status', '0')->count();
        } else {
            $datas['language_item'] = Language::where('emp_id', $condition, $currentuserid)->count();
            $datas['language_item_live'] = Language::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['language_item_unlive'] = Language::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // Theme
        if (isset($isManager) && $isManager == 1) {
            $datas['theme_item'] = Theme::count();
            $datas['theme_item_live'] = Theme::where('status', '1')->count();
            $datas['theme_item_unlive'] = Theme::where('status', '0')->count();
        } else {
            $datas['theme_item'] = Theme::where('emp_id', $condition, $currentuserid)->count();
            $datas['theme_item_live'] = Theme::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['theme_item_unlive'] = Theme::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // SpecialKeyword
        if (isset($isManager) && $isManager == 1) {
            $datas['keyword_item'] = SpecialKeyword::count();
            $datas['keyword_item_live'] = SpecialKeyword::where('status', '1')->count();
            $datas['keyword_item_unlive'] = SpecialKeyword::where('status', '0')->count();
        } else {
            $datas['keyword_item'] = SpecialKeyword::where('emp_id', $condition, $currentuserid)->count();
            $datas['keyword_item_live'] = SpecialKeyword::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['keyword_item_unlive'] = SpecialKeyword::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // Search Tags
        if (isset($isManager) && $isManager == 1) {
            $datas['search_tag_item'] = SearchTag::count();
            $datas['search_tag_item_live'] = SearchTag::where('status', '1')->count();
            $datas['search_tag_item_unlive'] = SearchTag::where('status', '0')->count();
        } else {
            $datas['search_tag_item'] = SearchTag::where('emp_id', $condition, $currentuserid)->count();
            $datas['search_tag_item_live'] = SearchTag::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['search_tag_item_unlive'] = SearchTag::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // Interest
        if (isset($isManager) && $isManager == 1) {
            $datas['interest_item'] = Interest::count();
            $datas['interest_item_live'] = Interest::where('status', '1')->count();
            $datas['interest_item_unlive'] = Interest::where('status', '0')->count();
        } else {
            $datas['interest_item'] = Interest::where('emp_id', $condition, $currentuserid)->count();
            $datas['interest_item_live'] = Interest::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['interest_item_unlive'] = Interest::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }


        if (isset($isManager) && $isManager == 1) {
            $datas['frame_cat_item'] = FrameCategory::count();
            $datas['frame_cat_item_live'] = FrameCategory::where('status', '1')->count();
            $datas['frame_cat_item_unlive'] = FrameCategory::where('status', '0')->count();
        } else {
            $datas['frame_cat_item'] = FrameCategory::where('emp_id', $condition, $currentuserid)->count();
            $datas['frame_cat_item_live'] = FrameCategory::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['frame_cat_item_unlive'] = FrameCategory::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['frame_item'] = FrameItem::count();
            $datas['frame_item_live'] = FrameItem::where('status', '1')->count();
            $datas['frame_item_unlive'] = FrameItem::where('status', '0')->count();
        } else {
            $datas['frame_item'] = FrameItem::where('emp_id', $condition, $currentuserid)->count();
            $datas['frame_item_live'] = FrameItem::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['frame_item_unlive'] = FrameItem::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['video_cat_item'] = VideoCat::count();
            $datas['video_cat_item_live'] = VideoCat::where('status', '1')->count();
            $datas['video_cat_item_unlive'] = VideoCat::where('status', '0')->count();
        } else {
            $datas['video_cat_item'] = VideoCat::where('emp_id', $condition, $currentuserid)->count();
            $datas['video_cat_item_live'] = VideoCat::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['video_cat_item_unlive'] = VideoCat::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['video_template_item'] = VideoTemplate::count();
            $datas['video_template_item_live'] = VideoTemplate::where('status', '1')->count();
            $datas['video_template_item_unlive'] = VideoTemplate::where('status', '0')->count();
        } else {
            $datas['video_template_item'] = VideoTemplate::where('emp_id', $condition, $currentuserid)->count();
            $datas['video_template_item_live'] = VideoTemplate::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['video_template_item_unlive'] = VideoTemplate::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if ($idAdmin) {

            //------------------------------------------------------------//------------------------------------------------------------//------------------------------------------------------------

            $currentMonth = Carbon::now();
            $startDate = Carbon::now()->copy()->startOfMonth()->subMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $currentYear = Carbon::now()->year;
            $lastYear = Carbon::now()->subYear()->year;

            $today_templates_inr = PurchaseHistory::where('currency_code', 'INR')->whereDate('created_at', Carbon::today())->sum('amount');
            $today_templates_usd = PurchaseHistory::where('currency_code', 'USD')->whereDate('created_at', Carbon::today())->sum('amount');

            $yesterday_templates_inr = PurchaseHistory::where('currency_code', 'INR')->whereDate('created_at', Carbon::yesterday())->sum('amount');
            $yesterday_templates_usd = PurchaseHistory::where('currency_code', 'USD')->whereDate('created_at', Carbon::yesterday())->sum('amount');

            $this_month_templates_inr = PurchaseHistory::where('currency_code', 'INR')->whereYear('created_at', $currentMonth->year)->whereMonth('created_at', $currentMonth->month)->sum('amount');
            $this_month_templates_usd = PurchaseHistory::where('currency_code', 'USD')->whereYear('created_at', $currentMonth->year)->whereMonth('created_at', $currentMonth->month)->sum('amount');

            $last_month_templates_inr = PurchaseHistory::where('currency_code', 'INR')->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
            $last_month_templates_usd = PurchaseHistory::where('currency_code', 'USD')->whereBetween('created_at', [$startDate, $endDate])->sum('amount');

            $this_year_templates_inr = PurchaseHistory::where('currency_code', 'INR')->whereYear('created_at', $currentYear)->sum('amount');
            $this_year_templates_usd = PurchaseHistory::where('currency_code', 'USD')->whereYear('created_at', $currentYear)->sum('amount');

            $last_year_templates_inr = PurchaseHistory::where('currency_code', 'INR')->whereYear('created_at', $lastYear)->sum('amount');
            $last_year_templates_usd = PurchaseHistory::where('currency_code', 'USD')->whereYear('created_at', $lastYear)->sum('amount');

            $total_templates_inr = PurchaseHistory::where('currency_code', 'INR')->sum('amount');
            $total_templates_usd = PurchaseHistory::where('currency_code', 'USD')->sum('amount');

            //------------------------------------------------------------//------------------------------------------------------------//------------------------------------------------------------

            $today_video_inr = VideoPurchaseHistory::where('currency_code', 'INR')->whereDate('created_at', Carbon::today())->sum('amount');
            $today_video_usd = VideoPurchaseHistory::where('currency_code', 'USD')->whereDate('created_at', Carbon::today())->sum('amount');

            $yesterday_video_inr = VideoPurchaseHistory::where('currency_code', 'INR')->whereDate('created_at', Carbon::yesterday())->sum('amount');
            $yesterday_video_usd = VideoPurchaseHistory::where('currency_code', 'USD')->whereDate('created_at', Carbon::yesterday())->sum('amount');

            $this_month_video_inr = VideoPurchaseHistory::where('currency_code', 'INR')->whereYear('created_at', $currentMonth->year)->whereMonth('created_at', $currentMonth->month)->sum('amount');
            $this_month_video_usd = VideoPurchaseHistory::where('currency_code', 'USD')->whereYear('created_at', $currentMonth->year)->whereMonth('created_at', $currentMonth->month)->sum('amount');

            $last_month_video_inr = VideoPurchaseHistory::where('currency_code', 'INR')->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
            $last_month_video_usd = VideoPurchaseHistory::where('currency_code', 'USD')->whereBetween('created_at', [$startDate, $endDate])->sum('amount');

            $this_year_video_inr = VideoPurchaseHistory::where('currency_code', 'INR')->whereYear('created_at', $currentYear)->sum('amount');
            $this_year_video_usd = VideoPurchaseHistory::where('currency_code', 'USD')->whereYear('created_at', $currentYear)->sum('amount');

            $last_year_video_inr = VideoPurchaseHistory::where('currency_code', 'INR')->whereYear('created_at', $lastYear)->sum('amount');
            $last_year_video_usd = VideoPurchaseHistory::where('currency_code', 'USD')->whereYear('created_at', $lastYear)->sum('amount');

            $total_video_inr = VideoPurchaseHistory::where('currency_code', 'INR')->sum('amount');
            $total_video_usd = VideoPurchaseHistory::where('currency_code', 'USD')->sum('amount');

            //------------------------------------------------------------//------------------------------------------------------------//------------------------------------------------------------

            $today_subs_inr = TransactionLog::where('currency_code', 'Rs')->whereDate('created_at', Carbon::today())->sum('paid_amount');
            $today_subs_usd = TransactionLog::where('currency_code', '$')->whereDate('created_at', Carbon::today())->sum('paid_amount');

            $yesterday_subs_inr = TransactionLog::where('currency_code', 'Rs')->whereDate('created_at', Carbon::yesterday())->sum('paid_amount');
            $yesterday_subs_usd = TransactionLog::where('currency_code', '$')->whereDate('created_at', Carbon::yesterday())->sum('paid_amount');

            $this_month_subs_inr = TransactionLog::where('currency_code', 'Rs')->whereYear('created_at', $currentMonth->year)->whereMonth('created_at', $currentMonth->month)->sum('paid_amount');
            $this_month_subs_usd = TransactionLog::where('currency_code', '$')->whereYear('created_at', $currentMonth->year)->whereMonth('created_at', $currentMonth->month)->sum('paid_amount');

            $last_month_subs_inr = TransactionLog::where('currency_code', 'Rs')->whereBetween('created_at', [$startDate, $endDate])->sum('paid_amount');
            $last_month_subs_usd = TransactionLog::where('currency_code', '$')->whereBetween('created_at', [$startDate, $endDate])->sum('paid_amount');

            $this_year_subs_inr = TransactionLog::where('currency_code', 'Rs')->whereYear('created_at', $currentYear)->sum('paid_amount');
            $this_year_subs_usd = TransactionLog::where('currency_code', '$')->whereYear('created_at', $currentYear)->sum('paid_amount');

            $last_year_subs_inr = TransactionLog::where('currency_code', 'Rs')->whereYear('created_at', $lastYear)->sum('paid_amount');
            $last_year_subs_usd = TransactionLog::where('currency_code', '$')->whereYear('created_at', $lastYear)->sum('paid_amount');

            $total_subs_inr = TransactionLog::where('currency_code', 'Rs')->sum('paid_amount');
            $total_subs_usd = TransactionLog::where('currency_code', '$')->sum('paid_amount');

            //------------------------------------------------------------//------------------------------------------------------------//------------------------------------------------------------

            $today_inr = $today_templates_inr + $today_subs_inr + $today_video_inr;
            $today_usd = $today_templates_usd + $today_subs_usd + $today_video_usd;

            $yesterday_inr = $yesterday_templates_inr + $yesterday_subs_inr + $yesterday_video_inr;
            $yesterday_usd = $yesterday_templates_usd + $yesterday_subs_usd + $yesterday_video_usd;

            $this_month_inr = $this_month_templates_inr + $this_month_subs_inr + $this_month_video_inr;
            $this_month_usd = $this_month_templates_usd + $this_month_subs_usd + $this_month_video_usd;

            $last_month_inr = $last_month_templates_inr + $last_month_subs_inr + $last_month_video_inr;
            $last_month_usd = $last_month_templates_usd + $last_month_subs_usd + $last_month_video_usd;

            $this_year_inr = $this_year_templates_inr + $this_year_subs_inr + $this_year_video_inr;
            $this_year_usd = $this_year_templates_usd + $this_year_subs_usd + $this_year_video_usd;

            $last_year_inr = $last_year_templates_inr + $last_year_subs_inr + $last_year_video_inr;
            $last_year_usd = $last_year_templates_usd + $last_year_subs_usd + $last_year_video_usd;

            $total_inr = $total_templates_inr + $total_subs_inr + $total_video_inr;
            $total_usd = $total_templates_usd + $total_subs_usd + $total_video_usd;

            $datas['today_templates_inr'] = 'Rs ' . $today_templates_inr;
            $datas['today_video_inr'] = 'Rs ' . $today_video_inr;
            $datas['today_subs_inr'] = 'Rs ' . $today_subs_inr;
            $datas['today_templates_usd'] = '$ ' . $today_templates_usd;
            $datas['today_video_usd'] = '$ ' . $today_video_usd;
            $datas['today_subs_usd'] = '$ ' . $today_subs_usd;

            $datas['yesterday_templates_inr'] = 'Rs ' . $yesterday_templates_inr;
            $datas['yesterday_video_inr'] = 'Rs ' . $yesterday_video_inr;
            $datas['yesterday_subs_inr'] = 'Rs ' . $yesterday_subs_inr;
            $datas['yesterday_templates_usd'] = '$ ' . $yesterday_templates_usd;
            $datas['yesterday_video_usd'] = '$ ' . $yesterday_video_usd;
            $datas['yesterday_subs_usd'] = '$ ' . $yesterday_subs_usd;

            $datas['this_month_templates_inr'] = 'Rs ' . $this_month_templates_inr;
            $datas['this_month_video_inr'] = 'Rs ' . $this_month_video_inr;
            $datas['this_month_subs_inr'] = 'Rs ' . $this_month_subs_inr;
            $datas['this_month_templates_usd'] = '$ ' . $this_month_templates_usd;
            $datas['this_month_video_usd'] = '$ ' . $this_month_video_usd;
            $datas['this_month_subs_usd'] = '$ ' . $this_month_subs_usd;

            $datas['last_month_templates_inr'] = 'Rs ' . $last_month_templates_inr;
            $datas['last_month_video_inr'] = 'Rs ' . $last_month_video_inr;
            $datas['last_month_subs_inr'] = 'Rs ' . $last_month_subs_inr;
            $datas['last_month_templates_usd'] = '$ ' . $last_month_templates_usd;
            $datas['last_month_video_usd'] = '$ ' . $last_month_video_usd;
            $datas['last_month_subs_usd'] = '$ ' . $last_month_subs_usd;


            $datas['this_year_templates_inr'] = 'Rs ' . $this_year_templates_inr;
            $datas['this_year_video_inr'] = 'Rs ' . $this_year_video_inr;
            $datas['this_year_subs_inr'] = 'Rs ' . $this_year_subs_inr;
            $datas['this_year_templates_usd'] = '$ ' . $this_year_templates_usd;
            $datas['this_year_video_usd'] = '$ ' . $this_year_video_usd;
            $datas['this_year_subs_usd'] = '$ ' . $this_year_subs_usd;

            $datas['last_year_templates_inr'] = 'Rs ' . $last_year_templates_inr;
            $datas['last_year_video_inr'] = 'Rs ' . $last_year_video_inr;
            $datas['last_year_subs_inr'] = 'Rs ' . $last_year_subs_inr;
            $datas['last_year_templates_usd'] = '$ ' . $last_year_templates_usd;
            $datas['last_year_video_usd'] = '$ ' . $last_year_video_usd;
            $datas['last_year_subs_usd'] = '$ ' . $last_year_subs_usd;

            $datas['total_templates_inr'] = 'Rs ' . $total_templates_inr;
            $datas['total_video_inr'] = 'Rs ' . $total_video_inr;
            $datas['total_subs_inr'] = 'Rs ' . $total_subs_inr;
            $datas['total_templates_usd'] = '$ ' . $total_templates_usd;
            $datas['total_video_usd'] = '$ ' . $total_video_usd;
            $datas['total_subs_usd'] = '$ ' . $total_subs_usd;

            $datas['today_inr'] = 'Rs ' . $today_inr;
            $datas['today_usd'] = '$ ' . $today_usd;

            $datas['yesterday_inr'] = 'Rs ' . $yesterday_inr;
            $datas['yesterday_usd'] = '$ ' . $yesterday_usd;

            $datas['this_month_inr'] = 'Rs ' . $this_month_inr;
            $datas['this_month_usd'] = '$ ' . $this_month_usd;

            $datas['last_month_inr'] = 'Rs ' . $last_month_inr;
            $datas['last_month_usd'] = '$ ' . $last_month_usd;

            $datas['this_year_inr'] = 'Rs ' . $this_year_inr;
            $datas['this_year_usd'] = '$ ' . $this_year_usd;

            $datas['last_year_inr'] = 'Rs ' . $last_year_inr;
            $datas['last_year_usd'] = '$ ' . $last_year_usd;

            $datas['total_inr'] = 'Rs ' . $total_inr;
            $datas['total_usd'] = '$ ' . $total_usd;


            $today_razorpay_inr = PurchaseHistory::where('currency_code', 'INR')->where('payment_method', 'Razorpay')->whereDate('created_at', Carbon::today())->sum('amount') + VideoPurchaseHistory::where('currency_code', 'INR')->where('payment_method', 'Razorpay')->whereDate('created_at', Carbon::today())->sum('amount') + TransactionLog::where('currency_code', 'Rs')->where('payment_method', 'Razorpay')->whereDate('created_at', Carbon::today())->sum('paid_amount');

            $yesterday_razorpay_inr = PurchaseHistory::where('currency_code', 'INR')->where('payment_method', 'Razorpay')->whereDate('created_at', Carbon::yesterday())->sum('amount') + VideoPurchaseHistory::where('currency_code', 'INR')->where('payment_method', 'Razorpay')->whereDate('created_at', Carbon::yesterday())->sum('amount') + TransactionLog::where('currency_code', 'Rs')->where('payment_method', 'Razorpay')->whereDate('created_at', Carbon::yesterday())->sum('paid_amount');


            $datas['today_razorpay_inr'] = 'Rs ' . $today_razorpay_inr;
            $datas['yesterday_razorpay_inr'] = 'Rs ' . $yesterday_razorpay_inr;

            //            $visits = EditorVisit::whereDate('created_at', Carbon::today())->count();
            $drafts = Draft::whereDate('created_at', Carbon::today())->count();
            $exports = ExportTable::whereDate('created_at', Carbon::today())->count();

            $datas['editor_history'] = "Drafts: $drafts, Exports: $exports";

        }

        $datas['cache'] = env('CACHE_VER', '1');

        return view('dashboard')->with('datas', $datas);
    }

    public function update_cache_ver(Request $request)
    {
        $cache_ver = $request->input('cache_ver');

        $this->setEnv('CACHE_VER', $cache_ver);

        return response()->json([
            'success' => 'Cache update successfully.'
        ]);
    }

    private function setEnv($key, $value)
    {
        file_put_contents(app()->environmentFilePath(), str_replace(
            $key . '=' . env($key, '1'),
            $key . '=' . $value,
            file_get_contents(app()->environmentFilePath())
        ));
    }
}
