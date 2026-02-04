<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TemplateRate;
use Illuminate\Support\Collection;

class RateController extends Controller
{

    public static function getRates($needVid = false,$isCaricature = false): Collection
    {
        if (!$needVid) {
            $rateTypes = ['premium_template', 'freemium_template', 'remove_watermark'];
        } elseif ($isCaricature){
            $rateTypes = ['premium_caricature', 'free_caricature', 'watermark_caricature'];
        } else {
            $rateTypes = ['premium_template', 'freemium_template', 'remove_watermark', 'mobile_video'];
        }


        return TemplateRate::whereIn('name', $rateTypes)
            ->pluck('value', 'name')
            ->map(function ($value) {
                $decoded = json_decode($value);
                return $decoded ?? [];
            });
    }

    public static function getTemplateRates(Collection $rates, $size, $isPremium = true, $isFreemium = false, $hasAnimation = false, $isEditorChoice = false): array
    {

        if ($isPremium) {
            $value = $rates['premium_template'] ?? TemplateRate::getRates("premium_template");
        } else if ($isFreemium) {
            $value = $rates['freemium_template'] ?? TemplateRate::getRates("freemium_template");
        } else {
            $value = $rates['remove_watermark'] ?? TemplateRate::getRates("remove_watermark");
        }

        if ($size == 0) {
            $size = 1;
        }

        $extraPage = $size - 1;

        $inrAmount = $value->inr->base_price + ($value->inr->page_price * $extraPage);
        $usdAmount = $value->usd->base_price + ($value->usd->page_price * $extraPage);
        $inrAmount = min($inrAmount, $value->inr->max_price);
        $usdAmount = min($usdAmount, $value->usd->max_price);

        if ($hasAnimation) {
            $inrAmount = $inrAmount + $value->inr->animation;
            $usdAmount = $usdAmount + $value->usd->animation;
        }

        if ($isEditorChoice) {
            $inrAmount = $inrAmount + $value->inr->editor_choice;
            $usdAmount = $usdAmount + $value->usd->editor_choice;
        }

        $payment['inrVal'] = $inrAmount;
        $payment['usdVal'] = $usdAmount;
        $payment['inrAmount'] = '₹' . $inrAmount;
        $payment['usdAmount'] = '$' . $usdAmount;

        return $payment;
    }

    public static function getVideoRates($rates, $size): array
    {
        $value = $rates['mobile_video'] ?? TemplateRate::getRates("mobile_video");

        if ($size == 0) {
            $size = 1;
        }

        $extraPage = $size - 1;

        $inrAmount = $value->inr->base_price + ($value->inr->page_price * $extraPage);
        $usdAmount = $value->usd->base_price + ($value->usd->page_price * $extraPage);
        $inrAmount = min($inrAmount, $value->inr->max_price);
        $usdAmount = min($usdAmount, $value->usd->max_price);

        $payment['inrVal'] = $inrAmount;
        $payment['usdVal'] = $usdAmount;
        $payment['inrAmount'] = '₹' . $inrAmount;
        $payment['usdAmount'] = '$' . $usdAmount;

        return $payment;
    }

    public static function getCaricatureRates($rates, $size,$isPremium = true, $isFreemium = false, $hasAnimation = false, $isEditorChoice = false): array
    {
//        $value = $rates['caricature'] ??  TemplateRate::getRates("premium_caricature",true);
        if ($isPremium) {
            $value = $rates['premium_caricature'] ?? TemplateRate::getRates("premium_caricature");
        } else if ($isFreemium) {
            $value = $rates['free_caricature'] ?? TemplateRate::getRates("free_caricature");
        } else {
            $value = $rates['watermark_caricature'] ?? TemplateRate::getRates("watermark_caricature");
        }

        if ($size == 0) {
            $size = 1;
        }

        $extraPage = $size - 1;

        $inrAmount = $value->inr->base_price + ($value->inr->head_price * $extraPage);
        $usdAmount = $value->usd->base_price + ($value->usd->head_price * $extraPage);
        $inrAmount = min($inrAmount, $value->inr->max_price);
        $usdAmount = min($usdAmount, $value->usd->max_price);

        if ($hasAnimation) {
            $inrAmount = $inrAmount + $value->inr->animation;
            $usdAmount = $usdAmount + $value->usd->animation;
        }

        if ($isEditorChoice) {
            $inrAmount = $inrAmount + $value->inr->editor_choice;
            $usdAmount = $usdAmount + $value->usd->editor_choice;
        }

        $payment['inrVal'] = $inrAmount;
        $payment['usdVal'] = $usdAmount;
        $payment['inrAmount'] = '₹' . $inrAmount;
        $payment['usdAmount'] = '$' . $usdAmount;

        return $payment;
    }

}
