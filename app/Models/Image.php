<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    public static $rules = [
        'img' => 'required|mimes:png,gif,jpeg,jpg,bmp'
    ];

    public static $messages = [
        'img.mimes' => 'Uploaded file is not in allowed image formats.',
        'img.required' => 'Image is required.'
    ];
}
