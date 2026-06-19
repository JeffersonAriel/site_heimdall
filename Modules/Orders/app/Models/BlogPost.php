<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use SoftDeletes;

    protected $table = 'blog_posts';

    protected $fillable = ['title', 'slug', 'content', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
