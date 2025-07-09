<?php

// =============================================================================
// 1. MIGRATION FILES
// =============================================================================

// Create migration: php artisan make:migration create_users_table
// database/migrations/xxxx_xx_xx_create_users_table.php
class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('email_verification_token')->nullable();
            $table->timestamp('email_verification_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}

// Create migration: php artisan make:migration create_password_resets_table
// database/migrations/xxxx_xx_xx_create_password_resets_table.php
class CreatePasswordResetsTable extends Migration
{
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at');
            $table->timestamp('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
}

// Create migration: php artisan make:migration create_personal_access_tokens_table
// database/migrations/xxxx_xx_xx_create_personal_access_tokens_table.php
class CreatePersonalAccessTokensTable extends Migration
{
    public function up()
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('personal_access_tokens');
    }
}

// =============================================================================
// 2. MODELS
// =============================================================================

// app/Models/User.php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verification_token',
        'email_verification_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_expires_at' => 'datetime',
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    public function markEmailAsVerified()
    {
        $this->email_verified_at = now();
        $this->email_verification_token = null;
        $this->email_verification_expires_at = null;
        $this->save();
    }

    public function generateEmailVerificationToken()
    {
        $this->email_verification_token = bin2hex(random_bytes(32));
        $this->email_verification_expires_at = now()->addHours(24);
        $this->save();
    }

    public function isEmailVerificationTokenValid($token)
    {
        return $this->email_verification_token === $token && 
               $this->email_verification_expires_at > now();
    }
}

// app/Models/PasswordReset.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'created_at',
        'expires_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public $timestamps = false;

    public function isExpired()
    {
        return $this->expires_at < now();
    }

    public static function createToken($email)
    {
        $token = bin2hex(random_bytes(32));
        
        // Delete any existing tokens for this email
        self::where('email', $email)->delete();
        
        // Create new token
        self::create([
            'email' => $email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => now()->addHours(1), // Token expires in 1 hour
        ]);
        
        return $token;
    }
}

// =============================================================================
// 3. REQUESTS (VALIDATION)
// =============================================================================

// app/Http/Requests/RegisterRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}

// app/Http/Requests/LoginRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];
    }
}

// app/Http/Requests/ForgotPasswordRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|string|email|exists:users,email',
        ];
    }
}

// app/Http/Requests/ResetPasswordRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'token' => 'required|string',
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}

// =============================================================================
// 4. MAIL CLASSES
// =============================================================================

// app/Mail/EmailVerificationMail.php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verificationUrl;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->verificationUrl = url('/api/auth/verify-email/' . $user->email_verification_token);
    }

    public function build()
    {
        return $this->subject('Verify Your Email Address')
                    ->view('emails.verify-email');
    }
}

// app/Mail/PasswordResetMail.php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;
    public $resetUrl;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
        $this->resetUrl = url('/reset-password?token=' . $token . '&email=' . $email);
    }

    public function build()
    {
        return $this->subject('Password Reset Request')
                    ->view('emails.password-reset');
    }
}

// =============================================================================
// 5. CONTROLLER
// =============================================================================

// app/Http/Controllers/AuthController.php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Models\PasswordReset;
use App\Mail\EmailVerificationMail;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            // Generate email verification token
            $user->generateEmailVerificationToken();

            // Send verification email
            Mail::to($user->email)->send(new EmailVerificationMail($user));

            return response()->json([
                'message' => 'User registered successfully. Please check your email to verify your account.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email address before logging in.',
                'email_verified' => false
            ], 403);
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }

    /**
     * Verify email address
     */
    public function verifyEmail($token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid verification token'
            ], 400);
        }

        if (!$user->isEmailVerificationTokenValid($token)) {
            return response()->json([
                'message' => 'Verification token has expired'
            ], 400);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'message' => 'Email verified successfully'
        ], 200);
    }

    /**
     * Resend email verification
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ], 400);
        }

        // Generate new verification token
        $user->generateEmailVerificationToken();

        // Send verification email
        Mail::to($user->email)->send(new EmailVerificationMail($user));

        return response()->json([
            'message' => 'Verification email sent successfully'
        ], 200);
    }

    /**
     * Forgot password
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $email = $request->email;
            
            // Create password reset token
            $token = PasswordReset::createToken($email);

            // Send password reset email
            Mail::to($email)->send(new PasswordResetMail($token, $email));

            return response()->json([
                'message' => 'Password reset email sent successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send password reset email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $passwordReset = PasswordReset::where('email', $request->email)
                                        ->where('token', $request->token)
                                        ->first();

            if (!$passwordReset) {
                return response()->json([
                    'message' => 'Invalid reset token'
                ], 400);
            }

            if ($passwordReset->isExpired()) {
                return response()->json([
                    'message' => 'Reset token has expired'
                ], 400);
            }

            // Update user password
            $user = User::where('email', $request->email)->first();
            $user->password = $request->password;
            $user->save();

            // Delete the reset token
            $passwordReset->delete();

            return response()->json([
                'message' => 'Password reset successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Password reset failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'email_verified_at' => $request->user()->email_verified_at,
            ]
        ], 200);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request)
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices successfully'
        ], 200);
    }
}

// =============================================================================
// 6. ROUTES
// =============================================================================

// routes/api.php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('verify-email/{token}', [AuthController::class, 'verifyEmail']);
    Route::post('resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('logout-all', [AuthController::class, 'logoutAll']);
});

// =============================================================================
// 7. EMAIL TEMPLATES
// =============================================================================

// resources/views/emails/verify-email.blade.php
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Your Email</title>
</head>
<body>
    <h1>Hello {{ $user->name }}!</h1>
    
    <p>Thank you for registering with us. Please click the button below to verify your email address:</p>
    
    <a href="{{ $verificationUrl }}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
        Verify Email Address
    </a>
    
    <p>If you cannot click the button, copy and paste the following URL into your browser:</p>
    <p>{{ $verificationUrl }}</p>
    
    <p>This verification link will expire in 24 hours.</p>
    
    <p>If you did not create an account, no further action is required.</p>
    
    <p>Regards,<br>{{ config('app.name') }}</p>
</body>
</html>

// resources/views/emails/password-reset.blade.php
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Your Password</title>
</head>
<body>
    <h1>Password Reset Request</h1>
    
    <p>You are receiving this email because we received a password reset request for your account.</p>
    
    <a href="{{ $resetUrl }}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
        Reset Password
    </a>
    
    <p>If you cannot click the button, copy and paste the following URL into your browser:</p>
    <p>{{ $resetUrl }}</p>
    
    <p>This password reset link will expire in 60 minutes.</p>
    
    <p>If you did not request a password reset, no further action is required.</p>
    
    <p>Regards,<br>{{ config('app.name') }}</p>
</body>
</html>

// =============================================================================
// 8. CONFIGURATION
// =============================================================================

// Add to config/sanctum.php
'expiration' => 60 * 24, // 24 hours

// Add to .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

// =============================================================================
// 9. INSTALLATION COMMANDS
// =============================================================================

/*
1. Install Laravel Sanctum:
   composer require laravel/sanctum

2. Publish Sanctum configuration:
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

3. Run migrations:
   php artisan migrate

4. Create the request classes:
   php artisan make:request RegisterRequest
   php artisan make:request LoginRequest
   php artisan make:request ForgotPasswordRequest
   php artisan make:request ResetPasswordRequest

5. Create the mail classes:
   php artisan make:mail EmailVerificationMail
   php artisan make:mail PasswordResetMail

6. Create the controller:
   php artisan make:controller AuthController

7. Add Sanctum middleware to app/Http/Kernel.php in the 'api' middleware group:
   \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
*/