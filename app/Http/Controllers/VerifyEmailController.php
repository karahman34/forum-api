<?php

namespace App\Http\Controllers;

use App\Helpers\Transformer;
use App\Jobs\SendVerifyEmailJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerifyEmailController extends Controller
{
    public function sendVerifyEmail()
    {
        try {
            $auth = Auth::user();
            if (!is_null($auth->verified_at)) {
                return Transformer::fail('User is already been verified.', null, 400);
            }

            dispatch(new SendVerifyEmailJob(Auth::user()));
            
            return Transformer::ok('Success to send verify email.');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to send verify email.');
        }
    }

    public function verifyEmail(Request $request)
    {
        $this->validate($request, [
            'userId' => 'required|string',
            'token' => 'required|string'
        ]);

        try {
            $userId = $request->get('userId');
            $token = $request->get('token');

            $match = DB::table('verify_emails')
                        ->where('token', $token)
                        ->where('user_id', $userId)
                        ->count();

            if ($match < 1) {
                return Transformer::fail('Token is not valid.');
            }

            $user = User::select('id', 'verified_at')->whereId($userId)->firstOrFail();

            if (!is_null($user->verified_at)) {
                return Transformer::fail('User is already been verified.', null, 400);
            }

            $user->update([
                'verified_at' => Carbon::now()
            ]);

            return view('verify_email_feedback', [
                'success' => true
            ]);
        } catch (ModelNotFoundException $th) {
            return Transformer::modelNotFound('User');
        } catch (\Throwable $th) {
            return view('verify_email_feedback', [
                'success' => false
            ]);
        }
    }
}
