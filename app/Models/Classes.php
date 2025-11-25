<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enrollment;

class Classes extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        // API uses 'title' but DB column is 'name' — normalize via accessors/mutators.
        'name',
        'description',
        'teacher_id',
        'code',
        'subject',
        'semester',
        'year',
        'is_active',
        'max_students',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'year' => 'integer',
        'max_students' => 'integer',
    ];

    // Accessor for API: $class->title reads the DB 'name' column
    public function getTitleAttribute()
    {
        return $this->attributes['name'] ?? null;
    }

    // Mutator for API: setting 'title' will write to DB 'name' column
    public function setTitleAttribute($value)
    {
        $this->attributes['name'] = $value;
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    public function activeEnrollments()
    {
        return $this->enrollments()->where('status', 'active');
    }
}