@extends('layouts.admin')

@section('title', $meta['label'])

@php
    $v = fn($key, $default = null) => old($key, $settings[$key] ?? $default);

    // Placeholder tokens are built here (fully formed, braces included) so
    // the HTML below only ever echoes a plain variable — writing a literal
    // "{{ }}" pair directly inside a Blade {{ }} echo tag breaks Blade's
    // compiler, since it isn't PHP-string-aware and just scans for the
    // first "}}" it finds, even if that's mid-string.
    $token = fn (string $name) => '{{'.$name.'}}';

    $templates = [
        [
            'key' => 'sms_template_registration',
            'label' => 'Registration Confirmation',
            'help' => 'Sent once, right after a member is registered.',
            'default' => 'Welcome to {{business_name}}! Your Admission ID: {{admission_id}}. Status: {{status}}',
            'placeholders' => array_map($token, ['business_name', 'full_name', 'admission_id', 'status']),
        ],
        [
            'key' => 'sms_template_payment_received',
            'label' => 'Payment Received',
            'help' => 'Sent every time a payment is recorded against a member.',
            'default' => 'Payment received: {{amount}} BDT. Receipt: {{receipt_number}}.',
            'placeholders' => array_map($token, ['business_name', 'full_name', 'amount', 'receipt_number', 'due_date']),
        ],
        [
            'key' => 'sms_template_renewal_reminder',
            'label' => 'Renewal Reminder',
            'help' => 'Sent on the day-offsets configured in Settings > Membership, before a member\'s due date.',
            'default' => '{{business_name}}: Your membership is due for renewal {{when}} ({{due_date}}). Please renew to avoid interruption.',
            'placeholders' => array_map($token, ['business_name', 'full_name', 'when', 'due_date']),
        ],
        [
            'key' => 'sms_template_closure_reminder',
            'label' => 'Closure Reminder',
            'help' => 'Sent on the countdown milestones configured in Settings > Membership, after a member has expired.',
            'default' => '{{business_name}}: Your membership will be permanently closed in {{label}}. Renew now to avoid losing your membership.',
            'placeholders' => array_map($token, ['business_name', 'full_name', 'label']),
        ],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">{{ $meta['label'] }}</h1>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">All Settings</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="alert alert-info small">
        Every automatic SMS in the app is built from one of the templates below — edit the wording here and it
        applies everywhere that message is sent. Use <code>@{{placeholder}}</code> tokens; anything left
        blank falls back to the default shown as a placeholder in the box.
        <br>The free-form "Bulk Message" tool (Members &gt; Send Message) is not templated here since admins type
        that text themselves each time.
    </div>

    <form method="POST" action="{{ route('admin.settings.update', $section) }}">
        @csrf
        @method('PUT')

        @foreach ($templates as $t)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-semibold">{{ $t['label'] }}</span>
                        <div class="text-muted small">{{ $t['help'] }}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary sms-template-reset" data-target="{{ $t['key'] }}" data-default="{{ $t['default'] }}">Reset to Default</button>
                </div>
                <div class="card-body">
                    <textarea name="{{ $t['key'] }}" id="{{ $t['key'] }}" class="form-control sms-template-input" rows="3" maxlength="500" placeholder="{{ $t['default'] }}">{{ $v($t['key']) }}</textarea>
                    <div class="d-flex justify-content-between mt-1">
                        <div class="small text-muted">
                            Placeholders:
                            @foreach ($t['placeholders'] as $p)
                                <code class="me-1">{{ $p }}</code>
                            @endforeach
                        </div>
                        <div class="small text-muted sms-template-count" data-for="{{ $t['key'] }}"></div>
                    </div>
                </div>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>

    <script>
        (function () {
            function segments(len) {
                if (len === 0) return 0;
                return len <= 160 ? 1 : Math.ceil(len / 153);
            }

            function updateCount(textarea) {
                var counter = document.querySelector('.sms-template-count[data-for="' + textarea.id + '"]');
                if (!counter) return;
                var len = textarea.value.length || (textarea.getAttribute('placeholder') || '').length;
                var isPlaceholder = textarea.value.length === 0;
                counter.textContent = len + ' characters' + (isPlaceholder ? ' (default)' : '') + ' · ' + segments(len) + ' SMS segment(s)';
            }

            document.querySelectorAll('.sms-template-input').forEach(function (textarea) {
                updateCount(textarea);
                textarea.addEventListener('input', function () { updateCount(textarea); });
            });

            document.querySelectorAll('.sms-template-reset').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var textarea = document.getElementById(btn.dataset.target);
                    if (!textarea) return;
                    textarea.value = btn.dataset.default;
                    updateCount(textarea);
                });
            });
        })();
    </script>
@endsection
