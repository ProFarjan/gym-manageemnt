<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $member->full_name }} — Membership Application Form</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #222;
            margin: 0;
            padding: 24px;
            background: #f4f4f6;
        }
        .sheet {
            position: relative;
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 36px 44px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 55%;
            max-width: 380px;
            opacity: .07;
            z-index: 0;
            pointer-events: none;
        }
        .sheet > *:not(.watermark) {
            position: relative;
            z-index: 1;
        }
        .toolbar {
            max-width: 900px;
            margin: 0 auto 14px;
            text-align: right;
        }
        .toolbar button {
            background: #d6336c;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #d6336c;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .header-inner {
            display: inline-flex;
            align-items: center;
            gap: 18px;
            text-align: left;
        }
        .header-inner img.logo { height: 56px; flex-shrink: 0; }
        .header-text h1 { margin: 0; font-size: 20px; color: #d6336c; letter-spacing: .02em; }
        .header-text p { margin: 2px 0; font-size: 12px; color: #666; }
        .form-title {
            text-align: center;
            text-transform: uppercase;
            letter-spacing: .12em;
            font-size: 14px;
            font-weight: 700;
            color: #555;
            margin: 4px 0 24px;
        }

        .top-block {
            display: flex;
            justify-content: space-between;
            gap: 28px;
            margin-bottom: 22px;
        }
        .top-block .facts { flex: 1; }
        .top-block .photo-box {
            width: 130px;
            height: 150px;
            border: 2px solid #d6336c;
            border-radius: 6px;
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #faf4f6;
            color: #d6336c;
            font-size: 12px;
            text-align: center;
        }
        .top-block .photo-box img { width: 100%; height: 100%; object-fit: cover; }

        table.facts-table { width: 100%; border-collapse: collapse; }
        table.facts-table td { padding: 4px 0; font-size: 14px; vertical-align: top; }
        table.facts-table td.label { color: #777; width: 150px; }
        table.facts-table td.value { font-weight: 600; }

        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #d6336c;
            border-bottom: 1px solid #eee;
            padding-bottom: 6px;
            margin: 0 0 10px;
        }
        dl.info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 24px;
            margin: 0;
        }
        dl.info-grid dt { font-size: 11px; color: #888; margin: 0; }
        dl.info-grid dd { font-size: 14px; margin: 0 0 8px; color: #222; }

        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-active { background: #d1f5e0; color: #147a4d; }
        .badge-pending { background: #fff3cd; color: #8a6400; }
        .badge-expired { background: #e2e3e5; color: #494a4c; }
        .badge-closed { background: #333; color: #fff; }

        .documents { display: flex; gap: 24px; }
        .documents .doc-box { text-align: center; }
        .documents .doc-box img {
            height: 110px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: block;
            margin-bottom: 4px;
        }
        .documents .doc-box span { font-size: 11px; color: #777; }

        .declaration {
            margin-top: 26px;
            font-size: 12px;
            color: #555;
            border-top: 1px dashed #ccc;
            padding-top: 16px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 46px;
        }
        .signatures .sig {
            width: 42%;
            text-align: center;
            border-top: 1px solid #444;
            padding-top: 6px;
            font-size: 12px;
            color: #555;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .sheet { box-shadow: none; max-width: 100%; padding: 10px 20px; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print</button>
    </div>

    <div class="sheet">
        @if (setting('logo_path'))
            <img src="{{ asset('storage/'.setting('logo_path')) }}" class="watermark">
        @endif

        <div class="header">
            <div class="header-inner">
                @if (setting('logo_path'))
                    <img src="{{ asset('storage/'.setting('logo_path')) }}" class="logo">
                @endif
                <div class="header-text">
                    <h1>{{ setting('business_name', config('app.name')) }}</h1>
                    @if (setting('business_address'))
                        <p>{{ setting('business_address') }}</p>
                    @endif
                    @if (setting('business_phone'))
                        <p>Phone: {{ setting('business_phone') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="form-title">Membership Application Form</div>

        <div class="top-block">
            <div class="facts">
                <table class="facts-table">
                    <tr>
                        <td class="label">Admission ID</td>
                        <td class="value">{{ $member->admission_id }}</td>
                    </tr>
                    <tr>
                        <td class="label">Full Name</td>
                        <td class="value">{{ $member->full_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status</td>
                        <td class="value">
                            <span class="badge badge-{{ $member->status }}">{{ ucfirst($member->status) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Admission Date</td>
                        <td class="value">{{ $member->admission_date?->format('d M Y') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Due Date</td>
                        <td class="value">{{ $member->due_date?->format('d M Y') ?? ($member->isLifetime() ? 'Lifetime' : '—') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Registered By</td>
                        <td class="value">{{ $member->registeredBy?->name ?? ucfirst($member->registration_type) }}</td>
                    </tr>
                </table>
            </div>
            <div class="photo-box">
                @if ($member->photo_path)
                    <img src="{{ asset('storage/'.$member->photo_path) }}">
                @else
                    No Photo
                @endif
            </div>
        </div>

        <div class="section">
            <h2>Personal Information</h2>
            <dl class="info-grid">
                <dt>Mobile</dt><dd>{{ $member->mobile_number }}</dd>
                <dt>Email</dt><dd>{{ $member->email ?? '—' }}</dd>
                <dt>Date of Birth</dt><dd>{{ $member->date_of_birth?->format('d M Y') ?? '—' }}</dd>
                <dt>NID Number</dt><dd>{{ $member->nid_number ?? '—' }}</dd>
                <dt>Address</dt><dd>{{ $member->address ?? '—' }}</dd>
                <dt>Emergency Contact</dt><dd>{{ $member->emergency_contact ?? '—' }}</dd>
            </dl>
        </div>

        <div class="section">
            <h2>Health Information</h2>
            <dl class="info-grid">
                <dt>Height</dt><dd>{{ $member->height ? $member->height.' cm' : '—' }}</dd>
                <dt>Weight</dt><dd>{{ $member->weight ? $member->weight.' kg' : '—' }}</dd>
                <dt>Blood Group</dt><dd>{{ $member->blood_group ?? '—' }}</dd>
                <dt>Fitness Goal</dt><dd>{{ $member->fitness_goal ?? '—' }}</dd>
                <dt>Medical Conditions</dt><dd>{{ $member->medical_conditions ?? '—' }}</dd>
            </dl>
        </div>

        @if ($member->membershipPlan)
            <div class="section">
                <h2>Package Details</h2>
                <dl class="info-grid">
                    <dt>Package Name</dt><dd>{{ $member->membershipPlan->name }}</dd>
                    <dt>Price</dt><dd>{{ number_format($member->membershipPlan->price, 2) }} BDT</dd>
                    <dt>Duration</dt>
                    <dd>{{ $member->membershipPlan->is_lifetime ? 'Lifetime' : $member->membershipPlan->duration_in_months.' Month(s)' }}</dd>
                    <dt>Admission Fee</dt>
                    <dd>
                        @if ($member->membershipPlan->admission_free)
                            Free
                        @else
                            {{ number_format($member->membershipPlan->admission_fee, 2) }} BDT
                        @endif
                    </dd>
                    @if ($member->discount_amount > 0)
                        <dt>Member Discount</dt>
                        <dd>{{ number_format($member->discount_amount, 2) }} BDT{{ $member->discount_reason ? ' ('.$member->discount_reason.')' : '' }}</dd>
                    @endif
                </dl>
            </div>
        @endif

        @if ($member->nid_image_path)
            <div class="section">
                <h2>Documents</h2>
                <div class="documents">
                    <div class="doc-box">
                        <img src="{{ asset('storage/'.$member->nid_image_path) }}">
                        <span>NID</span>
                    </div>
                </div>
            </div>
        @endif

        <div class="declaration">
            I hereby declare that the information provided above is true and correct to the best of my knowledge, and I agree to abide by the gym's rules, regulations, and membership terms.
        </div>

        <div class="signatures">
            <div class="sig">Member Signature</div>
            <div class="sig">Authorized Signature</div>
        </div>
    </div>
</body>
</html>
