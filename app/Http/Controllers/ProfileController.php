<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\ProfileUploadRequest;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        $this->authorize('update', $user);

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     *
     * @param  \App\Http\Requests\ProfileUpdateRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();
        $this->authorize('update', $user);

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        $this->authorize('delete', $user);

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Upload users avatar
     *
     * @param App\Http\Requests\ProfileUploadRequest $request
     * @return void
     */
    public function upload(ProfileUploadRequest $request)
    {
        $user = $request->user();
        $this->authorize('update', $user);

        $filename = '';
        if($request->hasFile('avatar')){
            $user->deleteAvatar();
            
            $filename = $user->saveAvatar($request->file('avatar')); 
        }
        
        if ($filename) {
            $user->fill(['avatar'=>$filename]);
        }
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'photo-updated');
    }
}
