<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\ApiToken;

class SettingsController extends Controller
{
    /**
     * Display settings page.
     */
    public function index()
    {
        $user = Auth::user();
        $tokens = ApiToken::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        return view('settings.index', compact('user', 'tokens'));
    }

    /**
     * Update admin password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    /**
     * Generate an administrative API token.
     */
    public function generateApiToken(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $plainToken = 'adm_' . Str::random(40);

        ApiToken::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'token' => hash('sha256', $plainToken),
        ]);

        return back()->with('success', 'New Admin API Token created successfully!')->with('plain_token', $plainToken);
    }

    /**
     * Revoke / delete an API token.
     */
    public function deleteApiToken($id)
    {
        $token = ApiToken::where('user_id', Auth::id())->findOrFail($id);
        $token->delete();

        return back()->with('success', 'Admin API Token successfully revoked.');
    }
}
