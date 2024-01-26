<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['nullable', 'string', 'email', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:users'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => $this->passwordRules(),
            'terms'    => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return User::create([
            'name'              => $input['name'],
            'email'             => $input['email'],
            'username'          => $input['username'],
            'phone'             => $input['phone'],
            'email_verified_at' => now(),
            'password'          => Hash::make($input['password']),
        ]);
    }
}
