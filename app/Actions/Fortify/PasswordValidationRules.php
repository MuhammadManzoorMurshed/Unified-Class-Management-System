<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Password validation rules used for registration and password update.
     *
     * @return array
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols(), 'confirmed'];
    }
}
