<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // This line tells Laravel it is safe to accept the 'name' field from a form
    protected $fillable = ['name'];
}