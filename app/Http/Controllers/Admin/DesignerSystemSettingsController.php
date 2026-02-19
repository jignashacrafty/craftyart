<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesignerType;
use App\Models\DesignerCategory;
use App\Models\DesignerGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DesignerSystemSettingsController extends Controller
{
    // ============ TYPES ============
    
    public function typesIndex()
    {
        $types = DesignerType::orderBy('sort_order')->get();
        return view('admin.designer_settings.types', compact('types'));
    }

    public function storeType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DesignerType::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Type created successfully');
    }

    public function updateType(Request $request, $id)
    {
        $type = DesignerType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $type->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? $type->sort_order,
        ]);

        return redirect()->back()->with('success', 'Type updated successfully');
    }

    public function deleteType($id)
    {
        $type = DesignerType::findOrFail($id);
        $type->delete();
        return redirect()->back()->with('success', 'Type deleted successfully');
    }

    public function toggleTypeActive($id)
    {
        $type = DesignerType::findOrFail($id);
        $type->is_active = !$type->is_active;
        $type->save();
        return redirect()->back()->with('success', 'Type status updated');
    }

    // ============ CATEGORIES ============
    
    public function categoriesIndex()
    {
        $categories = DesignerCategory::orderBy('sort_order')->get();
        return view('admin.designer_settings.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DesignerCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Category created successfully');
    }

    public function updateCategory(Request $request, $id)
    {
        $category = DesignerCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? $category->sort_order,
        ]);

        return redirect()->back()->with('success', 'Category updated successfully');
    }

    public function deleteCategory($id)
    {
        $category = DesignerCategory::findOrFail($id);
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully');
    }

    public function toggleCategoryActive($id)
    {
        $category = DesignerCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();
        return redirect()->back()->with('success', 'Category status updated');
    }

    // ============ GOALS ============
    
    public function goalsIndex()
    {
        $goals = DesignerGoal::orderBy('sort_order')->get();
        return view('admin.designer_settings.goals', compact('goals'));
    }

    public function storeGoal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DesignerGoal::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Goal created successfully');
    }

    public function updateGoal(Request $request, $id)
    {
        $goal = DesignerGoal::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $goal->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? $goal->sort_order,
        ]);

        return redirect()->back()->with('success', 'Goal updated successfully');
    }

    public function deleteGoal($id)
    {
        $goal = DesignerGoal::findOrFail($id);
        $goal->delete();
        return redirect()->back()->with('success', 'Goal deleted successfully');
    }

    public function toggleGoalActive($id)
    {
        $goal = DesignerGoal::findOrFail($id);
        $goal->is_active = !$goal->is_active;
        $goal->save();
        return redirect()->back()->with('success', 'Goal status updated');
    }
}
