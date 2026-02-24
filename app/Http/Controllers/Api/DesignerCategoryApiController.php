<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesignerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\ResponseHandler;
use App\Interfaces\ResponseInterface;

class DesignerCategoryApiController extends Controller implements ResponseInterface
{
  use ResponseHandler;

  /**
   * Get all designer categories
   * 
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(Request $request)
  {
    try {
      $query = DesignerCategory::query();

      // Filter by active status if provided
      if ($request->has('is_active')) {
        $query->where('is_active', $request->is_active);
      }

      // Order by sort_order
      $categories = $query->orderBy('sort_order')->get();

      return $this->sendResponse([
        'categories' => $categories,
        'total' => $categories->count()
      ], 'Categories retrieved successfully', $request);

    } catch (\Exception $e) {
      return $this->sendError('Failed to retrieve categories', $e->getMessage(), 500, $request);
    }
  }

  /**
   * Get single category by ID
   * 
   * @param Request $request
   * @param int $id
   * @return \Illuminate\Http\JsonResponse
   */
  public function show(Request $request, $id)
  {
    try {
      $category = DesignerCategory::find($id);

      if (!$category) {
        return $this->sendError('Category not found', 'No category exists with this ID', 404, $request);
      }

      return $this->sendResponse([
        'category' => $category
      ], 'Category retrieved successfully', $request);

    } catch (\Exception $e) {
      return $this->sendError('Failed to retrieve category', $e->getMessage(), 500, $request);
    }
  }

  /**
   * Create new category
   * 
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'icon' => 'nullable|string|max:255',
        'sort_order' => 'nullable|integer',
      ]);

      if ($validator->fails()) {
        return $this->sendError('Validation failed', $validator->errors()->toArray(), 422, $request);
      }

      $category = DesignerCategory::create([
        'name' => $request->name,
        'slug' => Str::slug($request->name),
        'description' => $request->description,
        'icon' => $request->icon,
        'sort_order' => $request->sort_order ?? 0,
        'is_active' => true,
      ]);

      return $this->sendResponse([
        'category' => $category
      ], 'Category created successfully', $request);

    } catch (\Exception $e) {
      return $this->sendError('Failed to create category', $e->getMessage(), 500, $request);
    }
  }

  /**
   * Update existing category
   * 
   * @param Request $request
   * @param int $id
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(Request $request, $id)
  {
    try {
      $category = DesignerCategory::find($id);

      if (!$category) {
        return $this->sendError('Category not found', 'No category exists with this ID', 404, $request);
      }

      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'icon' => 'nullable|string|max:255',
        'sort_order' => 'nullable|integer',
      ]);

      if ($validator->fails()) {
        return $this->sendError('Validation failed', $validator->errors()->toArray(), 422, $request);
      }

      $category->update([
        'name' => $request->name,
        'slug' => Str::slug($request->name),
        'description' => $request->description,
        'icon' => $request->icon,
        'sort_order' => $request->sort_order ?? $category->sort_order,
      ]);

      return $this->sendResponse([
        'category' => $category->fresh()
      ], 'Category updated successfully', $request);

    } catch (\Exception $e) {
      return $this->sendError('Failed to update category', $e->getMessage(), 500, $request);
    }
  }

  /**
   * Toggle category active status
   * 
   * @param Request $request
   * @param int $id
   * @return \Illuminate\Http\JsonResponse
   */
  public function toggleActive(Request $request, $id)
  {
    try {
      $category = DesignerCategory::find($id);

      if (!$category) {
        return $this->sendError('Category not found', 'No category exists with this ID', 404, $request);
      }

      $category->is_active = !$category->is_active;
      $category->save();

      return $this->sendResponse([
        'category' => $category
      ], 'Category status updated successfully', $request);

    } catch (\Exception $e) {
      return $this->sendError('Failed to update category status', $e->getMessage(), 500, $request);
    }
  }

  /**
   * Delete category
   * 
   * @param Request $request
   * @param int $id
   * @return \Illuminate\Http\JsonResponse
   */
  public function destroy(Request $request, $id)
  {
    try {
      $category = DesignerCategory::find($id);

      if (!$category) {
        return $this->sendError('Category not found', 'No category exists with this ID', 404, $request);
      }

      $category->delete();

      return $this->sendResponse([
        'deleted_id' => $id
      ], 'Category deleted successfully', $request);

    } catch (\Exception $e) {
      return $this->sendError('Failed to delete category', $e->getMessage(), 500, $request);
    }
  }
}
