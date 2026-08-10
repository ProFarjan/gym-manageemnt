<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailLog;
use App\Models\Setting;
use App\Models\ZKTecoCommand;
use App\Services\SmsGateway;
use App\Services\ZKTeco\ZKTecoDeviceClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'description' => 'Enable and configure a generic SMS gateway for member notifications.',
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
        'sms' => [],
        'email' => ['mail_host', 'mail_port', 'mail_encryption', 'mail_username', 'mail_password', 'mail_from_address', 'mail_from_name'],
        'bkash' => ['bkash_app_key', 'bkash_app_secret', 'bkash_username', 'bkash_password'],
        'nagad' => ['nagad_merchant_id', 'nagad_merchant_key'],
        'zkteco' => ['zkteco_mode', 'zkteco_ip', 'zkteco_port', 'zkteco_device_id'],
        'invoice' => ['invoice_footer'],
    ];

    private const SECTION_CHECKBOX_KEYS = [
        'sms' => ['sms_enabled'],
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

        if ($section === 'email') {
            $mailLogs = MailLog::latest('id')->paginate(10);

            return view('admin.settings.email', compact('settings', 'section', 'meta', 'mailLogs'));
        }

        return view("admin.settings.{$section}", compact('settings', 'section', 'meta'));
    }

    public function update(Request $request, string $section)
    {
        abort_unless(array_key_exists($section, self::SECTIONS), 404);

        if ($section === 'business') {
            $request->validate(['logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg', 'max:1024']]);
        }
        if ($section === 'email') {
            $request->validate([
                'mail_port' => ['nullable', 'integer'],
                'mail_encryption' => ['nullable', 'in:,tls,ssl'],
                'mail_test_email' => ['nullable', 'email'],
            ]);
        }
        if ($section === 'zkteco') {
            $request->validate(['zkteco_mode' => ['nullable', 'in:direct,service']]);
        }
        if ($section === 'sms') {
            $request->validate([
                'gateway_method' => ['nullable', 'in:GET,POST'],
                'gateway_key' => ['nullable', 'array'],
                'gateway_key.*' => ['nullable', 'string', 'max:100'],
                'gateway_value' => ['nullable', 'array'],
                'gateway_value.*' => ['nullable', 'string', 'max:500'],
                'sms_test_number' => ['nullable', 'string', 'max:20'],
            ]);
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

        $status = self::SECTIONS[$section]['label'].' updated.';

        if ($section === 'email' && $request->filled('mail_test_email')) {
            $testAddress = $request->input('mail_test_email');

            // Re-apply the just-saved settings so this test actually
            // exercises them, not whatever the mailer was still configured
            // with from before this save (see AppServiceProvider).
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => setting('mail_host'),
                'mail.mailers.smtp.port' => setting('mail_port', 587),
                'mail.mailers.smtp.username' => setting('mail_username'),
                'mail.mailers.smtp.password' => setting('mail_password'),
                'mail.mailers.smtp.scheme' => setting('mail_encryption') === 'ssl' ? 'smtps' : null,
                'mail.from.address' => setting('mail_from_address', 'hello@example.com'),
                'mail.from.name' => setting('mail_from_name', config('app.name')),
            ]);

            try {
                Mail::raw(
                    'This is a test email from '.setting('business_name', config('app.name')).'. Your SMTP settings are working correctly.',
                    fn ($message) => $message->to($testAddress)->subject('SMTP Test — Connection Successful')
                );
                $status .= " Test email sent to {$testAddress}.";
            } catch (\Throwable $e) {
                $status .= " Test email failed: {$e->getMessage()}";

                // The MessageSent event (which logs successful sends
                // automatically) never fires on failure, so log it here.
                MailLog::create([
                    'to_address' => $testAddress,
                    'subject' => 'SMTP Test — Connection Successful',
                    'mailer' => 'smtp',
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        if ($section === 'sms') {
            $pairs = collect($request->input('gateway_key', []))
                ->map(fn ($key, $i) => ['key' => trim($key ?? ''), 'value' => trim($request->input('gateway_value')[$i] ?? '')])
                ->filter(fn ($row) => $row['key'] !== '')
                ->values();

            $pairs->push(['key' => 'method', 'value' => $request->input('gateway_method') === 'POST' ? 'POST' : 'GET']);

            Setting::updateOrCreate(['key' => 'sms_gateway_params'], ['value' => $pairs->toJson()]);

            if ($request->boolean('sms_enabled') && $request->filled('sms_test_number')) {
                $result = SmsGateway::send($request->input('sms_test_number'), 'Your SMS Gateway setup was successful!');
                $status .= $result['success']
                    ? " Test message sent to {$request->input('sms_test_number')}."
                    : " Test message failed: {$result['message']}";
            }
        }

        return redirect()->route('admin.settings.edit', $section)->with('status', $status);
    }

    /**
     * AJAX: test the ZKTeco device connection using whatever IP/port is
     * currently typed into the form, before the admin saves anything.
     */
    public function testZktecoConnection(Request $request, ZKTecoDeviceClient $client)
    {
        $data = $request->validate([
            'zkteco_ip' => ['nullable', 'string', 'max:255'],
            'zkteco_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ]);

        $result = $client->testConnection(trim($data['zkteco_ip'] ?? ''), (int) ($data['zkteco_port'] ?? 4370));

        return response()->json($result);
    }

    /**
     * AJAX: list every user currently enrolled on the device, using the
     * saved zkteco_ip/zkteco_port settings.
     */
    public function zktecoUsers(ZKTecoDeviceClient $client)
    {
        return response()->json($client->listUsers());
    }

    /**
     * AJAX: delete a single user directly on the device by its ZKTeco uid.
     */
    public function zktecoDeleteUser(int $uid, ZKTecoDeviceClient $client)
    {
        return response()->json($client->deleteUserById($uid));
    }

    /**
     * AJAX: generate a fresh shared key for the ZKTeco Windows Service
     * ("Local Service" mode) to authenticate with, replacing any existing
     * key immediately (an old config.json still holding the previous key
     * will start getting 401s until updated).
     */
    public function regenerateZktecoApiKey()
    {
        $key = Str::random(48);

        Setting::updateOrCreate(['key' => 'zkteco_api_key'], ['value' => $key]);

        return response()->json(['api_key' => $key]);
    }

    /**
     * AJAX: recent device-management commands queued for "Local Service"
     * mode, newest first — the Windows service picks these up next time it
     * calls the /api/zkteco/sync endpoint.
     */
    public function zktecoCommandsIndex()
    {
        $commands = ZKTecoCommand::latest('id')->limit(20)->get();

        return response()->json(['commands' => $commands]);
    }

    /**
     * AJAX: queue a new device-management command for "Local Service" mode.
     */
    public function zktecoCommandsStore(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:create_user,update_user,delete_user,list_users'],
            'payload' => ['nullable', 'array'],
        ]);

        $command = ZKTecoCommand::create([
            'type' => $data['type'],
            'payload' => $data['payload'] ?? [],
            'status' => 'pending',
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['command' => $command]);
    }

    /**
     * AJAX: search/paginate the Mail Log table on Settings > Email.
     */
    public function emailMailLogs(Request $request)
    {
        $mailLogs = MailLog::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($q) use ($search) {
                    $q->where('to_address', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.settings.partials._mail-logs-table', compact('mailLogs'));
    }
}
