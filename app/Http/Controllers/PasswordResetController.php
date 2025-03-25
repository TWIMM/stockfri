<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function showResetForm()
    {
        return view('auth.modify_password');
    }

    public function showResetConfirmForm(Request $request, $token = null)
    {
        // Extract email from query string
        $email = $request->query('email');

        // Find the token record in the password_reset_tokens table
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        // Check if token exists
        if (!$resetRecord) {
            return back()->with('Invalide token !');
        }

        // Check token expiration (typically 60 minutes)
        $tokenCreatedAt = Carbon::parse($resetRecord->created_at);
        if ($tokenCreatedAt->diffInMinutes(now()) > 60) {
            // Delete expired token
            DB::table('password_reset_tokens')
                ->where('token', $token)
                ->delete();

            return back()->with('Token expire');
        }

        // Return view with token and email
        return view('auth.confirm', [
            'token' => $token,
            'email' => $email
        ]);
    }

    public function resetPassword(Request $request)
    {
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Check if reset token is valid
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
                return back()->withErrors(['token' => 'Invalid or expired reset token']);
            }

            // Check token expiration (15 minutes)
            $tokenCreatedAt = Carbon::parse($resetRecord->created_at);
            if ($tokenCreatedAt->diffInMinutes(now()) > 15) {
                DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->delete();

                return back()->withErrors(['token' => 'Reset token has expired']);
            }

            // Update user password
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete used reset token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // Log password reset
            Log::info('Password reset successful', [
                'email' => $request->email,
                'ip' => $request->ip()
            ]);

            // Redirect to login with success message
            return redirect()->route('login')
                ->with('status', 'Your password has been reset successfully!');

        } catch (\Exception $e) {
            // Log any unexpected errors
            Log::error('Password reset failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'An unexpected error occurred']);
        }
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'No account found with this email address.'
        ]);
    
        $token = Str::random(60);
    
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );
    
        // Modified app link to include both token and email
        $appLink = env("APP_URL") . env('RESET_PASSWORD') . "/{$token}?email=" . urlencode($request->email);
        
        $success = $this->emailService->sendEmailWithTemplate($request->email, 'emails.password-reset' , [
           'token' => $token, 
           'email' => $request->email, 
           'appLink' => $appLink,   
        ]);
    
        return back()->with('success', 'Vérifiez vos mails!');
    }
}