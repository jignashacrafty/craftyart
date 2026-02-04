<?php

namespace App\Traits;

use App\Models\AdminChangesLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait UpdateLogger
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait UpdateLogger
{
    public static function bootUpdateLogger()
    {
        // Ensure this runs only for Eloquent models
        if (!is_subclass_of(static::class, Model::class)) {
            return;
        }

        static::updated(function ($model) {
            try {
                // getChanges contains only attributes that were changed and saved
                $changes = $model->getChanges();

                // Exclude updated_at and any sensitive fields you don't want logged
                $exclude = ['updated_at', 'created_at', 'password', 'remember_token'];
                foreach ($exclude as $key) {
                    if (array_key_exists($key, $changes)) {
                        unset($changes[$key]);
                    }
                }

                // If there are no meaningful changes, skip
                if (empty($changes)) {
                    return;
                }

                // Save to admin changes log
                AdminChangesLog::create([
                    'emp_id'        => Auth::id(),
                    'model'          => get_class($model),
                    'model_id'       => $model->getKey(),
                    'updated_fields' => json_encode($changes),
                    'ip_address'     => Request::ip(),
                ]);
            } catch (\Throwable $e) {
                // Log error but don't break the main flow
                \Log::error('UpdateLogger error: ' . $e->getMessage());
            }
        });
    }
}
