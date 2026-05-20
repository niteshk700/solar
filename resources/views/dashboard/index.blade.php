@extends('layouts.app')

@section('title', 'Control Center - Solar IoT Weather Platform')

@section('content')
<!-- Page Header -->
<div class="content-header">
    <div>
        <h2 class="content-title">Control Center Dashboard</h2>
        <p class="content-subtitle">Real-time solar telemetry and environmental insights</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('devices.index') }}" class="btn btn-premium-primary d-flex align-items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Manage Devices</span>
        </a>
    </div>
</div>

<!-- Aggregated Metrics Grid -->
<div class="stats-grid">
    <!-- Temp Card -->
    <div class="glass-card p-4 d-flex align-items-center justify-content-between">
        <div>
            <span class="text-secondary small fw-semibold uppercase tracking-wider">Avg Temperature</span>
            <div class="stat-card-value text-danger">{{ isset($stats['avg_temp']) ? $stats['avg_temp'] . '°C' : '--' }}</div>
            <span class="text-muted small">Across active nodes</span>
        </div>
        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--secondary);">
            <i class="fa-solid fa-temperature-half" style="font-size: 1.5rem;"></i>
        </div>
    </div>

    <!-- Humidity Card -->
    <div class="glass-card p-4 d-flex align-items-center justify-content-between">
        <div>
            <span class="text-secondary small fw-semibold uppercase tracking-wider">Avg Humidity</span>
            <div class="stat-card-value text-primary">{{ isset($stats['avg_hum']) ? $stats['avg_hum'] . '%' : '--' }}</div>
            <span class="text-muted small">Relative air moisture</span>
        </div>
        <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
            <i class="fa-solid fa-droplet" style="font-size: 1.5rem;"></i>
        </div>
    </div>

    <!-- Pressure Card -->
    <div class="glass-card p-4 d-flex align-items-center justify-content-between">
        <div>
            <span class="text-secondary small fw-semibold uppercase tracking-wider">Avg Air Pressure</span>
            <div class="stat-card-value text-info">{{ isset($stats['avg_press']) ? $stats['avg_press'] . ' hPa' : '--' }}</div>
            <span class="text-muted small">Sea-level adjusted</span>
        </div>
        <div class="stat-icon" style="background: rgba(6, 182, 212, 0.1); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--info);">
            <i class="fa-solid fa-gauge-high" style="font-size: 1.5rem;"></i>
        </div>
    </div>

    <!-- Battery Card -->
    <div class="glass-card p-4 d-flex align-items-center justify-content-between">
        <div>
            <span class="text-secondary small fw-semibold uppercase tracking-wider">Avg Battery Cell</span>
            <div class="stat-card-value text-success">
                @if(isset($stats['avg_battery']))
                    {{ $stats['avg_battery'] }}V 
                    <span style="font-size: 0.95rem; font-weight: 500;" class="text-secondary">
                        @php
                            $pct = 0;
                            if ($stats['avg_battery'] >= 4.2) $pct = 100;
                            elseif ($stats['avg_battery'] <= 3.5) $pct = 0;
                            else $pct = round((($stats['avg_battery'] - 3.5) / 0.7) * 100);
                        @endphp
                        ({{ $pct }}%)
                    </span>
                @else
                    --
                @endif
            </div>
            <span class="text-muted small">Solar charging state</span>
        </div>
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--accent);">
            <i class="fa-solid fa-battery-three-quarters" style="font-size: 1.5rem;"></i>
        </div>
    </div>
</div>

