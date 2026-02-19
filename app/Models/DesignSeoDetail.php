<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignSeoDetail extends Model
{
    protected $fillable = [
        'design_submission_id',
        'meta_title',
        'meta_description',
        'slug',
        'keywords',
        'og_image',
        'is_featured',
        'is_trending',
        'priority',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_featured' => 'boolean',
        'is_trending' => 'boolean',
    ];

    public function design()
    {
        return $this->belongsTo(DesignSubmission::class, 'design_submission_id');
    }
}
