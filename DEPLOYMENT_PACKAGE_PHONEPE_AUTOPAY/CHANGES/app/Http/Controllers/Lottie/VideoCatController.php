<?php

namespace App\Http\Controllers\Lottie;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Video\VideoCat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoCatController extends AppBaseController
{

    public function create()
    {
        $allCategories = VideoCat::getAllCategoriesWithSubcategories();
        $appArray = \App\Models\AppCategory::all();
        $userRole = \App\Models\User::whereIn('user_type', [1, 2, 3])->get();
        return view('videos/create_cat', compact('allCategories', 'appArray', 'userRole'));
    }

    public function store(Request $request)
    {
        $res = new VideoCat;

        $accessCheck = $this->isAccessByRole("design");
        if ($accessCheck) {
            return response()->json([
                'status' => false,
                'error' => $accessCheck,
            ]);
        }

        // Basic fields
        $res->category_name = $request->input('category_name');
        $res->id_name = $request->input('id_name');
        $res->canonical_link = $request->input('canonical_link');
        $res->seo_emp_id = $request->input('seo_emp_id');
        $res->meta_title = $request->input('meta_title');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->h1_tag = $request->input('h1_tag');
        $res->tag_line = $request->input('tag_line');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->h2_tag = $request->input('h2_tag');
        $res->long_desc = $request->input('long_desc');

        // Handle category thumb
        if ($request->hasFile('category_thumb')) {
            // Traditional file upload
            $image = $request->file('category_thumb');
            $this->validate($request, ['category_thumb' => 'required|image|mimes:jpg,png,gif,webp,svg|max:2048']);
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/vCatThumb', $new_name);
            $res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
        } elseif ($request->has('category_thumb') && $request->input('category_thumb')) {
            // Base64 image from dynamic file input
            $base64Image = $request->input('category_thumb');
            if (strpos($base64Image, 'data:image') === 0) {
                // It's a base64 image, save it
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.png';
                $image_parts = explode(";base64,", $base64Image);
                $image_base64 = base64_decode($image_parts[1]);
                Storage::disk('public')->put('uploadedFiles/vCatThumb/' . $new_name, $image_base64);
                $res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
            } elseif (filter_var($base64Image, FILTER_VALIDATE_URL)) {
                // It's a URL, store it as is
                $res->category_thumb = $base64Image;
            }
        }

        // Handle mockup
        if ($request->hasFile('mockup')) {
            $mockup = $request->file('mockup');
            $this->validate($request, ['mockup' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $mockup->getClientOriginalExtension();
            StorageUtils::storeAs($mockup, 'uploadedFiles/vCatMockup', $new_name);
            $res->mockup = 'uploadedFiles/vCatMockup/' . $new_name;
        } elseif ($request->has('mockup') && $request->input('mockup')) {
            $base64Image = $request->input('mockup');
            if (strpos($base64Image, 'data:image') === 0) {
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.png';
                $image_parts = explode(";base64,", $base64Image);
                $image_base64 = base64_decode($image_parts[1]);
                Storage::disk('public')->put('uploadedFiles/vCatMockup/' . $new_name, $image_base64);
                $res->mockup = 'uploadedFiles/vCatMockup/' . $new_name;
            } elseif (filter_var($base64Image, FILTER_VALIDATE_URL)) {
                $res->mockup = $base64Image;
            }
        }

        // Handle banner
        if ($request->hasFile('banner')) {
            $banner = $request->file('banner');
            $this->validate($request, ['banner' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $banner->getClientOriginalExtension();
            StorageUtils::storeAs($banner, 'uploadedFiles/vCatBanner', $new_name);
            $res->banner = 'uploadedFiles/vCatBanner/' . $new_name;
        } elseif ($request->has('banner') && $request->input('banner')) {
            $base64Image = $request->input('banner');
            if (strpos($base64Image, 'data:image') === 0) {
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.png';
                $image_parts = explode(";base64,", $base64Image);
                $image_base64 = base64_decode($image_parts[1]);
                Storage::disk('public')->put('uploadedFiles/vCatBanner/' . $new_name, $image_base64);
                $res->banner = 'uploadedFiles/vCatBanner/' . $new_name;
            } elseif (filter_var($base64Image, FILTER_VALIDATE_URL)) {
                $res->banner = $base64Image;
            }
        }

        $res->app_id = $request->input('app_id');
        
        // Handle JSON fields - don't double encode, model will handle it
        if ($request->has('contents')) {
            $contents = $request->input('contents');
            // If it's a JSON string from form, decode it
            $res->contents = is_string($contents) ? json_decode($contents, true) : $contents;
        }
        if ($request->has('faqs')) {
            $faqs = $request->input('faqs');
            $res->faqs = is_string($faqs) ? json_decode($faqs, true) : $faqs;
        }
        if ($request->has('top_keywords')) {
            $topKeywords = $request->input('top_keywords');
            $res->top_keywords = is_string($topKeywords) ? json_decode($topKeywords, true) : $topKeywords;
        }

        $res->sequence_number = $request->input('sequence_number');
        $res->parent_category_id = $request->input('parent_category_id', 0);
        $res->status = $request->input('status');
        $res->emp_id = auth()->user()->id;
        $res->save();
        
        return response()->json([
            'success' => 'Category Added successfully.'
        ]);
    }

    public function show(Request $request)
    {
        $searchableFields = [
            ['id' => 'id', 'value' => 'ID'],
            ['id' => 'category_name', 'value' => 'Category Name'],
            ['id' => 'sequence_number', 'value' => 'Sequence Number'],
            ['id' => 'status', 'value' => 'Status'],
        ];

        $query = VideoCat::query();

        // Handle search query
        $searchQuery = $request->input('query');
        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('id', 'like', '%' . $searchQuery . '%')
                  ->orWhere('category_name', 'like', '%' . $searchQuery . '%')
                  ->orWhere('id_name', 'like', '%' . $searchQuery . '%')
                  ->orWhere('sequence_number', 'like', '%' . $searchQuery . '%');
            });
        }

        // Handle filter by specific field
        $filterBy = $request->input('filter_by');
        $filterValue = $request->input('filter_value');
        if ($filterBy && $filterValue !== null && $filterValue !== '') {
            $query->where($filterBy, 'like', '%' . $filterValue . '%');
        }

        // Handle sorting
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Handle pagination
        $perPage = $request->input('per_page', 10);
        if ($perPage === 'all') {
            $catArray = $query->get();
            // Create a mock paginator for 'all' option
            $catArray = new \Illuminate\Pagination\LengthAwarePaginator(
                $catArray,
                $catArray->count(),
                $catArray->count(),
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $catArray = $query->paginate($perPage)->appends($request->query());
        }

        return view('videos.show_cat', compact('catArray', 'searchableFields'));
    }


    public function edit(VideoCat $videoCat, $id)
    {
        $datas['cat'] = VideoCat::findOrFail($id);
        $datas['allCategories'] = VideoCat::getAllCategoriesWithSubcategories();
        $datas['appArray'] = \App\Models\AppCategory::all();
        $datas['userRole'] = \App\Models\User::whereIn('user_type', [1, 2, 3])->get();
        return view('videos.edit_cat', compact('datas'));
    }


    public function update(Request $request, VideoCat $videoCat)
    {
        $res = VideoCat::findOrFail($request->id);

        $accessCheck = $this->isAccessByRole("design", $res->id, $res->emp_id ?? null);
        if ($accessCheck) {
            if ($request->ajax()) {
                return response()->json(['message' => $accessCheck], 403);
            }
            return redirect()->back()->with('error', $accessCheck);
        }

        // Update basic fields
        $res->category_name = $request->input('category_name');
        $res->id_name = $request->input('id_name');
        $res->canonical_link = $request->input('canonical_link');
        $res->seo_emp_id = $request->input('seo_emp_id');
        $res->meta_title = $request->input('meta_title');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->h1_tag = $request->input('h1_tag');
        $res->tag_line = $request->input('tag_line');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->h2_tag = $request->input('h2_tag');
        $res->long_desc = $request->input('long_desc');

        // Handle category thumb
        if ($request->hasFile('category_thumb')) {
            $image = $request->file('category_thumb');
            $this->validate($request, ['category_thumb' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            // Delete old image
            if ($res->category_thumb && $res->category_thumb != 'uploadedFiles/vCatThumb/no_image.png' && !filter_var($res->category_thumb, FILTER_VALIDATE_URL)) {
                StorageUtils::delete('public/' . $res->category_thumb);
            }
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/vCatThumb', $new_name);
            $res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
        } elseif ($request->has('category_thumb') && $request->input('category_thumb')) {
            $base64Image = $request->input('category_thumb');
            if (strpos($base64Image, 'data:image') === 0) {
                // Delete old image
                if ($res->category_thumb && $res->category_thumb != 'uploadedFiles/vCatThumb/no_image.png' && !filter_var($res->category_thumb, FILTER_VALIDATE_URL)) {
                    StorageUtils::delete('public/' . $res->category_thumb);
                }
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.png';
                $image_parts = explode(";base64,", $base64Image);
                $image_base64 = base64_decode($image_parts[1]);
                Storage::disk('public')->put('uploadedFiles/vCatThumb/' . $new_name, $image_base64);
                $res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
            } elseif (filter_var($base64Image, FILTER_VALIDATE_URL)) {
                $res->category_thumb = $base64Image;
            }
        }

        // Handle mockup
        if ($request->hasFile('mockup')) {
            $mockup = $request->file('mockup');
            $this->validate($request, ['mockup' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            // Delete old mockup
            if ($res->mockup && !filter_var($res->mockup, FILTER_VALIDATE_URL)) {
                StorageUtils::delete('public/' . $res->mockup);
            }
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $mockup->getClientOriginalExtension();
            StorageUtils::storeAs($mockup, 'uploadedFiles/vCatMockup', $new_name);
            $res->mockup = 'uploadedFiles/vCatMockup/' . $new_name;
        } elseif ($request->has('mockup') && $request->input('mockup')) {
            $base64Image = $request->input('mockup');
            if (strpos($base64Image, 'data:image') === 0) {
                if ($res->mockup && !filter_var($res->mockup, FILTER_VALIDATE_URL)) {
                    StorageUtils::delete('public/' . $res->mockup);
                }
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.png';
                $image_parts = explode(";base64,", $base64Image);
                $image_base64 = base64_decode($image_parts[1]);
                Storage::disk('public')->put('uploadedFiles/vCatMockup/' . $new_name, $image_base64);
                $res->mockup = 'uploadedFiles/vCatMockup/' . $new_name;
            } elseif (filter_var($base64Image, FILTER_VALIDATE_URL)) {
                $res->mockup = $base64Image;
            }
        }

        // Handle banner
        if ($request->hasFile('banner')) {
            $banner = $request->file('banner');
            $this->validate($request, ['banner' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            // Delete old banner
            if ($res->banner && !filter_var($res->banner, FILTER_VALIDATE_URL)) {
                StorageUtils::delete('public/' . $res->banner);
            }
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $banner->getClientOriginalExtension();
            StorageUtils::storeAs($banner, 'uploadedFiles/vCatBanner', $new_name);
            $res->banner = 'uploadedFiles/vCatBanner/' . $new_name;
        } elseif ($request->has('banner') && $request->input('banner')) {
            $base64Image = $request->input('banner');
            if (strpos($base64Image, 'data:image') === 0) {
                if ($res->banner && !filter_var($res->banner, FILTER_VALIDATE_URL)) {
                    StorageUtils::delete('public/' . $res->banner);
                }
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.png';
                $image_parts = explode(";base64,", $base64Image);
                $image_base64 = base64_decode($image_parts[1]);
                Storage::disk('public')->put('uploadedFiles/vCatBanner/' . $new_name, $image_base64);
                $res->banner = 'uploadedFiles/vCatBanner/' . $new_name;
            } elseif (filter_var($base64Image, FILTER_VALIDATE_URL)) {
                $res->banner = $base64Image;
            }
        }

        $res->app_id = $request->input('app_id');
        
        // Handle JSON fields - don't double encode, model will handle it
        if ($request->has('contents')) {
            $contents = $request->input('contents');
            // If it's a JSON string from form, decode it
            $res->contents = is_string($contents) ? json_decode($contents, true) : $contents;
        }
        if ($request->has('faqs')) {
            $faqs = $request->input('faqs');
            $res->faqs = is_string($faqs) ? json_decode($faqs, true) : $faqs;
        }
        if ($request->has('top_keywords')) {
            $topKeywords = $request->input('top_keywords');
            $res->top_keywords = is_string($topKeywords) ? json_decode($topKeywords, true) : $topKeywords;
        }

        $res->sequence_number = $request->input('sequence_number');
        $res->parent_category_id = $request->input('parent_category_id', 0);
        $res->status = $request->input('status');
        $res->save();

        // AJAX response
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Category updated successfully.']);
        }

        return redirect()->route('show_v_cat')->with('success', 'Category updated successfully.');
    }



    public function destroy(VideoCat $videoCat, $id)
    {
        $res = VideoCat::find($id);
        $category_thumb = $res->category_thumb;
        $contains = Str::contains($category_thumb, 'no_image');
        if (!$contains) {
            try {
                unlink(storage_path("app/public/" . $category_thumb));
            } catch (\Exception $e) {
            }
        }
        VideoCat::destroy(array('id', $id));
        return redirect('show_v_cat');
    }

    public function imp_update(Request $request, $id)
    {
        try {
            $res = VideoCat::findOrFail($id);
            
            // Check access
            $accessCheck = $this->isAccessByRole("design", $res->id, $res->emp_id ?? null);
            if ($accessCheck) {
                return response()->json(['error' => $accessCheck], 403);
            }
            
            // Toggle IMP status
            $res->imp = $res->imp == '1' ? '0' : '1';
            
            if ($res->save()) {
                return response()->json([
                    'success' => 'IMP status updated successfully.'
                ]);
            } else {
                return response()->json(['error' => 'Failed to save'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Data not found'], 404);
        }
    }
}
