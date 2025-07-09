<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenancy;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TenantAuthController extends Controller
{
    use ApiResponses;
    public function login(Request $request)
    {
        $request->validate([
            'tenant_number'    => 'required|string',
            'password' => 'required|string',
        ]);

        $tenantUser = Tenancy::where('tenant_number', $request->tenant_number)->first();

        if (! $tenantUser || ! Hash::check($request->password, $tenantUser->password)) {
            return $this->error('Invalid credentials', 401);
        }

        $token = $tenantUser->createToken('tenant-api-token')->plainTextToken;

        return $this->ok('Logged in successfully', [
            'token'   => $token,
            'user'    => $tenantUser,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->ok('Logged out successfully');
    }
}
