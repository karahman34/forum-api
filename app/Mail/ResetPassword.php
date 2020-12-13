<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $auth;
    public $email;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $auth, string $email)
    {
        $this->auth = $auth;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $url = route('show.reset_password') . "?email={$this->email}&token={$token}";

        // Insert token & email
        DB::table('reset_passwords')
                ->updateOrInsert(
                    ['email' => $this->email],
                    ['token' => $token, 'created_at' => Carbon::now()]
                );
        
        return $this->markdown('emails.auth.reset_password')
                        ->subject('Reset Password')
                        ->with([
                            'token' => $token,
                            'url' => $url
                        ]);
    }
}
