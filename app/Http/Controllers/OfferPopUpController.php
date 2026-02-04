<?php

namespace App\Http\Controllers;

use App\Models\OfferPopUp;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfferPopUpController extends AppBaseController
{
    private function fromMsLabel($ms): string
    {
        if($ms == 0) return "0".' Sec';
        if ($ms % (60 * 60 * 1000) === 0) {
            return ($ms / (60 * 60 * 1000)) . ' Hour';
        } elseif ($ms % (60 * 1000) === 0) {
            return ($ms / (60 * 1000)) . ' Min';
        } else {
            return ($ms / 1000) . ' Sec';
        }
    }
    private function fromMsValueUnit($ms): array
    {
        if($ms == 0) return ['value' => 0, 'unit' => 'sec'];
        if ($ms % (60 * 60 * 1000) === 0) {
            return ['value' => $ms / (60 * 60 * 1000), 'unit' => 'hour'];
        } elseif ($ms % (60 * 1000) === 0) {
            return ['value' => $ms / (60 * 1000), 'unit' => 'min'];
        } else {
            return ['value' => $ms / 1000, 'unit' => 'sec'];
        }
    }

    private function toMs(int $val, string $unit): int
    {
        return match ($unit) {
            'sec' => $val * 1000,
            'min' => $val * 60 * 1000,
            'hour' => $val * 60 * 60 * 1000,
            default => $val,
        };
    }

    private function frequencyMsToDays($ms): int
    {
        if (!$ms) return 0;
        return (int) ($ms / (24 * 60 * 60 * 1000));
    }

    public function index(): Factory|View|Application
    {
        $offers = OfferPopUp::all()->map(function ($offer) {
            $offer->duration_time_label = $this->fromMsLabel($offer->duration);
            $offer->force_show_duration_label = $this->fromMsLabel($offer->force_show_duration);
            $offer->frequency_duration = $this->frequencyMsToDays($offer->frequency_duration) . ' Day';
            return $offer;
        });

        return view('offer_popup.index', compact('offers'));
    }

    public function store(Request $request): JsonResponse
    {

        $data = [
            'enable_offer' => $request->has('enable_offer') ? 1 : 0,
            'duration' => $this->toMs($request->duration_time_value, $request->duration_time_unit) ?? 0,
            'frequency_duration' => $request->frequency_duration_value * 24 * 60 * 60 * 1000 ?? 0,
            'force_show_duration' => $request->has('enable_force') ? $this->toMs($request->force_show_duration_value, $request->force_show_duration_unit) : 0,
            'enable_force' => $request->has('enable_force') ? 1 : 0,
        ];

        if ($request->id) {
            $offer = OfferPopUp::findOrFail($request->id);
            $offer->update($data);
        } else {
            $offer = OfferPopUp::create($data);
        }

        return response()->json(['status' => true, 'data' => $offer]);
    }

    public function edit($id): JsonResponse
    {
        $offer = OfferPopUp::findOrFail($id);

        return response()->json([
            'id' => $offer->id,
            'enable_offer' => (bool) $offer->enable_offer,
            'enable_force' => (bool) $offer->enable_force,
            'duration' => $this->fromMsValueUnit($offer->duration),
            'frequency_duration' => $this->frequencyMsToDays($offer->frequency_duration),
            'force_show_duration' => $this->fromMsValueUnit($offer->force_show_duration),
        ]);
    }

    public function setEnableOffer(Request $request, $id): JsonResponse
    {
        $offer = OfferPopUp::findOrFail($id);

        $offer->enable_offer = !$offer->enable_offer;
        $offer->save();

        return response()->json([
            'status' => true,
            'enable_offer' => $offer->enable_offer
        ]);
    }

}
