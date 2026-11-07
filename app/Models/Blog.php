<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = ['meta_keywords' => 'array'];

    public function details()
    {
        return $this->hasOne(BlogDetails::class, 'blog_id');
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class);
    }

    public function getMetaRobots()
    {
        return explode(",", $this->meta_robots);
    }

    public function getStatus(): string
    {
        if ($this->status == 0) {
            return '<span class="badge bg-soft-danger text-danger">
                        <span class="legend-indicator bg-danger"></span>' . trans('Inactive') . '
                    </span>';

        } elseif ($this->status == 1) {
            return '<span class="badge bg-soft-success text-success">
                        <span class="legend-indicator bg-success"></span>' . trans('Active') . '
                    </span>';

        } else {
            return 'Unknown';
        }
    }

    public function detailsLink(): string
    {
        return $this->details?->slug ? route('blog.details',$this->details?->slug) : '#';
    }

    public function titleLimit($limit = null): string
    {
        if ($limit) {
            return  Str::limit($this->details?->title, $limit);
        }
        else{
            return $this->details?->title;
        }
    }

    public function descriptionLimit($limit = null): string
    {
        if ($limit) {
            return  Str::limit($this->details?->description, $limit);
        }
        else{
            return $this->details?->description;
        }
    }



}
