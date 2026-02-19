<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignerApplication extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'experience',
        'experience_level',
        'skills',
        'portfolio_links',
        'uploaded_samples',
        'selected_types',
        'selected_categories',
        'selected_goals',
        'status',
        'is_enrolled',
        'enrolled_at',
        'has_chosen_plan',
        'chosen_plan',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'portfolio_links' => 'array',
        'uploaded_samples' => 'array',
        'selected_types' => 'array',
        'selected_categories' => 'array',
        'selected_goals' => 'array',
        'is_enrolled' => 'boolean',
        'has_chosen_plan' => 'boolean',
        'enrolled_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function profile()
    {
        return $this->hasOne(DesignerProfile::class, 'application_id');
    }
}
