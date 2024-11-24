<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    /** @use HasFactory<\Database\Factories\UrlFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    //relacion uno s muchos inversa con la tabla users
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
