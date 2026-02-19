<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignSubmission extends Model
{
    protected $fillable = [
        'designer_id',
        'title',
        'description',
        'category',
        'category_id',
        'design_file_path',
        'preview_images',
        'tags',
        'status',
        'designer_head_notes',
        'seo_head_notes',
        'designer_head_reviewed_by',
        'designer_head_reviewed_at',
        'seo_head_reviewed_by',
        'seo_head_reviewed_at',
        'published_at',
        'total_sales',
        'total_revenue',
    ];

    protected $casts = [
        'preview_images' => 'array',
        'tags' => 'array',
        'designer_head_reviewed_at' => 'datetime',
        'seo_head_reviewed_at' => 'datetime',
        'published_at' => 'datetime',
        'total_revenue' => 'decimal:2',
    ];

    public function designer()
    {
        return $this->belongsTo(DesignerProfile::class, 'designer_id');
    }

    public function designerHeadReviewer()
    {
        return $this->belongsTo(User::class, 'designer_head_reviewed_by');
    }

    public function seoHeadReviewer()
    {
        return $this->belongsTo(User::class, 'seo_head_reviewed_by');
    }

    public function seoDetails()
    {
        return $this->hasOne(DesignSeoDetail::class, 'design_submission_id');
    }

    public function sales()
    {
        return $this->hasMany(DesignSale::class, 'design_submission_id');
    }
}
