<?php

namespace App\Http\Controllers;

use App\Helpers\Transformer;
use App\Mail\ResetPassword;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    public function view(Request $request)
    {
        $payload = $this->validate($request, [
            'token' => 'required|string',
            'email' => 'required|string|email',
        ]);

        return view('reset-password', $payload);
    }

    /**
     * Send password reset email.
     *
     * @param   Request  $request
     *
     * @return  JsonResponse
     */
    public function sendResetEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255'
        ]);

        try {
            $email = $request->get('email');
            $auth = User::where('email', $email)->firstOrFail();

            // Check verif
            if (is_null($auth->verified_at)) {
                return Transformer::fail('Email is not verified.', null, 403);
            }

            Mail::to($auth)->send(new ResetPassword($auth, $email));

            return Transformer::ok('Success to send reset password mail.');
        } catch (ModelNotFoundException $th) {
            return Transformer::fail('Email not found.', null, 404);
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to send password reset.');
        }
    }

    /**
     * Reset user password.
     *
     * @param   Request  $request
     *
     * @return  JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $payload = $this->validate($request, [
            'token' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|max:255|confirmed',
        ]);

        try {
            // Check token.
            $match = DB::table('reset_passwords')
                            ->where('email', $payload['email'])
                            ->where('token', $payload['token'])
                            ->first();
            if (!$match) {
                return Transformer::fail('Invalid token.', null, 400);
            }

            // Check token expire date.
            $now = Carbon::now();
            $token_expire_date = Carbon::parse($match->created_at)->addHour();
            if ($now > $token_expire_date) {
                return Transformer::fail('Token is already expired, please make a new request.', null, 401);
            }

            // Get auth user & update password.
            $user = User::where('email', $payload['email'])->firstOrFail();
            $user->update([
                'password' => app('hash')->make($payload['password']),
            ]);

            // Delete token.
            DB::table('reset_passwords')
                            ->where('email', $payload['email'])
                            ->where('token', $payload['token'])
                            ->delete();

            return Transformer::ok('Success to reset user\'s password');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to reset user\'s password');
        }
    }
}
