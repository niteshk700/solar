@extends('layouts.app')

@section('title', 'Manage Devices - Solar IoT Weather Platform')

@section('content')
<!-- Page Header -->
<div class="content-header">
    <div>
        <h2 class="content-title">Device Management</h2>
        <p class="content-subtitle">Register, monitor, and configure environmental solar hardware nodes</p>
    </div>
    <div>
        <button class="btn btn-premium-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
            <i class="fa-solid fa-circle-plus"></i>
            <span>Register New Node</span>
        </button>
    </div>
</div>

<!-- Alert messages -->
@if(session('success'))
    <div class="alert alert-success glass-panel border-0 text-success p-3 mb-4 d-flex align-items-center gap-2" role="alert" style="background: rgba(16, 185, 129, 0.1);">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger glass-panel border-0 text-danger p-3 mb-4" role="alert" style="background: rgba(239, 68, 68, 0.1);">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span class="fw-bold">Validation Error Occurred</span>
        </div>
        <ul class="mb-0 small ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Devices frosted glass table -->
<div class="glass-card mb-4">
    <div class="table-responsive">
        <table class="table table-glass">
            <thead>
                <tr>
                    <th>Device Name</th>
                    <th>Device ID</th>
                    <th>Security Credentials</th>
                    <th>Network Status</th>
                    <th>Last Transmission</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                    <tr>
                        <!-- Name & Icon -->
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="sidebar-brand-icon" style="width: 38px; height: 38px; border-radius: 8px; font-size: 1.1rem; background: var(--primary-glow); color: var(--primary);">
                                    <i class="fa-solid fa-microchip"></i>
                                </div>
                                <div class="fw-bold">{{ $device->device_name }}</div>
                            </div>
                        </td>

                        <!-- ID -->
                        <td>
                            <code class="badge form-control-glass text-secondary px-2.5 py-1.5 small">{{ $device->device_id }}</code>
                        </td>

                        <!-- Security Credentials (Hidden by default, copy button) -->
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <input type="password" class="form-control form-control-glass form-control-sm border-0 bg-transparent text-secondary py-1" 
                                       value="{{ $device->api_key }}" readonly id="apiKey_{{ $device->id }}" style="width: 130px; font-family: monospace;">
                                <button class="btn btn-sm btn-link text-primary p-0" onclick="toggleKeyVisibility({{ $device->id }})" title="Reveal Credentials">
                                    <i class="fa-regular fa-eye" id="eyeIcon_{{ $device->id }}"></i>
                                </button>
                                <button class="btn btn-sm btn-link text-accent p-0" onclick="copyApiKey('{{ $device->api_key }}')" title="Copy Credentials">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </td>

                        <!-- Status badge -->
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($device->status === 'active')
                                    @if($device->is_online)
                                        <span class="badge bg-success-subtle text-success px-2.5 py-1.5 d-flex align-items-center gap-1.5" style="border-radius: 6px;">
                                            <span class="pulse-online" style="width: 6px; height: 6px; box-shadow: none; animation: none;"></span>
                                            <span>ONLINE</span>
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning px-2.5 py-1.5 d-flex align-items-center gap-1.5" style="border-radius: 6px;">
                                            <span class="pulse-offline" style="width: 6px; height: 6px; background: var(--warning);"></span>
                                            <span>OFFLINE</span>
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-2.5 py-1.5 d-flex align-items-center gap-1.5" style="border-radius: 6px;">
                                        <span class="pulse-offline" style="width: 6px; height: 6px; background: var(--danger);"></span>
                                        <span>DEACTIVATED</span>
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Last online -->
                        <td>
                            <span class="text-secondary small">
                                <i class="fa-regular fa-clock me-1"></i>
                                {{ $device->last_seen ? $device->last_seen->diffForHumans() : 'Never transmitted' }}
                            </span>
                        </td>

                        <!-- Actions CRUD -->
                        <td>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <a href="{{ route('devices.analytics', $device->id) }}" class="btn btn-sm btn-premium-secondary px-2.5 py-1.5" title="View historical graphs">
                                    <i class="fa-solid fa-chart-line text-primary"></i>
                                </a>
                                <button class="btn btn-sm btn-premium-secondary px-2.5 py-1.5" 
                                        data-bs-toggle="modal" data-bs-target="#editDeviceModal_{{ $device->id }}" title="Edit configuration">
                                    <i class="fa-solid fa-pen-to-square text-info"></i>
                                </button>
                                <button class="btn btn-sm btn-premium-secondary px-2.5 py-1.5" 
                                        data-bs-toggle="modal" data-bs-target="#deleteDeviceModal_{{ $device->id }}" title="Delete Node">
                                    <i class="fa-solid fa-trash-can text-danger"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Device Modal -->
                    <div class="modal fade modal-glass" id="editDeviceModal_{{ $device->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title font-heading fw-bold">Edit Node Configuration</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('devices.update', $device->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-semibold">Device Hardware ID (Unique)</label>
                                            <input type="text" class="form-control form-control-glass" value="{{ $device->device_id }}" disabled>
                                            <div class="form-text text-muted">Hardware identifiers cannot be changed after registration.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="device_name_{{ $device->id }}" class="form-label text-secondary small fw-semibold">Custom Friendly Name</label>
                                            <input type="text" name="device_name" id="device_name_{{ $device->id }}" 
                                                   class="form-control form-control-glass" value="{{ $device->device_name }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="api_key_{{ $device->id }}_form" class="form-label text-secondary small fw-semibold">API Secret key / Device Token</label>
                                            <input type="text" name="api_key" id="api_key_{{ $device->id }}_form" 
                                                   class="form-control form-control-glass" value="{{ $device->api_key }}" required>
                                            <div class="form-text text-muted">This token is used by the ESP8266 node to authenticate JSON payloads.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="status_{{ $device->id }}" class="form-label text-secondary small fw-semibold">Node Status</label>
                                            <select name="status" id="status_{{ $device->id }}" class="form-select form-control-glass" required>
                                                <option value="active" {{ $device->status === 'active' ? 'selected' : '' }}>Active (Allow telemetry transmission)</option>
                                                <option value="inactive" {{ $device->status === 'inactive' ? 'selected' : '' }}>Deactivated (Reject telemetry transmission)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-premium-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-premium-primary py-2 px-3">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Device Modal -->
                    <div class="modal fade modal-glass" id="deleteDeviceModal_{{ $device->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title font-heading fw-bold text-danger">Delete Solar Node?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you absolutely sure you want to delete the solar hardware node <strong>"{{ $device->device_name }}"</strong> (<code>{{ $device->device_id }}</code>)?</p>
                                    <div class="alert alert-danger p-2.5 small" role="alert">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                        <strong>WARNING:</strong> This will permanently delete the device registration AND all weather telemetry log data from the database. This action is irreversible.
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <form action="{{ route('devices.destroy', $device->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-premium-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger py-2 px-3 text-white">Delete Permanently</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fa-solid fa-circle-exclamation mb-3" style="font-size: 3rem; color: var(--text-muted)"></i>
                            <h5 class="h6 font-heading">No Hardware Stations Found</h5>
                            <p class="small text-secondary mb-0">Register your first environmental tracking node to get started.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Device Modal -->
<div class="modal fade modal-glass" id="addDeviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-heading fw-bold">Register Hardware Node</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('devices.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="device_id" class="form-label text-secondary small fw-semibold">Hardware Device ID (Unique URL-slug)</label>
                        <input type="text" name="device_id" id="device_id" class="form-control form-control-glass" 
                               placeholder="weather_node_01" required value="{{ old('device_id') }}">
                        <div class="form-text text-muted">Use alphanumeric and underscores only. This MUST match the device ID inside the ESP8266 code payload.</div>
                    </div>

                    <div class="mb-3">
                        <label for="device_name" class="form-label text-secondary small fw-semibold">Friendly Station Name</label>
                        <input type="text" name="device_name" id="device_name" class="form-control form-control-glass" 
                               placeholder="Meadow Station BME280" required value="{{ old('device_name') }}">
                        <div class="form-text text-muted">A descriptive name representing the physical location (e.g. Roof Node).</div>
                    </div>

                    <div class="mb-3">
                        <label for="api_key" class="form-label text-secondary small fw-semibold">Device API Key / Secure Secret</label>
                        <input type="text" name="api_key" id="api_key" class="form-control form-control-glass" 
                               placeholder="Leave blank to auto-generate a secure token" value="{{ old('api_key') }}">
                        <div class="form-text text-muted">Highly recommended to leave blank so the platform generates a high-entropy secret.</div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label text-secondary small fw-semibold">Initial Status</label>
                        <select name="status" id="status" class="form-select form-control-glass" required>
                            <option value="active" selected>Active (Immediately accept incoming connections)</option>
                            <option value="inactive">Deactivated (Reject incoming connections)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-premium-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-premium-primary py-2 px-3">Register Device</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Copy function
    function copyApiKey(key) {
        navigator.clipboard.writeText(key).then(() => {
            alert('Device API Key successfully copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }

    // Toggle visibility of fields
    function toggleKeyVisibility(id) {
        const inputField = document.getElementById('apiKey_' + id);
        const eyeIcon = document.getElementById('eyeIcon_' + id);
        
        if (inputField.type === 'password') {
            inputField.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            inputField.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }
</script>
@endsection
