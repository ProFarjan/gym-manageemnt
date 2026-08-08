<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * One card on the index page per section, and one dedicated edit page
     * each — settings used to be a single giant form; each section now
     * saves independently.
     */
    private const SECTIONS = [
        'business' => [
            'label' => 'Business Information',
            'description' => 'Gym name, contact details, and the logo shown on receipts and invoices.',
        ],
        'membership' => [
            'label' => 'Membership',
            'description' => 'Admission ID prefix and the auto-checkout closing time.',
        ],
        'sms' => [
            'label' => 'SMS Gateway',
            'description' => 'SMS provider credentials used for member notifications.',
        ],
        'email' => [
            'label' => 'Email (SMTP) Settings',
            'description' => 'Outgoing mail server configuration.',
        ],
        'bkash' => [
            'label' => 'bKash Settings',
            'description' => 'bKash merchant credentials for online payments.',
        ],
        'nagad' => [
            'label' => 'Nagad Settings',
            'description' => 'Nagad merchant credentials for online payments.',
        ],
        'zkteco' => [
            'label' => 'ZKTeco Device Settings',
            'description' => 'Biometric device connection details for attendance sync.',
        ],
        'invoice' => [
            'label' => 'Invoice Footer',
            'description' => 'Footer note printed on bills and payment receipts.',
        ],
    ];

    /**
     * Plain text/select keys per section. Checkboxes and the logo file are
     * handled separately below since they need different request handling
     * (boolean presence, file upload).
     */
    private const SECTION_KEYS = [
        'business' => ['business_name', 'business_tagline', 'business_address', 'business_phone'],
        'membership' => ['membership_prefix', 'gym_closing_time'],
        'sms' => ['sms_driver', 'sms_api_key', 'sms_sender_id'],
        'email' => ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_from_address', 'mail_from_name'],
        'bkash' => ['bkash_app_key', 'bkash_app_secret', 'bkash_username', 'bkash_password'],
        'nagad' => ['nagad_merchant_id', 'nagad_merchant_key'],
        'zkteco' => ['zkteco_ip', 'zkteco_port', 'zkteco_device_id'],
        'invoice' => ['invoice_footer'],
    ];

    private const SECTION_CHECKBOX_KEYS = [
        'bkash' => ['bkash_sandbox'],
        'nagad' => ['nagad_sandbox'],
    ];

    public function index()
    {
        return view('admin.settings.index', ['sections' => self::SECTIONS]);
    }

    public function edit(string $section)
    {
        abort_unless(array_key_exists($section, self::SECTIONS), 404);

        $settings = Setting::cached();
        $meta = self::SECTIONS[$section];

        return view("admin.settings.{$section}", compact('settings', 'section', 'meta'));
    }

    public function update(Request $request, string $section)
    {
        abort_unless(array_key_exists($section, self::SECTIONS), 404);

        if ($section === 'business') {
            $request->validate(['logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg', 'max:1024']]);
        }
        if ($section === 'email') {
            $request->validate(['mail_port' => ['nullable', 'integer']]);
        }

        foreach (self::SECTION_KEYS[$section] as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $request->input($key)]);
        }

        foreach (self::SECTION_CHECKBOX_KEYS[$section] ?? [] as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $request->boolean($key) ? '1' : '0']);
        }

        if ($section === 'business' && $request->hasFile('logo')) {
            $existing = Setting::where('key', 'logo_path')->value('value');
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }
            $path = $request->file('logo')->store('branding', 'public');
            Setting::updateOrCreate(['key' => 'logo_path'], ['value' => $path]);
        }

        return redirect()->route('admin.settings.edit', $section)
            ->with('status', self::SECTIONS[$section]['label'].' updated.');
    }
}
