<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the groups that have this permission.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_permission');
    }
}
