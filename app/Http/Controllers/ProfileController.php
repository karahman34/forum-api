<?php

namespace App\Http\Controllers;

use App\Helpers\Transformer;
use App\Http\Resources\PostsCollection;
use App\Http\Resources\UserPostsCollection;
use App\Http\Resources\UserResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Get User info.
     *
     * @param   string  $username
     *
     * @return  JsonResponse
     */
    public function getUser(string $username)
    {
        try {
            $user = User::select('id', 'avatar', 'username', 'bio')->whereUsername($username)->firstOrFail();

            return Transformer::ok('Success to get user info.', [
                'user' => $user
            ]);
        } catch (ModelNotFoundException $th) {
            return Transformer::modelNotFound('User');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get user\'s info.');
        }
    }

    /**
     * Get user\'s posts collection.
     *
     * @param   Request  $request
     * @param   string   $username
     *
     * @return  JsonResponse
     */
    public function getUserPosts(Request $request, $username)
    {
        try {
            $user = User::select('id')
                            ->where('username', $username)
                            ->firstOrFail();

            $posts = Post::select('id', 'user_id', 'title', 'solved', 'views', 'created_at', 'updated_at')
                            ->with(['author:id,avatar,username', 'screenshots', 'tags:post_id,name'])
                            ->withCount('comments')
                            ->where('user_id', $user->id)
                            ->paginate($request->get('limit', 10));

            return (new PostsCollection($posts))
                    ->additional(
                        Transformer::meta(true, 'Success to get user\'s posts.')
                    );
        } catch (ModelNotFoundException $th) {
            return Transformer::modelNotFound('User');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get user\'s posts.');
        }
    }

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
            'email' => 'nullable|string|email|max:255',
            'avatar' => 'nullable|mimes:jpg,jpeg,png|max:4096',
            'username' => 'required|string|min:8|max:30|regex:/^[a-z]+([_a-z0-9]+)?$/|unique:users,username,' . $user->id,
            'bio' => 'nullable|string'
        ]);

        try {
            $payload = $request->only(['username', 'bio']);

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

            // Email
            if ($request->has('email') && !is_null($request->get('email'))) {
                if (is_null($user->verified_at)) {
                    return Transformer::fail('You have to verify your email address first.', null, 403);
                }

                $payload['email'] = $request->get('email');
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
