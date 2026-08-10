@extends('layouts.admin')

@section('title', $meta['label'])

@php
    $v = fn($key, $default = null) => old($key, $settings[$key] ?? $default);
    $mode = $v('zkteco_mode', 'direct') === 'service' ? 'service' : 'direct';
    $apiKey = $settings['zkteco_api_key'] ?? null;
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
            <div class="card-header">Connection Mode</div>
            <div class="card-body">
                <div class="form-check mb-2">
                    <input type="radio" name="zkteco_mode" value="direct" id="zktecoModeDirect" class="form-check-input zkteco-mode-radio" @checked($mode === 'direct')>
                    <label for="zktecoModeDirect" class="form-check-label">
                        <strong>Current System — Direct IP</strong>
                        <div class="text-muted small">This server connects straight to the device over the network using its IP/port.</div>
                    </label>
                </div>
                <div class="form-check">
                    <input type="radio" name="zkteco_mode" value="service" id="zktecoModeService" class="form-check-input zkteco-mode-radio" @checked($mode === 'service')>
                    <label for="zktecoModeService" class="form-check-label">
                        <strong>Client-Side PC — Local Service</strong>
                        <div class="text-muted small">
                            A local Windows PC on the same network as the device runs the
                            <a href="https://github.com/ProFarjan/ZKTeco-Windows-Service" target="_blank" rel="noopener">ZKTeco Windows Service</a>
                            and pushes/pulls data through this app's API instead — use this when the device isn't
                            directly reachable from this server (e.g. different network/behind NAT).
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div id="zktecoDirectSection" class="{{ $mode === 'direct' ? '' : 'd-none' }}">
            <div class="card mb-3">
                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Device IP</label>
                        <input type="text" name="zkteco_ip" id="zktecoIp" value="{{ $v('zkteco_ip') }}" class="form-control" placeholder="e.g. 192.168.1.201">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Port</label>
                        <input type="text" name="zkteco_port" id="zktecoPort" value="{{ $v('zkteco_port', '4370') }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Device ID</label>
                        <input type="text" name="zkteco_device_id" value="{{ $v('zkteco_device_id') }}" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <button type="button" id="zktecoTestBtn" class="btn btn-outline-secondary btn-sm">Test Connection</button>
                        <span id="zktecoTestStatus" class="ms-2 small"></span>
                    </div>
                    <div class="col-md-12">
                        <p class="text-muted small mb-0">
                            Test Connection only checks that something is reachable at this IP/port — it does not
                            verify the ZKTeco protocol handshake or Device ID.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="zktecoServiceSection" class="{{ $mode === 'service' ? '' : 'd-none' }}">
            <div class="card mb-3">
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Sync Endpoint URL</label>
                        <input type="text" class="form-control form-control-sm" value="{{ route('api.zkteco.sync') }}" readonly onclick="this.select()">
                        <div class="form-text">Paste this into the Windows service's <code>config.json</code> as the API endpoint.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">API Key</label>
                        <div class="input-group input-group-sm" style="max-width:520px;">
                            <input type="text" id="zktecoApiKey" class="form-control" value="{{ $apiKey ?? 'Not generated yet' }}" readonly onclick="this.select()">
                            <button type="button" id="zktecoRegenerateKeyBtn" class="btn btn-outline-secondary">Regenerate</button>
                        </div>
                        <div class="form-text">Sent by the Windows service as the <code>X-API-Key</code> header. Regenerating immediately invalidates the old key.</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Device Commands</span>
                    <button type="button" id="zktecoQueueListUsersBtn" class="btn btn-sm btn-primary">Queue: List Users</button>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Commands queued here are picked up by the Windows service the next time it syncs (its own
                        interval, e.g. every 5 minutes) — not instantly.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Queued</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody id="zktecoCommandsBody">
                                <tr><td colspan="4" class="text-center text-muted py-3">Loading…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>

    <div id="zktecoDirectUsersCard" class="card mt-4 {{ $mode === 'direct' ? '' : 'd-none' }}">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Enrolled Users</span>
            <button type="button" id="zktecoUserListBtn" class="btn btn-sm btn-primary">User List</button>
        </div>
        <div class="card-body">
            <div id="zktecoUsersStatus" class="small mb-2 d-none"></div>
            <div class="table-responsive d-none" id="zktecoUsersTableWrap">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>UID</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Privilege</th>
                            <th>Card</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="zktecoUsersBody"></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value ?? '';
                return div.innerHTML;
            }

            // --- Mode toggle ---
            const directSection = document.getElementById('zktecoDirectSection');
            const serviceSection = document.getElementById('zktecoServiceSection');
            const directUsersCard = document.getElementById('zktecoDirectUsersCard');

            document.querySelectorAll('.zkteco-mode-radio').forEach((radio) => {
                radio.addEventListener('change', function () {
                    const isService = document.getElementById('zktecoModeService').checked;
                    serviceSection.classList.toggle('d-none', !isService);
                    directSection.classList.toggle('d-none', isService);
                    directUsersCard.classList.toggle('d-none', isService);
                });
            });

            // --- Direct mode: Test Connection ---
            const testBtn = document.getElementById('zktecoTestBtn');
            const statusEl = document.getElementById('zktecoTestStatus');

            testBtn?.addEventListener('click', function () {
                const ip = document.getElementById('zktecoIp').value.trim();
                const port = document.getElementById('zktecoPort').value.trim();

                testBtn.disabled = true;
                statusEl.className = 'ms-2 small text-muted';
                statusEl.textContent = 'Testing…';

                fetch('{{ route('admin.settings.zkteco.test-connection') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ zkteco_ip: ip, zkteco_port: port }),
                })
                    .then((response) => response.json())
                    .then((data) => {
                        statusEl.className = 'ms-2 small ' + (data.success ? 'text-success' : 'text-danger');
                        statusEl.textContent = data.message;
                    })
                    .catch(() => {
                        statusEl.className = 'ms-2 small text-danger';
                        statusEl.textContent = 'Test failed — could not reach the server.';
                    })
                    .finally(() => {
                        testBtn.disabled = false;
                    });
            });

            // --- Direct mode: User List ---
            const userListBtn = document.getElementById('zktecoUserListBtn');
            const usersStatus = document.getElementById('zktecoUsersStatus');
            const usersTableWrap = document.getElementById('zktecoUsersTableWrap');
            const usersBody = document.getElementById('zktecoUsersBody');

            function renderUsers(users) {
                if (!users.length) {
                    usersBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">No users found on the device.</td></tr>';
                    return;
                }

                usersBody.innerHTML = users.map((u) => `
                    <tr>
                        <td>${escapeHtml(u.uid)}</td>
                        <td>${escapeHtml(u.user_id)}</td>
                        <td>${escapeHtml(u.name)}</td>
                        <td>${Number(u.privilege) === 14 ? '<span class="badge bg-dark">Admin</span>' : '<span class="badge bg-secondary">User</span>'}</td>
                        <td>${u.card ? escapeHtml(u.card) : '—'}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger zkteco-user-delete" data-uid="${escapeHtml(u.uid)}" data-name="${escapeHtml(u.name)}">Delete</button>
                        </td>
                    </tr>
                `).join('');
            }

            userListBtn?.addEventListener('click', function () {
                userListBtn.disabled = true;
                usersStatus.classList.remove('d-none');
                usersStatus.className = 'small mb-2 text-muted';
                usersStatus.textContent = 'Loading users…';
                usersTableWrap.classList.add('d-none');

                fetch('{{ route('admin.settings.zkteco.users') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then((response) => response.json())
                    .then((data) => {
                        usersStatus.className = 'small mb-2 ' + (data.success ? 'text-success' : 'text-danger');
                        usersStatus.textContent = data.message;

                        if (data.success) {
                            usersTableWrap.classList.remove('d-none');
                            renderUsers(data.users || []);
                        }
                    })
                    .catch(() => {
                        usersStatus.className = 'small mb-2 text-danger';
                        usersStatus.textContent = 'Failed to load users — could not reach the server.';
                    })
                    .finally(() => {
                        userListBtn.disabled = false;
                    });
            });

            usersBody?.addEventListener('click', function (e) {
                const btn = e.target.closest('.zkteco-user-delete');
                if (!btn) return;

                const uid = btn.dataset.uid;
                const name = btn.dataset.name || ('UID ' + uid);
                if (!confirm(`Delete "${name}" from the device? This cannot be undone.`)) return;

                btn.disabled = true;
                btn.textContent = 'Deleting…';

                fetch('{{ url('admin/settings/zkteco/users') }}/' + uid, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            btn.closest('tr')?.remove();
                        } else {
                            alert(data.message);
                            btn.disabled = false;
                            btn.textContent = 'Delete';
                        }
                    })
                    .catch(() => {
                        alert('Delete failed — could not reach the server.');
                        btn.disabled = false;
                        btn.textContent = 'Delete';
                    });
            });

            // --- Service mode: Regenerate API key ---
            document.getElementById('zktecoRegenerateKeyBtn')?.addEventListener('click', function () {
                if (!confirm('Regenerate the API key? The Windows service will stop authenticating until its config.json is updated with the new key.')) return;

                fetch('{{ route('admin.settings.zkteco.regenerate-api-key') }}', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
                })
                    .then((response) => response.json())
                    .then((data) => {
                        document.getElementById('zktecoApiKey').value = data.api_key;
                    })
                    .catch(() => alert('Failed to regenerate the API key — please try again.'));
            });

            // --- Service mode: Commands ---
            const commandsBody = document.getElementById('zktecoCommandsBody');

            function statusBadge(status) {
                const map = { pending: 'secondary', sent: 'info', completed: 'success', failed: 'danger' };
                return `<span class="badge bg-${map[status] || 'secondary'}">${escapeHtml(status)}</span>`;
            }

            function summarizeResult(command) {
                if (!command.result) return '—';
                const msg = command.result.message ? escapeHtml(command.result.message) : '';
                if (command.type === 'list_users' && command.result.data && Array.isArray(command.result.data.users)) {
                    return `${command.result.data.users.length} user(s) found` + (msg ? ` — ${msg}` : '');
                }
                return msg || '—';
            }

            function loadCommands() {
                fetch('{{ route('admin.settings.zkteco.commands.index') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then((response) => response.json())
                    .then((data) => {
                        const commands = data.commands || [];
                        if (!commands.length) {
                            commandsBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No commands queued yet.</td></tr>';
                            return;
                        }
                        commandsBody.innerHTML = commands.map((c) => `
                            <tr>
                                <td>${escapeHtml(c.type)}</td>
                                <td>${statusBadge(c.status)}</td>
                                <td>${escapeHtml(c.created_at)}</td>
                                <td>${summarizeResult(c)}</td>
                            </tr>
                        `).join('');
                    })
                    .catch(() => {
                        commandsBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Failed to load commands.</td></tr>';
                    });
            }

            document.getElementById('zktecoQueueListUsersBtn')?.addEventListener('click', function (e) {
                e.target.disabled = true;

                fetch('{{ route('admin.settings.zkteco.commands.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ type: 'list_users' }),
                })
                    .then((response) => response.json())
                    .then(() => loadCommands())
                    .catch(() => alert('Failed to queue the command — please try again.'))
                    .finally(() => { e.target.disabled = false; });
            });

            if (document.getElementById('zktecoModeService')?.checked) {
                loadCommands();
            }
        });
    </script>
@endpush
