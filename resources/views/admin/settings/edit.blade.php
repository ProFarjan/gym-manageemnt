@extends('layouts.admin')

@section('title', 'Settings')

@php
    $v = fn($key, $default = null) => old($key, $settings[$key] ?? $default);
@endphp

@section('content')
    <h1 class="h4 mb-4">Settings</h1>

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

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header">Business Information</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Gym Name</label>
                    <input type="text" name="business_name" value="{{ $v('business_name', config('app.name')) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tagline</label>
                    <input type="text" name="business_tagline" value="{{ $v('business_tagline', "Mymensingh's First Ever & Only Dedicated Ladies Gym") }}" class="form-control">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Address</label>
                    <input type="text" name="business_address" value="{{ $v('business_address') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="business_phone" value="{{ $v('business_phone') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Logo</label>
                    @if ($v('logo_path'))
                        <div class="mb-2"><img src="{{ asset('storage/'.$v('logo_path')) }}" style="height:48px;"></div>
                    @endif
                    <input type="file" name="logo" class="form-control">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Membership</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Admission ID Prefix</label>
                    <input type="text" name="membership_prefix" value="{{ $v('membership_prefix', 'GG') }}" class="form-control" maxlength="10">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gym Closing Time (for auto-checkout)</label>
                    <input type="time" name="gym_closing_time" value="{{ $v('gym_closing_time', '22:00') }}" class="form-control">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">SMS Gateway</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Driver</label>
                    <input type="text" name="sms_driver" value="{{ $v('sms_driver', 'log') }}" class="form-control" placeholder="e.g. log (dev) or your provider's name">
                </div>
                <div class="col-md-4">
                    <label class="form-label">API Key</label>
                    <input type="text" name="sms_api_key" value="{{ $v('sms_api_key') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sender ID</label>
                    <input type="text" name="sms_sender_id" value="{{ $v('sms_sender_id') }}" class="form-control">
                </div>
                <div class="col-md-12">
                    <p class="text-muted small mb-0">No live SMS gateway is connected yet — messages are logged instead of sent until real credentials and a provider integration are added.</p>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Email (SMTP) Settings</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" name="mail_host" value="{{ $v('mail_host') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Port</label>
                    <input type="number" name="mail_port" value="{{ $v('mail_port') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="mail_username" value="{{ $v('mail_username') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="mail_password" value="{{ $v('mail_password') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">From Address</label>
                    <input type="email" name="mail_from_address" value="{{ $v('mail_from_address') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ $v('mail_from_name', config('app.name')) }}" class="form-control">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">bKash Settings</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">App Key</label>
                    <input type="text" name="bkash_app_key" value="{{ $v('bkash_app_key') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">App Secret</label>
                    <input type="password" name="bkash_app_secret" value="{{ $v('bkash_app_secret') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="bkash_username" value="{{ $v('bkash_username') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="bkash_password" value="{{ $v('bkash_password') }}" class="form-control">
                </div>
                <div class="col-md-12">
                    <div class="form-check">
                        <input type="checkbox" name="bkash_sandbox" value="1" id="bkash_sandbox" class="form-check-input" @checked($v('bkash_sandbox', '1') === '1')>
                        <label for="bkash_sandbox" class="form-check-label">Sandbox Mode</label>
                    </div>
                </div>
                <div class="col-md-12">
                    <p class="text-muted small mb-0">No live bKash merchant account is connected yet — the Member Portal renewal flow uses a local demo checkout until real credentials and the bKash SDK are wired in.</p>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Nagad Settings</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Merchant ID</label>
                    <input type="text" name="nagad_merchant_id" value="{{ $v('nagad_merchant_id') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Merchant Key</label>
                    <input type="password" name="nagad_merchant_key" value="{{ $v('nagad_merchant_key') }}" class="form-control">
                </div>
                <div class="col-md-12">
                    <div class="form-check">
                        <input type="checkbox" name="nagad_sandbox" value="1" id="nagad_sandbox" class="form-check-input" @checked($v('nagad_sandbox', '1') === '1')>
                        <label for="nagad_sandbox" class="form-check-label">Sandbox Mode</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">ZKTeco Device Settings</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Device IP</label>
                    <input type="text" name="zkteco_ip" value="{{ $v('zkteco_ip') }}" class="form-control" placeholder="e.g. 192.168.1.201">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Port</label>
                    <input type="text" name="zkteco_port" value="{{ $v('zkteco_port', '4370') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Device ID</label>
                    <input type="text" name="zkteco_device_id" value="{{ $v('zkteco_device_id') }}" class="form-control">
                </div>
                <div class="col-md-12">
                    <p class="text-muted small mb-0">No physical ZKTeco device is reachable yet — user sync and attendance pull run in stub mode regardless of these values.</p>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Invoice Footer</div>
            <div class="card-body">
                <textarea name="invoice_footer" class="form-control" rows="2">{{ $v('invoice_footer', "Thank you for choosing GirliGirl Gym & Fitness — Mymensingh's First Ever & Only Dedicated Ladies Gym.") }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
@endsection
