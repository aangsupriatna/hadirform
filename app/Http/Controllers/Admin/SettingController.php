<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $organization = $user->organization;

        return view('admin.settings.index', compact('organization', 'user'));
    }

    public function updateOrganization(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'admin') {
            abort(403, 'Hanya Administrator yang dapat mengubah pengaturan organisasi.');
        }

        $org = $user->organization;

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('organizations', 'slug')->ignore($org->id)],
            'brand_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'address' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:150',
            'timezone' => ['required', 'string', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])],
            'receipt_prefix' => 'nullable|string|max:10',
            'receipt_footer' => 'nullable|string|max:500',
            'require_operator_signature' => 'nullable|boolean',
        ]);

        $before = $org->toArray();

        // Handle logo upload
        $logoPath = $org->logo_path;
        if ($request->hasFile('logo')) {
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        // Merge settings_json
        $settings = $org->settings_json ?? [];
        $settings['address'] = $validated['address'] ?? null;
        $settings['email'] = $validated['email'] ?? null;
        $settings['phone'] = $validated['phone'] ?? null;
        $settings['website'] = $validated['website'] ?? null;
        $settings['timezone'] = $validated['timezone'];
        $settings['receipt_prefix'] = $validated['receipt_prefix'] ?? 'HF-';
        $settings['receipt_footer'] = $validated['receipt_footer'] ?? null;
        $settings['require_operator_signature'] = $request->boolean('require_operator_signature');

        $org->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'brand_color' => $validated['brand_color'] ?? '#4f46e5',
            'logo_path' => $logoPath,
            'settings_json' => $settings,
        ]);

        AuditLog::log('organization.update', $org, $before, $org->toArray(), $org->id);

        return back()->with('success', 'Pengaturan organisasi berhasil diperbarui.');
    }

    public function removeLogo(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'admin') {
            abort(403);
        }

        $org = $user->organization;
        if ($org->logo_path && Storage::disk('public')->exists($org->logo_path)) {
            Storage::disk('public')->delete($org->logo_path);
        }

        $org->update(['logo_path' => null]);
        AuditLog::log('organization.logo_remove', $org, null, null, $org->id);

        return back()->with('success', 'Logo organisasi berhasil dihapus.');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        AuditLog::log('user.profile_update', $user, null, null, $user->organization_id);

        return back()->with('success', 'Profil akun Anda berhasil diperbarui.');
    }
}
