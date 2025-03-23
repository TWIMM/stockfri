<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use App\Services\EmailService;
use Carbon\Carbon;

class RegisterController extends Controller
{
    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    public function showRegistrationForm()
    {
        return view('auth/register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Validate the registration form
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);
        $success = $this->emailService->sendEmailWithTemplate($user->email, 'emails.user_confirm' , [
            'name' => $user->name,      
            'appLink' => env("APP_URL").env('VALIDATE_MAIL')."/".$user->id,   
        ]);
        return redirect()->route('pricing.page');
    }

    public function ValidateMail($id){
        $user = User::find($id);
        if($user->email_verified_at){

            return redirect()->route('login');
        }
        $user->email_verified_at = Carbon::now(); 
        $user->save();
        return redirect()->route('login');

    }
}
