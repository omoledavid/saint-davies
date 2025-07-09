<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\PasswordReset;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use ApiResponses;
    public function register(RegisterRequest $request)
    {
        $uploadedImagePath = null;

        try {
            // Start database transaction
            DB::beginTransaction();

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'phone' => $request->phone,
                'location' => $request->location,
                'role' => $request->role,
                'status' => UserStatus::ACTIVE,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                'country' => $request->country,
                'website' => $request->website,
                'facebook' => $request->facebook,
                'instagram' => $request->instagram,
                'twitter' => $request->twitter,
                'linkedin' => $request->linkedin,
            ];

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('users/images', $imageName, 'public');
                $userData['image'] = $imagePath;
                $uploadedImagePath = $imagePath; // Store for potential cleanup
            }

            $user = User::create($userData);

            // Generate email verification token
            $user->generateEmailVerificationToken();

            // Send verification email
            // Mail::to($user->email)->send(new EmailVerificationMail($user));

            // Commit transaction
            DB::commit();

            return $this->ok('User registered successfully. Please check your email to verify your account.', UserResource::make($user), 201);
        } catch (\Exception $e) {
            // Rollback database transaction
            DB::rollBack();

            // Clean up uploaded file if it exists
            if ($uploadedImagePath && Storage::disk('public')->exists($uploadedImagePath)) {
                Storage::disk('public')->delete($uploadedImagePath);
            }

            return $this->error('Registration failed', 500, $e->getMessage());
        }
    }
    public function verifyEmail($token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return $this->error('Invalid verification token', 400);
        }

        if (!$user->isEmailVerificationTokenValid($token)) {
            return $this->error('Verification token has expired', 400);
        }

        $user->markEmailAsVerified();

        return $this->ok('Email verified successfully');
    }
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->error('Invalid credentials', 401);
        }

        $user = Auth::user();

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            return $this->error('Please verify your email address before logging in.', 403);
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->ok('Login successful', [
            'user' => UserResource::make($user),
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return $this->error('Email already verified', 400);
        }

        // Generate new verification token
        $user->generateEmailVerificationToken();

        // Send verification email
        // Mail::to($user->email)->send(new EmailVerificationMail($user));

        return $this->ok('Verification email sent successfully');
    }
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $email = $request->email;

            // Create password reset token
            $token = PasswordReset::createToken($email);

            // Send password reset email
            // Mail::to($email)->send(new PasswordResetMail($token, $email));

            return $this->ok('Password reset email sent successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to send password reset email', 500, $e->getMessage());
        }
    }
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $passwordReset = PasswordReset::where('email', $request->email)
                ->where('token', $request->token)
                ->first();

            if (!$passwordReset) {
                return $this->error('Invalid reset token', 400);
            }

            if ($passwordReset->isExpired()) {
                return $this->error('Reset token has expired', 400);
            }

            // Update user password
            $user = User::where('email', $request->email)->first();
            $user->password = $request->password;
            $user->save();

            // Delete the reset token
            $passwordReset->delete();

            return $this->ok('Password reset successfully');
        } catch (\Exception $e) {
            return $this->error('Password reset failed', 500, $e->getMessage());
        }
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->ok('Logged out successfully');
    }
}
