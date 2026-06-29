<?php

namespace App\Http\Controllers;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ProfileController extends Controller
{
    use ProfileValidationRules;

    public function show()
    {
        $user = auth()->user();

        abort_if($user?->isAdmin(), 403);

        return view('profile.show', compact('user'));
    }

    public function edit()
    {
        $user = auth()->user();

        abort_if($user?->isAdmin(), 403);

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        abort_if($user?->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'nip' => ['nullable', 'string', 'max:30'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'profile_photo' => ['nullable', File::image()->max(2048)],
        ]);

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'nip' => $data['nip'] ?? $user->nip,
            'no_hp' => $data['no_hp'] ?? $user->no_hp,
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $updateData['profile_photo_path'] = $path;
        }

        $user->update($updateData);

        return redirect()->route('profile.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
