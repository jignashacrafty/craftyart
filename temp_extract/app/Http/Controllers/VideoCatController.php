<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Video\VideoCat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoCatController extends AppBaseController
{

    public function index()
    {

    }

    public function create()
    {
        $allCategories = VideoCat::getAllCategoriesWithSubcategories();
        return view('videos/create_cat', compact('allCategories'));
    }

    public function store(Request $request)
    {
        $res = new VideoCat;
        $res->category_name = $request->input('category_name');

        $image = $request->file('category_thumb');
        if ($image != null) {
            $this->validate($request, ['category_thumb' => 'required|image|mimes:jpg,png,gif|max:2048']);
            $new_name = rand() . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/vCatThumb', $new_name);
            $res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
        } else {
            $res->category_thumb = 'uploadedFiles/vCatThumb/no_image.png';
        }

        $res->sequence_number = $request->input('sequence_number');
        $res->parent_category_id = $request->input('parent_category_id');
        $res->status = $request->input('status');
        $res->emp_id = auth()->user()->id;
        $res->save();
        $request->session()->flash('msg', 'Category Added');
        return redirect('show_v_cat');
    }

    public function show(Request $request)
    {

        $query = $request->input('query', '');
        $perPage = $request->input('per_page', 20);
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');
        // Validate sort order
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        // Filter and paginate results
        $cateArray = VideoCat::where('category_name', 'like', '%' . $query . '%')
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        return view('videos/show_cat')->with('catArray', $cateArray);
    }

    public function edit(VideoCat $videoCat, $id)
    {
        $datas['cat'] = VideoCat::find($id);
        $datas['allCategories'] = VideoCat::getAllCategoriesWithSubcategories();
        return view('videos/edit_cat')->with('datas', $datas);
    }

    public function update(Request $request, VideoCat $videoCat)
    {
        $res = VideoCat::find($request->id);
        $res->category_name = $request->input('category_name');

        $image = $request->file('category_thumb');
        if ($image != null) {
            $this->validate($request, ['category_thumb' => 'required|image|mimes:jpg,png,gif|max:2048']);
            $new_name = rand() . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/vCatThumb', $new_name);
            $res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
        }

        $res->sequence_number = $request->input('sequence_number');
        $res->parent_category_id = $request->input('parent_category_id');
        $res->status = $request->input('status');
        $res->save();

        return redirect('show_v_cat');
    }

    public function destroy(VideoCat $videoCat, $id)
    {
        $res = VideoCat::find($id);
        $category_thumb = $res->category_thumb;
        $contains = Str::contains($category_thumb, 'no_image');
        if (!$contains) {
            try {
                StorageUtils::delete($category_thumb);
            } catch (\Exception $e) {
            }
        }
        VideoCat::destroy(array('id', $id));
        return redirect('show_v_cat');
    }
}