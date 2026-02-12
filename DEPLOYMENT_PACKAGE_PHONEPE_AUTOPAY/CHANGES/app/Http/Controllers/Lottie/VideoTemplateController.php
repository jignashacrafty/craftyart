<?php

namespace App\Http\Controllers\Lottie;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Http\Controllers\Controller;
use App\Models\SearchTag;
use App\Models\Video\VideoCat;
use App\Models\Video\VideoTemplate;
use App\Models\Video\VideoType;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SoareCostin\FileVault\Facades\FileVault;
use DB;
use Auth;
use Carbon\Carbon;

class VideoTemplateController extends AppBaseController
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $request)
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = RoleManager::isAdminOrDesignerManager(Auth::user()->user_type);
        $condition = "=";


        if ($idAdmin) {
            $condition = "!=";
            $currentuserid = -1;
        }
        // Sorting
        $sortingField = $request->get('sort_by', 'created_at');
        $sortingOrder = $request->get('sort_order', 'desc');

        if ($sortingField == '') {
            $sortingField = 'id';
        }
        // Pagination
        $perPage = $request->get('per_page', 10);

        // Filtering
        $query = $request->get('query', '');

        $items = VideoTemplate::with('videoCat')
            ->where('emp_id', $condition, $currentuserid)
            ->where('isDeleted', 0)
            ->where(function ($queryBuilder) use ($query) {
                if (!empty($query)) {
                    $queryBuilder->where('relation_id', 'like', "%$query%")
                        ->orWhere('video_name', 'like', "%$query%")
                        ->orWhereHas('videoCat', function ($subQuery) use ($query) {
                            $subQuery->where('category_name', 'like', "%$query%");
                        });
                }
            })
            ->orderBy($sortingField, $sortingOrder)
            ->paginate($perPage);

        // dd($items);
        $datas['item'] = $items;
        return view('videos/show_item')->with('itemArray', $datas);
    }


    public function create(Request $request)
    {
        $datas['cat'] = VideoCat::all();
        $datas['templateType'] = VideoType::all();
        $datas['appId'] = $request->input('passingAppId');
        $datas['searchTagArray'] = SearchTag::all();
        return view('videos/create_item')->with('datas', $datas);
    }

    public function store(Request $request)
    {
        $currentuserid = Auth::user()->id;

        $res = new VideoTemplate;

        $accessCheck = $this->isAccessByRole("design");
        if ($accessCheck) {
            return response()->json([
                'status' => false,
                'error' => $accessCheck,
            ]);
        }

        $this->validate($request, ['video_thumb' => 'required|image|mimes:jpg,png,gif|max:2048']);
        $this->validate($request, ['video_file' => 'required|file|mimes:mp4,mov|max:20000']);
        $this->validate($request, ['zip_file' => 'required|file|mimes:zip|max:15000']);

        $video_thumb = $request->file('video_thumb');
        if ($video_thumb != null) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $video_thumb->getClientOriginalExtension();
            StorageUtils::storeAs($video_thumb, 'uploadedFiles/vThumb_file', $new_name);
            $res->video_thumb = 'uploadedFiles/vThumb_file/' . $new_name;
        }

        $video_file = $request->file('video_file');
        if ($video_file != null) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $video_file->getClientOriginalExtension();
            StorageUtils::storeAs($video_file, 'uploadedFiles/video_file', $new_name);
            $res->video_url = 'uploadedFiles/video_file/' . $new_name;
        }

        $zip_file = $request->file('zip_file');
        if ($zip_file != null) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $zip_file->getClientOriginalExtension();
            StorageUtils::storeAs($zip_file, 'uploadedFiles/vZip_file', $new_name);
            $res->video_zip_url = 'uploadedFiles/vZip_file/' . $new_name;
            $res->folder_name = $new_name;
        }

        $res->emp_id = $currentuserid;
        $res->relation_id = $request->input('relation_id');
        $res->string_id = $this->generateId();
        $res->category_id = $request->input('category_id');

        $res->video_name = $request->input('video_name');
        $res->pages = $request->input('pages');
        $res->width = $request->input('width');
        $res->height = $request->input('height');
        $res->watermark_height = $request->input('watermark_height');
        $res->template_type = $request->input('template_type');
        $res->do_front_lottie = $request->input('do_front_lottie');

        $img_array = array();

        if ($request->has('img_key')) {
            $count = count($request->img_key);
            $img_key = $request->img_key;
            $isShape = $request->img_shape;


            for ($i = 0; $i < $count; $i++) {
                $img_array[] = array(
                    'key' => $img_key[$i],
                    'isShape' => $isShape[$i],
                );
            }
        }

        $res->editable_image = json_encode($img_array);


        $text_array = array();

        if ($request->has('editable_text_id')) {
            $count = count($request->editable_text_id);
            $keys = $request->key;
            $titles = $request->title;
            $editable_text_id = $request->editable_text_id;
            $font_family = $request->font_family;


            for ($i = 0; $i < $count; $i++) {
                $text_array[] = array(
                    'key' => $keys[$i],
                    'title' => $titles[$i],
                    'value' => $editable_text_id[$i],
                    'font_family' => $font_family[$i],
                );
            }
        }

        $res->editable_text = json_encode($text_array);
        if ($request->input('keywords') != null) {
            $res->keyword = $request->input('keywords');
        }

        $encrypted = $request->input('encrypted');
        $res->encrypted = $encrypted;
        if ($encrypted == '1') {
            $res->encryption_key = $request->input('encryption_key');
        } else {
            $res->encryption_key = null;
        }

        $res->change_music = $request->input('change_music');
        $res->is_premium = $request->input('is_premium');
        $res->status = $request->input('status');
        $res->save();

        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function edit(VideoTemplate $item, $id)
    {
        $vData = VideoTemplate::where('id', $id)->where('isDeleted', 0)->first();
        if (!$vData) {
            abort(404);
        }

        $datas['cat'] = VideoCat::all();
        $datas['templateType'] = VideoType::all();
        $datas['searchTagArray'] = SearchTag::all();
        $datas['item'] = VideoTemplate::find($id);
        $datas['editable_image'] = json_decode($datas['item']->editable_image, true);
        $datas['editable_text'] = json_decode($datas['item']->editable_text, true);
        return view('videos/edit_item')->with('dataArray', $datas);
    }

    public function update(Request $request, VideoTemplate $item)
    {
        $res = VideoTemplate::where('id', $request->id)->where('isDeleted', 0)->first();
        if (!$res) {
            return response()->json(['status' => false, 'error' => 'Template not found.'], 404);
        }

        $accessCheck = $this->isAccessByRole("design", $res->id, $res->emp_id ?? null);
        if ($accessCheck) {
            return response()->json(['status' => false, 'error' => $accessCheck]);
        }

        $video_thumb = $request->file('video_thumb');
        if ($video_thumb != null) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $video_thumb->getClientOriginalExtension();
            StorageUtils::storeAs($video_thumb, 'uploadedFiles/vThumb_file', $new_name);
            $res->video_thumb = 'uploadedFiles/vThumb_file/' . $new_name;
        }

        $video_file = $request->file('video_file');
        if ($video_file != null) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $video_file->getClientOriginalExtension();
            StorageUtils::storeAs($video_file, 'uploadedFiles/video_file', $new_name);
            $res->video_url = 'uploadedFiles/video_file/' . $new_name;
        }

        $zip_file = $request->file('zip_file');
        if ($zip_file != null) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $zip_file->getClientOriginalExtension();
            StorageUtils::storeAs($zip_file, 'uploadedFiles/vZip_file', $new_name);
            $res->video_zip_url = 'uploadedFiles/vZip_file/' . $new_name;
            $res->folder_name = $new_name;
        }

        $res->relation_id = $request->input('relation_id');

        // Don't update these fields - they're managed in SEO edit
        // $res->category_id = $request->input('category_id');
        // $res->video_name = $request->input('video_name');

        $res->pages = $request->input('pages');
        $res->width = $request->input('width');
        $res->height = $request->input('height');
        $res->watermark_height = $request->input('watermark_height');
        $res->template_type = $request->input('template_type');
        $res->do_front_lottie = $request->input('do_front_lottie');

        $img_array = array();

        if ($request->has('img_key')) {
            $count = count($request->img_key);
            $img_key = $request->img_key;
            $isShape = $request->img_shape;


            for ($i = 0; $i < $count; $i++) {
                $img_array[] = array(
                    'key' => $img_key[$i],
                    'isShape' => $isShape[$i],
                );
            }
        }

        $res->editable_image = json_encode($img_array);


        $text_array = array();

        if ($request->has('editable_text_id')) {
            $count = count($request->editable_text_id);
            $keys = $request->key;
            $titles = $request->title;
            $editable_text_id = $request->editable_text_id;
            $font_family = $request->font_family;


            for ($i = 0; $i < $count; $i++) {
                $text_array[] = array(
                    'key' => $keys[$i],
                    'title' => $titles[$i],
                    'value' => $editable_text_id[$i],
                    'font_family' => $font_family[$i],
                );
            }
        }

        $res->editable_text = json_encode($text_array);

        // Don't update these fields - they're managed in SEO edit
        // if ($request->input('keywords') != null) {
        //     $res->keyword = $request->input('keywords');
        // }

        $encrypted = $request->input('encrypted');
        $res->encrypted = $encrypted;
        if ($encrypted == '1') {
            $res->encryption_key = $request->input('encryption_key');
        } else {
            $res->encryption_key = null;
        }
        $res->change_music = $request->input('change_music');
        // Don't update these fields - they're managed in SEO edit
        // $res->is_premium = $request->input('is_premium');
        // $res->status = $request->input('status');
        $res->save();

        return response()->json(['status' => true, 'message' => 'Video updated successfully.']);
    }

    public function destroy(VideoTemplate $item, $id)
    {
        $currentuserid = Auth::user()->id;

        $idAdmin = RoleManager::isAdminOrDesignerManager(Auth::user()->user_type);

        if ($idAdmin) {
            $res = VideoTemplate::find($id);
            $res->isDeleted = 1;
            $res->save();
        }

        // try {
        //    unlink(storage_path("app/public/".$video_thumb));
        // } catch (\Exception $e) {
        // }

        // try {
        //     unlink(storage_path("app/public/".$video_url));
        // } catch (\Exception $e) {
        // }

        // try {
        //     unlink(storage_path("app/public/".$video_zip_url));
        // } catch (\Exception $e) {
        // }

        // VideoTemplate::destroy(array('id', $id));
        return redirect('show_v_item');
    }

    function sortArray($a, $b)
    {
        return strnatcasecmp($a['imageName'], $b['imageName']);
    }


    public static function generateId($length = 8)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $string_id = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (VideoTemplate::where('string_id', $string_id)->exists());
        return $string_id;
    }

    public function editSeo(VideoTemplate $item, $id)
    {
        $vData = VideoTemplate::where('id', $id)->where('isDeleted', 0)->first();
        if (!$vData) {
            abort(404);
        }

        $datas['cat'] = VideoCat::all();
        $allCategoriesCollection = VideoCat::getAllCategoriesWithSubcategories();
        $datas['allCategories'] = $allCategoriesCollection->map(function($category) {
            return [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'subcategories' => $category->subcategories->map(function($sub) {
                    return [
                        'id' => $sub->id,
                        'category_name' => $sub->category_name,
                        'subcategories' => $sub->subcategories ?? []
                    ];
                })->toArray()
            ];
        })->toArray();
        
        $datas['searchTagArray'] = SearchTag::all();
        $datas['item'] = VideoTemplate::find($id);
        
        // Get selected category for display
        $selectedCategory = VideoCat::find($datas['item']->category_id);
        $datas['select_category'] = $selectedCategory ? $selectedCategory->toArray() : [];
        
        // Get filter data
        $datas['langArray'] = \App\Models\Language::all();
        $datas['themeArray'] = \App\Models\Theme::all();
        $datas['styleArray'] = \App\Models\Style::all();
        $datas['sizes'] = \App\Models\Size::all();
        $datas['religions'] = \App\Models\Religion::all();
        $datas['interestArray'] = \App\Models\Interest::all();
        
        return view('videos/edit_seo_item')->with('dataArray', $datas);
    }

    public function updateSeo(Request $request, VideoTemplate $item)
    {
        $res = VideoTemplate::where('id', $request->id)->where('isDeleted', 0)->first();
        if (!$res) {
            abort(404);
        }

        // Update basic fields
        $res->video_name = $request->input('video_name');
        $res->category_id = $request->input('category_id');
        
        if ($request->input('keywords') != null) {
            $res->keyword = $request->input('keywords');
        }
        
        $res->is_premium = $request->input('is_premium');
        $res->status = $request->input('status');

        // Update SEO fields
        $res->id_name = $request->input('id_name');
        $res->h2_tag = $request->input('h2_tag');
        $res->canonical_link = $request->input('canonical_link');
        $res->meta_title = $request->input('meta_title');
        $res->description = $request->input('description');
        $res->meta_description = $request->input('meta_description');

        // Update filter fields
        if ($request->has('lang_id')) {
            $res->lang_id = json_encode($request->input('lang_id'));
        }
        
        if ($request->has('theme_id')) {
            $res->theme_id = json_encode($request->input('theme_id'));
        }
        
        if ($request->has('styles')) {
            $res->style_id = json_encode($request->input('styles'));
        }
        
        $res->orientation = $request->input('orientation');
        $res->template_size = $request->input('template_size');
        
        if ($request->has('religion_id')) {
            $res->religion_id = json_encode($request->input('religion_id'));
        }
        
        if ($request->has('interest_id')) {
            $res->interest_id = json_encode($request->input('interest_id'));
        }
        
        $res->is_freemium = $request->input('is_freemium');
        
        // Handle date range
        if ($request->input('date_range') && $request->input('date_range') != '') {
            $dateRange = explode(' - ', $request->input('date_range'));
            if (count($dateRange) == 2) {
                // Convert from "MM/DD/YYYY" to "YYYY-MM-DD" for database storage
                try {
                    $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($dateRange[0]))->format('Y-m-d');
                    $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($dateRange[1]))->format('Y-m-d');
                    $res->start_date = $startDate;
                    $res->end_date = $endDate;
                } catch (\Exception $e) {
                    // If parsing fails, try to use the dates as-is
                    $res->start_date = trim($dateRange[0]);
                    $res->end_date = trim($dateRange[1]);
                }
            }
        } else {
            // Clear dates if date_range is empty
            $res->start_date = null;
            $res->end_date = null;
        }
        
        $res->color_ids = $request->input('color_ids');

        $res->save();
        
        return response()->json([
            'success' => true,
            'message' => 'SEO data updated successfully.'
        ]);
    }
}
