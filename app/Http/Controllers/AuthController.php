<?php

namespace App\Http\Controllers;

use App\Helpers\Transformer;
use App\Http\Resources\UserResource;
use App\Jobs\SendVerifyEmailJob;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Token TTL.
     *
     * @var int
     */
    private $token_ttl = 604800; // 1 week

    /**
     * Token response structure.
     *
     * @param  string $token
     *
     * @return  array
     */
    private function respondWithToken(string $token)
    {
        return [
            'access_token' => $token,
            'type' => 'Bearer',
            'expired_in' => auth()->factory()->getTTL(),
        ];
    }

    /**
     * Create user account.
     *
     * @param   Request  $request
     *
     * @return  JsonResponse
     */
    public function register(Request $request)
    {
        $payload = $this->validate($request, [
            'username' => 'required|string|min:8|max:30|regex:/^[a-z]+([_a-z0-9]+)?$/|unique:users,username',
            'email' => 'required|string|email|min:8|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:255|confirmed',
        ]);

        try {
            // Hash password
            $payload['password'] = app('hash')->make($payload['password']);

            // Crete user
            $user = User::create($payload);

            // Login user
            $token = Auth::login($user);

            // Send verify email
            dispatch(new SendVerifyEmailJob($user));

            return Transformer::ok(
                'Success to create user.',
                array_merge(
                    $this->respondWithToken($token),
                    ['user' => new UserResource(auth()->user())]
                ),
                201
            );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to create user.');
        }
    }

    /**
     * Login user.
     *
     * @param  Request  $request
     *
     * @return  JsonResponse
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email_or_username' => 'required|string',
            'password' => 'required|string',
        ]);

        $payload = $request->only('password');
        $email_or_username = $request->get('email_or_username');

        filter_var($email_or_username, FILTER_VALIDATE_EMAIL)
            ? $payload['email'] = $email_or_username
            : $payload['username'] = $email_or_username;

        try {
            $token = Auth::setTTL($this->token_ttl)->attempt($payload);

            if (!$token) {
                return Transformer::fail('Invalid login credentials.', null, 401);
            }

            return Transformer::ok(
                'Success to authenticated user.',
                array_merge(
                    $this->respondWithToken($token),
                    ['user' => new UserResource(auth()->user())]
                ),
                200
            );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to authenticated user.');
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return Transformer::ok(
            'Success to get user details.',
            [
                'user' => new UserResource(auth()->user()),
            ],
            200
        );
    }

    /**
     * Refresh user token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return Transformer::ok(
            'Success to refresh token.',
            $this->respondWithToken(auth()->setTTL($this->token_ttl)->refresh())
        );
    }

    /**
     * Logout user.
     *
     * @return JsonResponse
     */
    public function logout()
    {
        try {
            Auth::logout();

            return Transformer::ok('Success to logged out user.');
        } catch (\Throwable $th) {
            return Transformer::ok('Failed to logged out user.');
        }
    }
}