<div class="row">
    <!-- Active Solar Nodes Grid -->
    <div class="col-xl-8 col-lg-12 mb-4">
        <h3 class="h5 font-heading fw-bold mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-tower-broadcast text-primary"></i>
            <span>Registered Hardware Nodes ({{ $stats['total_devices'] }})</span>
        </h3>
        
        <div class="row row-cols-1 row-cols-md-2 g-4">
            @forelse($devices as $device)
                <div class="col">
                    <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                        <!-- Node Header -->
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="h6 font-heading fw-bold mb-1">{{ $device->device_name }}</h4>
                                    <span class="badge form-control-glass text-secondary px-2.5 py-1.5 small">{{ $device->device_id }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @if($device->is_online)
                                        <span class="pulse-online"></span>
                                        <span class="text-secondary small fw-semibold">ONLINE</span>
                                    @else
                                        <span class="pulse-offline"></span>
                                        <span class="text-muted small fw-semibold">OFFLINE</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Core Meteorological Data -->
                            @if($device->latest_log)
                                <div class="row g-2 text-center mb-4 mt-3">
                                    <div class="col-4">
                                        <div class="p-2 form-control-glass border-0" style="border-radius: 8px;">
                                            <div class="text-muted small" style="font-size: 0.72rem;">Temp</div>
                                            <div class="fw-bold text-danger">
                                                {{ !is_null($device->latest_log->temperature) ? $device->latest_log->temperature . '°C' : '--' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 form-control-glass border-0" style="border-radius: 8px;">
                                            <div class="text-muted small" style="font-size: 0.72rem;">Humidity</div>
                                            <div class="fw-bold text-primary">
                                                {{ !is_null($device->latest_log->humidity) ? $device->latest_log->humidity . '%' : '--' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 form-control-glass border-0" style="border-radius: 8px;">
                                            <div class="text-muted small" style="font-size: 0.72rem;">Pressure</div>
                                            <div class="fw-bold text-info" style="font-size: 0.88rem; padding: 1.5px 0;">
                                                {{ !is_null($device->latest_log->pressure) ? round($device->latest_log->pressure) . ' hPa' : '--' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Node Status Bars (Battery & RSSI) -->
                                <div class="mb-3 small">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-secondary">Battery ({{ $device->latest_log->battery }}V)</span>
                                        @php
                                            $batPct = 0;
                                            if ($device->latest_log->battery >= 4.2) $batPct = 100;
                                            elseif ($device->latest_log->battery <= 3.5) $batPct = 0;
                                            else $batPct = round((($device->latest_log->battery - 3.5) / 0.7) * 100);

                                            $batColor = 'var(--accent)';
                                            if ($batPct < 20) $batColor = 'var(--danger)';
                                            elseif ($batPct < 50) $batColor = 'var(--warning)';
                                        @endphp
                                        <span class="fw-semibold text-secondary">{{ $batPct }}%</span>
                                    </div>
                                    <div class="progress-bar-custom">
                                        <div class="progress-fill" style="width: {{ $batPct }}%; background: {{ $batColor }};"></div>
                                    </div>
                                </div>

                                <div class="mb-3 small">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-secondary">Signal RSSI ({{ $device->latest_log->rssi }} dBm)</span>
                                        @php
                                            $rssi = $device->latest_log->rssi;
                                            $rssiLabel = 'Excellent';
                                            $rssiColor = 'var(--accent)';
                                            $rssiPct = 100;

                                            if ($rssi <= -90) { $rssiLabel = 'Critical'; $rssiColor = 'var(--danger)'; $rssiPct = 15; }
                                            elseif ($rssi <= -80) { $rssiLabel = 'Poor'; $rssiColor = 'var(--warning)'; $rssiPct = 40; }
                                            elseif ($rssi <= -70) { $rssiLabel = 'Good'; $rssiColor = 'var(--primary)'; $rssiPct = 70; }
                                        @endphp
                                        <span class="fw-semibold" style="color: {{ $rssiColor }}">{{ $rssiLabel }}</span>
                                    </div>
                                    <div class="progress-bar-custom">
                                        <div class="progress-fill" style="width: {{ $rssiPct }}%; background: {{ $rssiColor }};"></div>
                                    </div>
                                </div>

                                <!-- Component Diagnostic Panel -->
                                <div class="border-top pt-3 mt-3">
                                    <span class="text-secondary small fw-semibold uppercase tracking-wider d-block mb-2" style="font-size: 0.75rem;">Component Statuses</span>
                                    <div class="row g-2">
                                        <!-- BME280 Status -->
                                        <div class="col-6">
                                            <div class="p-2 form-control-glass d-flex align-items-center justify-content-between border-0" style="border-radius: 8px; background: rgba(100, 116, 139, 0.05);">
                                                <span class="text-secondary small" style="font-size: 0.68rem; font-weight: 500;">BME280 Sensor</span>
                                                @if($device->latest_log->bme_status)
                                                    <span class="badge bg-success text-white px-1.5 py-0.5" style="font-size: 0.6rem; border-radius: 4px;">OK</span>
                                                @else
                                                    <span class="badge bg-danger text-white px-1.5 py-0.5" style="font-size: 0.6rem; border-radius: 4px; animation: pulse 1.5s infinite;">OFFLINE</span>
                                                @endif
                                            </div>
                                        </div>
                                        <!-- Charger State (TP4056 + Solar) -->
                                        <div class="col-6">
                                            <div class="p-2 form-control-glass d-flex align-items-center justify-content-between border-0" style="border-radius: 8px; background: rgba(100, 116, 139, 0.05);">
                                                <span class="text-secondary small" style="font-size: 0.68rem; font-weight: 500;">Solar Charger</span>
                                                @if($device->latest_log->solar_status === 'charging')
                                                    <span class="badge bg-warning text-dark px-1.5 py-0.5" style="font-size: 0.6rem; border-radius: 4px;"><i class="fa-solid fa-bolt me-0.5" style="font-size: 0.55rem;"></i>CHARGING</span>
                                                @elseif($device->latest_log->solar_status === 'full')
                                                    <span class="badge bg-success text-white px-1.5 py-0.5" style="font-size: 0.6rem; border-radius: 4px;"><i class="fa-solid fa-check me-0.5" style="font-size: 0.55rem;"></i>FULL</span>
                                                @else
                                                    <span class="badge bg-secondary text-white px-1.5 py-0.5" style="font-size: 0.6rem; border-radius: 4px;"><i class="fa-solid fa-moon me-0.5" style="font-size: 0.55rem;"></i>IDLE</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center text-muted my-5 py-2">
                                    <i class="fa-solid fa-triangle-exclamation mb-2" style="font-size: 1.5rem;"></i>
                                    <div class="small">No logs transmitted yet</div>
                                </div>
                            @endif
                        </div>

                        <!-- Footer actions -->
                        <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                <i class="fa-regular fa-clock me-1"></i>
                                {{ $device->last_seen ? $device->last_seen->diffForHumans() : 'Never' }}
                            </span>
                            <a href="{{ route('devices.analytics', $device->id) }}" class="btn btn-premium-secondary btn-sm py-1.5 px-3 d-flex align-items-center gap-1.5" style="border-radius: 8px;">
                                <i class="fa-solid fa-chart-line"></i>
                                <span>Analytics</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="glass-card p-5 text-center text-muted">
                        <i class="fa-solid fa-microchip mb-3" style="font-size: 3rem; color: var(--text-muted)"></i>
                        <h4 class="h5 font-heading text-secondary">No Solar Nodes Registered</h4>
                        <p class="small">Register your first ESP8266 weather station node to begin gathering telemetry.</p>
                        <a href="{{ route('devices.index') }}" class="btn btn-premium-primary mt-3 py-2 px-4">Register Device</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Packet Stream Panel (Recent Packets) -->
    <div class="col-xl-4 col-lg-12">
        <h3 class="h5 font-heading fw-bold mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-cube text-primary"></i>
            <span>Live Packet Stream</span>
        </h3>
        
        <div class="glass-card p-4 h-100" style="max-height: 520px; overflow-y: auto;">
            <div class="d-flex flex-column gap-3">
                @forelse($recentLogs as $log)
                    <div class="form-control-glass p-3 border-0" style="border-radius: 12px; transition: var(--transition-smooth);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small text-primary">{{ $log->device_name }}</span>
                            <span class="text-muted" style="font-size: 0.72rem;">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="row text-center g-1 text-secondary" style="font-size: 0.8rem;">
                            <div class="col-4">
                                <i class="fa-solid fa-temperature-half me-1 text-danger"></i>{{ $log->temperature }}°
                            </div>
                            <div class="col-4">
                                <i class="fa-solid fa-droplet me-1 text-primary"></i>{{ $log->humidity }}%
                            </div>
                            <div class="col-4">
                                <i class="fa-solid fa-battery-three-quarters me-1 text-success"></i>{{ $log->battery }}V
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fa-solid fa-satellite-dish mb-2" style="font-size: 2rem;"></i>
                        <div class="small">Waiting for incoming telemetry...</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
