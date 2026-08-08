@extends('layouts.admin')

@section('title', $meta['label'])

@php
    $v = fn($key, $default = null) => old($key, $settings[$key] ?? $default);

    $saved = json_decode($settings['sms_gateway_params'] ?? '[]', true) ?: [];
    $isMethodRow = fn ($row) => strtolower(trim($row['key'] ?? '')) === 'method';

    $savedMethod = collect($saved)->first($isMethodRow)['value'] ?? 'GET';
    $gatewayMethod = strtoupper(old('gateway_method', $savedMethod ?: 'GET')) === 'POST' ? 'POST' : 'GET';

    $oldKeys = old('gateway_key');
    if ($oldKeys !== null) {
        $oldValues = old('gateway_value', []);
        $gatewayRows = collect($oldKeys)->map(fn ($key, $i) => ['key' => $key, 'value' => $oldValues[$i] ?? '']);
    } else {
        $nonMethodRows = collect($saved)->reject($isMethodRow)->values();
        $gatewayRows = $nonMethodRows->isNotEmpty()
            ? $nonMethodRows
            : collect([
                ['key' => 'URL', 'value' => ''],
                ['key' => 'number', 'value' => '@number'],
                ['key' => 'message', 'value' => '@message'],
            ]);
    }

    $smsEnabled = $v('sms_enabled', '0') === '1';
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

    <form method="POST" action="{{ route('admin.settings.update', $section) }}">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-body">
                <div class="form-check form-switch">
                    <input type="checkbox" name="sms_enabled" value="1" id="smsEnabled" class="form-check-input" @checked($smsEnabled)>
                    <label for="smsEnabled" class="form-check-label fw-semibold">Enable SMS Gateway</label>
                </div>
                <p class="text-muted small mb-0 mt-1">Disabled by default — member notifications are logged instead of sent until this is turned on and configured below.</p>
            </div>
        </div>

        <div id="smsGatewayConfig" class="{{ $smsEnabled ? '' : 'd-none' }}">
            <div class="card mb-3">
                <div class="card-header">Gateway Parameters</div>
                <div class="card-body">
                    <div class="mb-3" style="max-width:200px;">
                        <label class="form-label">HTTP Method</label>
                        <select name="gateway_method" class="form-select form-select-sm">
                            <option value="GET" @selected($gatewayMethod === 'GET')>GET</option>
                            <option value="POST" @selected($gatewayMethod === 'POST')>POST</option>
                        </select>
                    </div>
                    <p class="text-muted small">
                        <strong>URL</strong> is the gateway's API endpoint. For every other row, set the
                        <strong>Key</strong> to the exact parameter name your provider expects (e.g. <code>to</code>,
                        <code>text</code>, <code>apikey</code>) and use <code>@number</code> / <code>@message</code>
                        anywhere in a <strong>Value</strong> — including inside the URL itself — as placeholders for
                        the actual recipient number and message text. Any other value is sent through exactly as
                        typed (API key, sender ID, etc).
                    </p>
                    <table class="table table-sm align-middle mb-2">
                        <thead>
                            <tr>
                                <th>Gateway Key</th>
                                <th>Gateway Value</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="gatewayRowsBody">
                            @foreach ($gatewayRows as $row)
                                <tr class="gateway-row">
                                    <td><input type="text" name="gateway_key[]" class="form-control form-control-sm" value="{{ $row['key'] }}" placeholder="e.g. URL, number, message"></td>
                                    <td><input type="text" name="gateway_value[]" class="form-control form-control-sm" value="{{ $row['value'] }}"></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger gateway-row-remove">&times;</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="gatewayRowAdd">+ Add Row</button>
                </div>
            </div>

            <template id="gatewayRowTemplate">
                <tr class="gateway-row">
                    <td><input type="text" name="gateway_key[]" class="form-control form-control-sm" placeholder="e.g. URL, number, message"></td>
                    <td><input type="text" name="gateway_value[]" class="form-control form-control-sm"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger gateway-row-remove">&times;</button></td>
                </tr>
            </template>

            <div class="card mb-3">
                <div class="card-header">Testing</div>
                <div class="card-body">
                    <label class="form-label">Testing Message Number</label>
                    <input type="text" name="sms_test_number" value="{{ old('sms_test_number') }}" class="form-control" style="max-width:280px;" placeholder="e.g. 01700000000">
                    <p class="text-muted small mb-0 mt-1">If filled in, a "setup successful" test message is sent to this number when you save.</p>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const enabledToggle = document.getElementById('smsEnabled');
            const configWrap = document.getElementById('smsGatewayConfig');

            enabledToggle?.addEventListener('change', function () {
                configWrap.classList.toggle('d-none', !enabledToggle.checked);
            });

            const rowsBody = document.getElementById('gatewayRowsBody');
            const template = document.getElementById('gatewayRowTemplate');

            document.getElementById('gatewayRowAdd')?.addEventListener('click', function () {
                rowsBody.appendChild(template.content.cloneNode(true));
            });

            rowsBody?.addEventListener('click', function (e) {
                const removeBtn = e.target.closest('.gateway-row-remove');
                if (!removeBtn) return;
                if (rowsBody.querySelectorAll('.gateway-row').length <= 1) return;
                removeBtn.closest('.gateway-row')?.remove();
            });
        });
    </script>
@endpush
