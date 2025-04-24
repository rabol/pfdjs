<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OverlayPosition extends Model
{
    protected $fillable = ['user_doc_id', 'top', 'left', 'width', 'height', 'image_url', 'page'];
}
