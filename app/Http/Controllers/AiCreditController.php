<?php

namespace App\Http\Controllers;

use App\Models\AiCredit;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Foundation\Application;

class AiCreditController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ['id' => 'id', 'value' => 'Id'],
            ['id' => 'credits', 'value' => 'Credits'],
            ['id' => 'disc', 'value' => 'Disc'],
            ['id' => 'inr_price', 'value' => 'Inr Price'],
            ['id' => 'usd_price', 'value' => 'Usd Price'],
            ['id' => 'status', 'value' => 'Status'],
        ];

        $aiCredits = $this->applyFiltersAndPagination(
            $request,
            AiCredit::query(),
            $searchableFields,
        );

        return view('ai_credits.index', compact('aiCredits', 'searchableFields'));
    }

    public function submit(Request $request): JsonResponse
    {
        try {
            // Update case
            if ($request->credit_id) {
                $aiCredit = AiCredit::findOrFail($request->credit_id);

                $aiCredit->update($request->only(['credits', 'disc', 'inr_price', 'usd_price', 'status']));

                return response()->json([
                    'status' => true,
                    'success' => 'AI Credit has been updated successfully.',
                ]);
            }
            // Create case
            else {
                AiCredit::create([
                    'credits' => $request['credits'],
                    'disc' => $request['disc'] ?? 0,
                    'inr_price' => $request['inr_price'],
                    'usd_price' => $request['usd_price'],
                    'status' => $request['status'],
                ]);

                return response()->json([
                    'status' => true,
                    'success' => 'AI Credit has been added successfully.',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /** Remove the specified resource from storage.*/
    public function destroy($id): JsonResponse
    {
        try {
            $aiCredit = AiCredit::findOrFail($id);
            $aiCredit->delete();

            return response()->json([
                'status' => true,
                'success' => "AI Credit has been deleted successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}