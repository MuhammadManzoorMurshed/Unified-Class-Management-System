<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    // Database uses `role_name` column; keep compatibility with `name` if used elsewhere.
    protected $fillable = [
        'role_name',
        'name',
        'guard_name',
    ];

    // If other code expects $role->role_name or $role->name, normalize both via accessors.
    public function getRoleNameAttribute()
    {
        // prefer explicit role_name column, fallback to name
        return $this->attributes['role_name'] ?? $this->attributes['name'] ?? null;
    }

    public function getNameAttribute()
    {
        return $this->attributes['name'] ?? $this->attributes['role_name'] ?? null;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
