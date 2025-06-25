<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index(){
        $user = User::where('id',6)->first();
        return view("pages.profile",compact('user'));
    }
    public function editProfile(Request $request)
    {
        $validated = $request->validate([
            'avatar_url' => 'required|image|max:1024|mimes:jpg,jpeg,png',
            'username' => 'required|max:255',
            'bio' => 'max:255',
        ]);

        $user = User::find(6); // Replace with Auth::user() or Auth::id() in real use

    $data = $validated;

    if ($request->hasFile('avatar_url')) {
        // Delete old avatar if exists
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        // Save new image
        $filePath = $request->file('avatar_url')->store('images/users/avatar', 'public');
        $data['avatar_url'] = $filePath;
    }

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }
}
