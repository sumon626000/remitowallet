<?php

namespace App\Traits;


trait ContentDelete
{
    public static function booted()
    {
        static::deleting(function ($model) {
            if (isset($model->contentMedia->description->image)) {
				file_exists(config('filelocation.content.path') . '/' . $model->contentMedia->description->image) && is_file(config('filelocation.content.path') . '/' . $model->contentMedia->description->image) ? @unlink(config('filelocation.content.path') . '/' . $model->contentMedia->description->image) : '';
            };
            $model->contentMedia()->delete();
            $model->contentDetails()->delete();
        });
    }
}
