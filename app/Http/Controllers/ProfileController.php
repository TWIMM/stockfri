<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    //
    public function updateGeneralProfile(Request $request)
    {
        $user = auth()->user();

        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'tel' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            //'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:800', // Validate the image
        ]);

        // Update user profile data
        $user->update($request->only('name', 'tel', 'email', 'address', 'country', 'city'));

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($user->profile_image && Storage::exists('public/profiles/' . $user->profile_image)) {
                Storage::delete('public/profiles/' . $user->profile_image);
            }

            // Store the new image
            $imagePath = $request->file('profile_image')->store('profiles', 'public');

            // Update user profile image in database
            $user->profile_image = basename($imagePath); // Save only the file name in the database
            $user->save();
        }

        if ($request->hasFile('invoice_logo')) {
            // Delete old image if exists
            if ($user->invoice_logo && Storage::exists('public/invoice_logo/' . $user->invoice_logo)) {
                Storage::delete('public/invoice_logo/' . $user->invoice_logo);
            }

            // Store the new image
            $imagePath = $request->file('invoice_logo')->store('invoice_logo', 'public');

            // Update user profile image in database
            $user->invoice_logo = basename($imagePath); // Save only the file name in the database
            $user->save();
        }

        return redirect()->route('profile.page')->with('success', 'Profile updated successfully!');
    }

}
