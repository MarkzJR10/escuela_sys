<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['text', 'url', 'icon'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'menu_role');
    }
}
