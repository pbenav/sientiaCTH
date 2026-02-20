<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    public function update($user, array $input)
    {
        // Normalizar campos opcionales: convertir cadenas vacías a null
        if (empty($input['family_name2'])) {
            $input['family_name2'] = null;
        }
        
        // Si locale está vacío, usar el valor actual del usuario o 'es' por defecto
        if (empty($input['locale'])) {
            $input['locale'] = $user->locale ?? 'es';
        }

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'family_name1' => ['required', 'string', 'max:255'],
            'family_name2' => ['nullable', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'user_code' => ['required', 'max:10', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'locale' => ['required', 'string', 'in:es,en'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'family_name1' => $input['family_name1'],
                'family_name2' => $input['family_name2'] ?? null,
                'dni' => $input['dni'] ?? null,
                'email' => $input['email'],
                'user_code' => $input['user_code'],
                'locale' => $input['locale'] ?? 'es',
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    protected function updateVerifiedUser($user, array $input)
    {
        $user->forceFill([
            'name' => $input['name'],
            'family_name1' => $input['family_name1'],
            'family_name2' => $input['family_name2'] ?? null,
            'dni' => $input['dni'] ?? null,
            'email' => $input['email'],
            'user_code' => $input['user_code'],
            'locale' => $input['locale'] ?? 'es',
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
