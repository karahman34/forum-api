<?php

namespace App\Http\Controllers;

use App\Helpers\Transformer;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Update user profile.
     *
     * @param   Request  $request
     *
     * @return  JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $this->validate($request, [
            'avatar' => 'present|nullable|mimes:jpg,jpeg,png|max:4096',
            'username' => 'required|string|min:8|max:30|regex:/^[a-z]+([_a-z0-9]+)?$/|unique:users,username,' . $user->id,
        ]);

        try {
            $payload = $request->only('username');

            // Avatar
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $file_name = uniqid(rand()) . '.' . $avatar->getClientOriginalExtension();

                $destination = base_path('public/' . User::$avatar_folder);

                // Store avatar
                $avatar->move($destination, $file_name);
                $payload['avatar'] = User::$avatar_folder . '/' . $file_name;

                // Remove old avatar
                if (!is_null($user->avatar)) {
                    unlink(base_path('public/' . $user->avatar));
                }
            }

            // Update user
            $user->update($payload);

            return Transformer::ok('Success to update user.', new UserResource($user));
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update user.');
        }
    }

    /**
     * Update user password.
     *
     * @param   Request  $request
     *
     * @return  JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required|string',
            'password' => 'required|string|min:8|max:255|confirmed',
        ]);

        try {
            // Get user
            $user = Auth::user();

            // Check old password
            if (!app('hash')->check($request->get('old_password'), $user->password)) {
                return Transformer::fail('The old password doesn\'t match.', null, 401);
            }

            $user->update([
                'password' => app('hash')->make($request->get('password'))
            ]);

            return Transformer::ok('Success to update user\'s password.');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update user\'s password.');
        }
    }
}
