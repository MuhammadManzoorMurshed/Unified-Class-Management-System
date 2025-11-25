<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Classes;

class ClassPolicy
{
    /** শুধুমাত্র Admin বা Teacher ক্লাস তৈরি করতে পারবে */
    public function create(User $user)
    {
        return in_array($user->role->role_name, ['Admin', 'Teacher']);
    }

    /** Admin বা নিজের ক্লাসের Teacher ম্যানেজ করতে পারবে */
    public function manage(User $user, Classes $class)
    {
        return $user->id === $class->teacher_id
            || $user->role->role_name === 'Admin';
    }

    /** Update → Teacher (নিজের) বা Admin */
    public function update(User $user, Classes $class)
    {
        return $this->manage($user, $class);
    }

    /** Delete → শুধুমাত্র Admin */
    public function delete(User $user, Classes $class)
    {
        return $user->role->role_name === 'Admin';
    }
}