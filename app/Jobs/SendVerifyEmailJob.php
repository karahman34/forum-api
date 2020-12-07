<?php

namespace App\Jobs;

use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendVerifyEmailJob extends Job implements ShouldQueue
{
    /**
     * User model.
     *
     * @var User
     */
    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Create verification email token.
     *
     * @return  string
     */
    private function createToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $token = $this->createToken();
        $url = route('verify_email') . "?token={$token}&userId={$this->user->id}";

        DB::table('verify_emails')
                ->updateOrInsert(
                    ['user_id' => $this->user->id],
                    ['token' => $token],
                );

        Mail::to($this->user)
                ->send(new VerifyEmail($url));
    }
}
