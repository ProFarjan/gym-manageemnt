<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Plain text/select settings this form manages. Checkboxes and the logo
     * file are handled separately in update() since they need different
     * request handling (boolean presence, file upload).
     */
    private const TEXT_KEYS = [
        'business_name', 'business_tagline', 'business_address', 'business_phone',
        'membership_prefix', 'gym_closing_time',
        'sms_driver', 'sms_api_key', 'sms_sender_id',
        'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_from_address', 'mail_from_name',
        'bkash_app_key', 'bkash_app_secret', 'bkash_username', 'bkash_password',
        'nagad_merchant_id', 'nagad_merchant_key',
        'zkteco_ip', 'zkteco_port', 'zkteco_device_id',
        'invoice_footer',
    ];

    private const CHECKBOX_KEYS = ['bkash_sandbox', 'nagad_sandbox'];

    public function edit()
    {
        $settings = Setting::cached();

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg', 'max:1024'],
            'mail_port' => ['nullable', 'integer'],
        ]);

        foreach (self::TEXT_KEYS as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $request->input($key)]);
        }

        foreach (self::CHECKBOX_KEYS as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $request->boolean($key) ? '1' : '0']);
        }

        if ($request->hasFile('logo')) {
            $existing = Setting::where('key', 'logo_path')->value('value');
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }
            $path = $request->file('logo')->store('branding', 'public');
            Setting::updateOrCreate(['key' => 'logo_path'], ['value' => $path]);
        }

        return redirect()->route('admin.settings.edit')->with('status', 'Settings updated.');
    }
}
