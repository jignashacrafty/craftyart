<?php

namespace App\Http\Controllers\Plan;

use App\Enums\PlanFeatureSlug;
use App\Http\Controllers\AppBaseController;
use App\Models\PlanCategoryFeature;
use App\Models\PlanFeature;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanFeatureController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $categoryFeatures = PlanCategoryFeature::all();
        $slugOptions = PlanFeatureSlug::toArray();
        $searchableFields = [["id" => 'id', "value" => 'Id'], ["id" => 'name', "value" => 'Name'], ["id" => 'slug', "value" => 'slug']];
        $features = $this->applyFiltersAndPagination($request, PlanFeature::query(), $searchableFields);
        return view("plan.plan_feature.index", [
            'features' => $features,
            'categoryFeatures' => $categoryFeatures,
            'slugOptions' => $slugOptions,
            'searchableFields' => $searchableFields
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $inputs = $request->except('_token');

        try {
            $isUpdate = !empty($inputs['feature_id']);

            if (!empty($inputs['slug'])) {   // only check if slug is not null/empty
                $existing = PlanFeature::where('slug', $inputs['slug']);
                if ($isUpdate) {
                    $existing->where('id', '!=', $inputs['feature_id']);
                }
                if ($existing->exists()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Slug already exists. Please use a different feature name.'
                    ]);
                }
            }



            if ($isUpdate) {
                $feature = PlanFeature::findOrFail($inputs['feature_id']);
                $feature->update($inputs);
            } else {
                PlanFeature::create($inputs);
            }

            return response()->json([
                'status' => true,
                'message' => 'Feature has been ' . ($isUpdate ? 'updated' : 'added') . ' successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(PlanFeature $feature): JsonResponse
    {
        return response()->json([
            'feature' => $feature,
            'slugOptions' => PlanFeatureSlug::toArray()
        ]);
    }

    public function destroy(PlanFeature $feature): JsonResponse
    {
        try {
            $feature->delete();
            return response()->json([
                'status' => true,
                'message' => 'Feature has been deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
