<?php

namespace App\Http\Controllers\Lottie;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Video\VideoCat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoCatController extends AppBaseController
{

    public function create()
    {
        $allCategories = VideoCat::getAllCategoriesWithSubcategories();
        return view('videos/create_cat', compact('allCategories'));
    }

    public function store(Request $request)
    {
        $res = new VideoCat;
        $res->category_name = $request->input('category_name');

        $accessCheck = $this->isAccessByRole("design");
        if ($accessCheck) {
            return response()->json([
                'status' => false,
                'error' => $accessCheck,
            ]);
        }

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
        $searchableFields = [
            ['id' => 'id', 'value' => 'ID'],
            ['id' => 'category_name', 'value' => 'Category Name'],
            ['id' => 'sequence_number', 'value' => 'Sequence Number'],
            ['id' => 'status', 'value' => 'Status'],
        ];

        $query = VideoCat::query();

        $filterBy = $request->input('filter_by');
        $filterValue = $request->input('filter_value');

        if ($filterBy && $filterValue !== null && $filterValue !== '') {
            $query->where($filterBy, 'like', '%' . $filterValue . '%');
        }

        $catArray = $query->orderBy('id', 'desc')->paginate(10);

        return view('videos.show_cat', compact('catArray', 'searchableFields'));
    }


    public function edit(VideoCat $videoCat, $id)
    {
        $datas['cat'] = VideoCat::findOrFail($id);
        $datas['allCategories'] = VideoCat::getAllCategoriesWithSubcategories();
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


        // Validation
        $rules = [
            'category_name' => 'required|string|max:255',
            'sequence_number' => 'required|numeric',
            'status' => 'required|in:0,1',
        ];

        if ($request->hasFile('category_thumb')) {
            $rules['category_thumb'] = 'image|mimes:jpg,jpeg,png,gif|max:2048';
        }

        $validated = $request->validate($rules);

        // Update fields
        $res->category_name = $request->input('category_name');
        $res->sequence_number = $request->input('sequence_number');
        $res->parent_category_id = $request->input('parent_category_id');
        $res->status = $request->input('status');

        // Image upload
        if ($request->hasFile('category_thumb')) {
            $image = $request->file('category_thumb');
            $new_name = rand() . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/vCatThumb', $new_name);
            $res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
        }

        $res->save();

        // AJAX response
        if ($request->ajax()) {
            return response()->json(['success' => true]);
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
}
