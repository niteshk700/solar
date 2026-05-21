@extends('layouts.app')

@section('title', 'Control Center - Solar IoT Weather Platform')

@section('content')
<style>
/* Sleek glassmorphic card refinements & animations */
.glass-card {
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease !important;
    position: relative;
    overflow: hidden;
}
.glass-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;
    background: linear-gradient(to right, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.03) 50%, rgba(255, 255, 255, 0) 100%);
    transform: skewX(-25deg);
    transition: 0.75s;
}
.glass-card:hover::before {
    left: 150%;
}
.glass-card:hover {
    transform: translateY(-4px) scale(1.008);
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.18), 0 0 1px rgba(255, 255, 255, 0.25) !important;
}
/* Neon status pulses */
.pulse-online {
    background: #10B981 !important;
    box-shadow: 0 0 10px #10B981;
}
.pulse-offline {
    background: #EF4444 !important;
    box-shadow: 0 0 10px #EF4444;
}
/* Value update flash cues */
@keyframes pop-flash {
    0% { transform: scale(1); filter: brightness(1); }
    50% { transform: scale(1.08); filter: brightness(1.4); }
    100% { transform: scale(1); filter: brightness(1); }
}
.flash-update {
    animation: pop-flash 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    display: inline-block;
}
/* Interactive chart override */
#liveTelemetryChart {
    filter: drop-shadow(0px 8px 24px rgba(59, 130, 246, 0.1));
}
.chart-card {
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
}
</style>

<!-- Page Header -->
<div class="content-header">
    <div>
        <h2 class="content-title">Control Center Dashboard</h2>
        <p class="content-subtitle">Real-time solar telemetry and environmental insights</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button id="btn-force-sync" class="btn btn-premium-secondary d-flex align-items-center gap-2" style="border-radius: 10px; padding: 10px 18px;">
            <i class="fa-solid fa-arrows-rotate" id="sync-icon"></i>
            <span>Force Sync</span>
        </button>
        <a href="{{ route('dashboard.export') }}" class="btn btn-premium-secondary d-flex align-items-center gap-2" style="border-radius: 10px; padding: 10px 18px;">
            <i class="fa-solid fa-file-csv text-success"></i>
            <span>Export Excel</span>
        </a>
        <a href="{{ route('devices.index') }}" class="btn btn-premium-primary d-flex align-items-center gap-2" style="border-radius: 10px; padding: 10px 18px;">
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
            <div class="stat-card-value text-danger" id="avg-temp">{{ isset($stats['avg_temp']) ? $stats['avg_temp'] . '°C' : '--' }}</div>
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
            <div class="stat-card-value text-primary" id="avg-hum">{{ isset($stats['avg_hum']) ? $stats['avg_hum'] . '%' : '--' }}</div>
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
            <div class="stat-card-value text-info" id="avg-press">{{ isset($stats['avg_press']) ? $stats['avg_press'] . ' hPa' : '--' }}</div>
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
            <div class="stat-card-value text-success" id="avg-battery">
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
    <!-- Live Telemetry Stream Chart -->
    <div class="col-12 mb-4">
        <div class="glass-card p-4 chart-card">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h3 class="h5 font-heading fw-bold m-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-chart-line text-primary"></i>
                    <span>Real-Time Environmental Stream</span>
                </h3>
                <div>
                    <span class="badge form-control-glass text-secondary px-2.5 py-1.5 small" id="chart-pulse-badge">
                        <span class="pulse-online me-1.5" style="display: inline-block; width: 8px; height: 8px; border-radius: 50%;"></span>LIVE STREAMING
                    </span>
                </div>
            </div>
            <div style="position: relative; height: 320px; width: 100%;">
                <canvas id="liveTelemetryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Active Solar Nodes Grid -->
    <div class="col-xl-8 col-lg-12 mb-4">
        <h3 class="h5 font-heading fw-bold mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-tower-broadcast text-primary"></i>
            <span>Registered Hardware Nodes ({{ $stats['total_devices'] }})</span>
        </h3>
        
        <div class="row row-cols-1 row-cols-md-2 g-4">
            @forelse($devices as $device)
                <div class="col" id="device-card-{{ $device->device_id }}">
                    <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                        <!-- Node Header -->
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="h6 font-heading fw-bold mb-1">{{ $device->device_name }}</h4>
                                    <span class="badge form-control-glass text-secondary px-2.5 py-1.5 small">{{ $device->device_id }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 dev-online-badge">
                                    @if($device->is_online)
                                        <span class="pulse-online"></span>
                                        <span class="text-secondary small fw-semibold">ONLINE</span>
                                    @else
                                        <span class="pulse-offline"></span>
                                        <span class="text-muted small fw-semibold">OFFLINE</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Core Telemetry Area Wrapper -->
                            <div class="dev-telemetry-body">
                                @if($device->latest_log)
                                    <!-- Core Meteorological Data -->
                                    <div class="row g-2 text-center mb-4 mt-3">
                                        <div class="col-4">
                                            <div class="p-2 form-control-glass border-0" style="border-radius: 8px;">
                                                <div class="text-muted small" style="font-size: 0.72rem;">Temp</div>
                                                <div class="fw-bold text-danger dev-temp">
                                                    {{ !is_null($device->latest_log->temperature) ? $device->latest_log->temperature . '°C' : '--' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 form-control-glass border-0" style="border-radius: 8px;">
                                                <div class="text-muted small" style="font-size: 0.72rem;">Humidity</div>
                                                <div class="fw-bold text-primary dev-hum">
                                                    {{ !is_null($device->latest_log->humidity) ? $device->latest_log->humidity . '%' : '--' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 form-control-glass border-0" style="border-radius: 8px;">
                                                <div class="text-muted small" style="font-size: 0.72rem;">Pressure</div>
                                                <div class="fw-bold text-info dev-press" style="font-size: 0.88rem; padding: 1.5px 0;">
                                                    {{ !is_null($device->latest_log->pressure) ? round($device->latest_log->pressure) . ' hPa' : '--' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Node Status Bars (Battery & RSSI) -->
                                    <div class="mb-3 small">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-secondary dev-battery-label">Battery ({{ $device->latest_log->battery }}V)</span>
                                            @php
                                                $batPct = 0;
                                                if ($device->latest_log->battery >= 4.2) $batPct = 100;
                                                elseif ($device->latest_log->battery <= 3.5) $batPct = 0;
                                                else $batPct = round((($device->latest_log->battery - 3.5) / 0.7) * 100);

                                                $batColor = 'var(--accent)';
                                                if ($batPct < 20) $batColor = 'var(--danger)';
                                                elseif ($batPct < 50) $batColor = 'var(--warning)';
                                            @endphp
                                            <span class="fw-semibold text-secondary dev-battery-pct">{{ $batPct }}%</span>
                                        </div>
                                        <div class="progress-bar-custom">
                                            <div class="progress-fill dev-battery-fill" style="width: {{ $batPct }}%; background: {{ $batColor }};"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3 small">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-secondary dev-rssi-label">Signal RSSI ({{ $device->latest_log->rssi }} dBm)</span>
                                            @php
                                                $rssi = $device->latest_log->rssi;
                                                $rssiLabel = 'Excellent';
                                                $rssiColor = 'var(--accent)';
                                                $rssiPct = 100;

                                                if ($rssi <= -90) { $rssiLabel = 'Critical'; $rssiColor = 'var(--danger)'; $rssiPct = 15; }
                                                elseif ($rssi <= -80) { $rssiLabel = 'Poor'; $rssiColor = 'var(--warning)'; $rssiPct = 40; }
                                                elseif ($rssi <= -70) { $rssiLabel = 'Good'; $rssiColor = 'var(--primary)'; $rssiPct = 70; }
                                            @endphp
                                            <span class="fw-semibold dev-rssi-status" style="color: {{ $rssiColor }}">{{ $rssiLabel }}</span>
                                        </div>
                                        <div class="progress-bar-custom">
                                            <div class="progress-fill dev-rssi-fill" style="width: {{ $rssiPct }}%; background: {{ $rssiColor }};"></div>
                                        </div>
                                    </div>

                                    <!-- Component Diagnostic Panel -->
                                    <div class="border-top pt-3 mt-3">
                                        <span class="text-secondary small fw-semibold uppercase tracking-wider d-block mb-2" style="font-size: 0.75rem;">Component Statuses</span>
                                        <div class="row g-2">
                                            <!-- BME280 Status -->
                                            <div class="col-4">
                                                <div class="p-2 form-control-glass d-flex flex-column align-items-center justify-content-center border-0 text-center" style="border-radius: 8px; background: rgba(100, 116, 139, 0.05); min-height: 52px;">
                                                    <span class="text-secondary small mb-1" style="font-size: 0.6rem; font-weight: 500;">BME280</span>
                                                    <span class="dev-bme-badge">
                                                        @if($device->latest_log->bme_status)
                                                            <span class="badge bg-success text-white px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px;">OK</span>
                                                        @else
                                                            <span class="badge bg-danger text-white px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px; animation: pulse 1.5s infinite;">OFFLINE</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <!-- DHT11 Status -->
                                            <div class="col-4">
                                                <div class="p-2 form-control-glass d-flex flex-column align-items-center justify-content-center border-0 text-center" style="border-radius: 8px; background: rgba(100, 116, 139, 0.05); min-height: 52px;">
                                                    <span class="text-secondary small mb-1" style="font-size: 0.6rem; font-weight: 500;">DHT11</span>
                                                    <span class="dev-dht-badge">
                                                        @if(isset($device->latest_log->dht_status) ? $device->latest_log->dht_status : true)
                                                            <span class="badge bg-success text-white px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px;">OK</span>
                                                        @else
                                                            <span class="badge bg-danger text-white px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px; animation: pulse 1.5s infinite;">OFFLINE</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <!-- Charger State (TP4056 + Solar) -->
                                            <div class="col-4">
                                                <div class="p-2 form-control-glass d-flex flex-column align-items-center justify-content-center border-0 text-center" style="border-radius: 8px; background: rgba(100, 116, 139, 0.05); min-height: 52px;">
                                                    <span class="text-secondary small mb-1" style="font-size: 0.6rem; font-weight: 500;">Solar</span>
                                                    <span class="dev-solar-badge">
                                                        @if($device->latest_log->solar_status === 'charging')
                                                            <span class="badge bg-warning text-dark px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px;"><i class="fa-solid fa-bolt me-0.5" style="font-size: 0.5rem;"></i>CHG</span>
                                                        @elseif($device->latest_log->solar_status === 'full')
                                                            <span class="badge bg-success text-white px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px;"><i class="fa-solid fa-check me-0.5" style="font-size: 0.5rem;"></i>FULL</span>
                                                        @else
                                                            <span class="badge bg-secondary text-white px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px;"><i class="fa-solid fa-moon me-0.5" style="font-size: 0.5rem;"></i>IDLE</span>
                                                        @endif
                                                    </span>
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
                        </div>

                        <!-- Footer actions -->
                        <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted small dev-last-seen">
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
            <div class="d-flex flex-column gap-3" id="live-packet-stream">
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

<!-- Import Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Modern AJAX Polling & Charting Engine -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const liveUrl = "{{ route('dashboard.live') }}";
    
    // Helper to trigger micro-animation flashes on value changes
    function updateAnimatedValue(el, newValue) {
        if (!el) return;
        if (el.innerText !== newValue) {
            el.innerText = newValue;
            el.classList.remove('flash-update');
            void el.offsetWidth; // Trigger reflow to restart animation
            el.classList.add('flash-update');
        }
    }

    // Helper to trigger micro-animation HTML updates
    function updateAnimatedHtml(el, newHtml) {
        if (!el) return;
        if (el.innerHTML !== newHtml) {
            el.innerHTML = newHtml;
            el.classList.remove('flash-update');
            void el.offsetWidth; // Trigger reflow
            el.classList.add('flash-update');
        }
    }

    // 1. Setup Chart.js Telemetry Monitor
    const ctx = document.getElementById('liveTelemetryChart').getContext('2d');
    
    // Neon Red Gradient for Temperature
    const tempGradient = ctx.createLinearGradient(0, 0, 0, 300);
    tempGradient.addColorStop(0, 'rgba(239, 68, 68, 0.2)');
    tempGradient.addColorStop(1, 'rgba(239, 68, 68, 0)');
    
    // Primary Blue Gradient for Humidity
    const humGradient = ctx.createLinearGradient(0, 0, 0, 300);
    humGradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
    humGradient.addColorStop(1, 'rgba(59, 130, 246, 0)');
    
    // Load initial weather packets from blade loop
    const initialLogs = @json($recentLogs->reverse()->values());
    const labels = [];
    const tempData = [];
    const humData = [];
    
    initialLogs.forEach(log => {
        labels.push(new Date(log.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'}));
        tempData.push(log.temperature);
        humData.push(log.humidity);
    });

    let lastTimestamp = initialLogs.length > 0 ? initialLogs[initialLogs.length - 1].created_at : null;

    const liveChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Temperature (°C)',
                    data: tempData,
                    borderColor: '#EF4444',
                    backgroundColor: tempGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#EF4444',
                    pointHoverRadius: 7,
                    yAxisID: 'y'
                },
                {
                    label: 'Humidity (%)',
                    data: humData,
                    borderColor: '#3B82F6',
                    backgroundColor: humGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3B82F6',
                    pointHoverRadius: 7,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: 'rgba(255, 255, 255, 0.7)',
                        font: { family: 'Outfit, Inter, sans-serif', size: 12, weight: '500' }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.04)' },
                    ticks: { color: 'rgba(255, 255, 255, 0.5)', font: { size: 10 } }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: { color: 'rgba(255, 255, 255, 0.04)' },
                    ticks: { color: 'rgba(239, 68, 68, 0.7)', font: { size: 10 } },
                    title: { display: true, text: 'Temperature (°C)', color: 'rgba(239, 68, 68, 0.7)' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { color: 'rgba(59, 130, 246, 0.7)', font: { size: 10 } },
                    title: { display: true, text: 'Humidity (%)', color: 'rgba(59, 130, 246, 0.7)' }
                }
            }
        }
    });

    // 2. Main Live Data Polling Engine
    function fetchLiveData() {
        fetch(liveUrl)
            .then(response => response.json())
            .then(data => {
                const stats = data.stats;
                
                // Update overall aggregated metrics
                updateAnimatedValue(document.getElementById('avg-temp'), stats.avg_temp !== null ? stats.avg_temp + '°C' : '--');
                updateAnimatedValue(document.getElementById('avg-hum'), stats.avg_hum !== null ? stats.avg_hum + '%' : '--');
                updateAnimatedValue(document.getElementById('avg-press'), stats.avg_press !== null ? Math.round(stats.avg_press) + ' hPa' : '--');
                
                const avgBatteryEl = document.getElementById('avg-battery');
                if (avgBatteryEl && stats.avg_battery !== null) {
                    const batVal = parseFloat(stats.avg_battery);
                    let batPct = 0;
                    if (batVal >= 4.2) batPct = 100;
                    else if (batVal <= 3.5) batPct = 0;
                    else batPct = Math.round(((batVal - 3.5) / 0.7) * 100);
                    
                    const newHtml = `${batVal}V <span style="font-size: 0.95rem; font-weight: 500;" class="text-secondary">(${batPct}%)</span>`;
                    updateAnimatedHtml(avgBatteryEl, newHtml);
                } else if (avgBatteryEl) {
                    updateAnimatedValue(avgBatteryEl, '--');
                }

                // Update total registered device count title
                const headerCount = document.querySelector('h3 span');
                if (headerCount) {
                    headerCount.innerText = `Registered Hardware Nodes (${stats.total_devices})`;
                }

                // Update individual device cards
                data.devices.forEach(device => {
                    const card = document.getElementById(`device-card-${device.device_id}`);
                    if (!card) return;

                    // Update online/offline badge
                    const badgeContainer = card.querySelector('.dev-online-badge');
                    if (badgeContainer) {
                        const expectedHtml = device.is_online 
                            ? `<span class="pulse-online"></span><span class="text-secondary small fw-semibold">ONLINE</span>`
                            : `<span class="pulse-offline"></span><span class="text-muted small fw-semibold">OFFLINE</span>`;
                        if (badgeContainer.innerHTML !== expectedHtml) {
                            badgeContainer.innerHTML = expectedHtml;
                        }
                    }

                    // Update telemetry body
                    const log = device.latest_log;
                    const telemetryBody = card.querySelector('.dev-telemetry-body');
                    
                    if (log && telemetryBody) {
                        // Refresh logic if card previously had empty warning
                        if (telemetryBody.querySelector('.text-center.my-5')) {
                            window.location.reload(); 
                            return;
                        }

                        // Temperature
                        const tempEl = card.querySelector('.dev-temp');
                        updateAnimatedValue(tempEl, log.temperature !== null ? log.temperature + '°C' : '--');

                        // Humidity
                        const humEl = card.querySelector('.dev-hum');
                        updateAnimatedValue(humEl, log.humidity !== null ? log.humidity + '%' : '--');

                        // Pressure
                        const pressEl = card.querySelector('.dev-press');
                        updateAnimatedValue(pressEl, log.pressure !== null ? Math.round(log.pressure) + ' hPa' : '--');

                        // Battery
                        const batVal = parseFloat(log.battery) || 0;
                        let batPct = 0;
                        if (batVal >= 4.2) batPct = 100;
                        else if (batVal <= 3.5) batPct = 0;
                        else batPct = Math.round(((batVal - 3.5) / 0.7) * 100);

                        let batColor = 'var(--accent)';
                        if (batPct < 20) batColor = 'var(--danger)';
                        else if (batPct < 50) batColor = 'var(--warning)';

                        const batLabel = card.querySelector('.dev-battery-label');
                        if (batLabel) batLabel.innerText = `Battery (${batVal.toFixed(2)}V)`;

                        const batPctEl = card.querySelector('.dev-battery-pct');
                        updateAnimatedValue(batPctEl, `${batPct}%`);

                        const batFill = card.querySelector('.dev-battery-fill');
                        if (batFill) {
                            batFill.style.width = `${batPct}%`;
                            batFill.style.background = batColor;
                        }

                        // RSSI Signal Strength
                        const rssiVal = parseInt(log.rssi) || 0;
                        let rssiLabel = 'Excellent';
                        let rssiColor = 'var(--accent)';
                        let rssiPct = 100;
                        if (rssiVal <= -90) { rssiLabel = 'Critical'; rssiColor = 'var(--danger)'; rssiPct = 15; }
                        else if (rssiVal <= -80) { rssiLabel = 'Poor'; rssiColor = 'var(--warning)'; rssiPct = 40; }
                        else if (rssiVal <= -70) { rssiLabel = 'Good'; rssiColor = 'var(--primary)'; rssiPct = 70; }

                        const rssiLabelEl = card.querySelector('.dev-rssi-label');
                        if (rssiLabelEl) rssiLabelEl.innerText = `Signal RSSI (${rssiVal} dBm)`;

                        const rssiStatus = card.querySelector('.dev-rssi-status');
                        if (rssiStatus && rssiStatus.innerText !== rssiLabel) {
                            rssiStatus.innerText = rssiLabel;
                            rssiStatus.style.color = rssiColor;
                        }

                        const rssiFill = card.querySelector('.dev-rssi-fill');
                        if (rssiFill) {
                            rssiFill.style.width = `${rssiPct}%`;
                            rssiFill.style.background = rssiColor;
                        }

                        // Component Statuses
                        const bmeBadge = card.querySelector('.dev-bme-badge');
                        if (bmeBadge) {
                            const expectedBme = log.bme_status 
                                ? `<span class="badge bg-success text-white px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px;">OK</span>`
                                : `<span class="badge bg-danger text-white px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px; animation: pulse 1.5s infinite;">OFFLINE</span>`;
                            if (bmeBadge.innerHTML !== expectedBme) bmeBadge.innerHTML = expectedBme;
                        }

                        const dhtBadge = card.querySelector('.dev-dht-badge');
                        if (dhtBadge) {
                            const expectedDht = log.dht_status !== false
                                ? `<span class="badge bg-success text-white px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px;">OK</span>`
                                : `<span class="badge bg-danger text-white px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px; animation: pulse 1.5s infinite;">OFFLINE</span>`;
                            if (dhtBadge.innerHTML !== expectedDht) dhtBadge.innerHTML = expectedDht;
                        }

                        const solarBadge = card.querySelector('.dev-solar-badge');
                        if (solarBadge) {
                            let expectedSolar = '';
                            if (log.solar_status === 'charging') {
                                expectedSolar = `<span class="badge bg-warning text-dark px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px;"><i class="fa-solid fa-bolt me-0.5" style="font-size: 0.5rem;"></i>CHG</span>`;
                            } else if (log.solar_status === 'full') {
                                expectedSolar = `<span class="badge bg-success text-white px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px;"><i class="fa-solid fa-check me-0.5" style="font-size: 0.5rem;"></i>FULL</span>`;
                            } else {
                                expectedSolar = `<span class="badge bg-secondary text-white px-1.5 py-0.5" style="font-size: 0.55rem; border-radius: 4px;"><i class="fa-solid fa-moon me-0.5" style="font-size: 0.5rem;"></i>IDLE</span>`;
                            }
                            if (solarBadge.innerHTML !== expectedSolar) solarBadge.innerHTML = expectedSolar;
                        }
                    }

                    // Update relative timestamp
                    const lastSeenEl = card.querySelector('.dev-last-seen');
                    if (lastSeenEl) {
                        lastSeenEl.innerHTML = `<i class="fa-regular fa-clock me-1"></i> ${device.last_seen_human}`;
                    }
                });

                // Update Live Packet Stream Panel
                const packetStream = document.getElementById('live-packet-stream');
                if (packetStream && data.recentLogs.length > 0) {
                    let htmlContent = '';
                    data.recentLogs.forEach(log => {
                        const tempStr = log.temperature !== null ? log.temperature + '°' : '--';
                        const humStr = log.humidity !== null ? log.humidity + '%' : '--';
                        const batStr = log.battery !== null ? log.battery + 'V' : '--';
                        
                        htmlContent += `
                            <div class="form-control-glass p-3 border-0" style="border-radius: 12px; transition: var(--transition-smooth);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold small text-primary">${log.device_name}</span>
                                    <span class="text-muted" style="font-size: 0.72rem;">${log.created_at_human}</span>
                                </div>
                                <div class="row text-center g-1 text-secondary" style="font-size: 0.8rem;">
                                    <div class="col-4">
                                        <i class="fa-solid fa-temperature-half me-1 text-danger"></i>${tempStr}
                                    </div>
                                    <div class="col-4">
                                        <i class="fa-solid fa-droplet me-1 text-primary"></i>${humStr}
                                    </div>
                                    <div class="col-4">
                                        <i class="fa-solid fa-battery-three-quarters me-1 text-success"></i>${batStr}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    packetStream.innerHTML = htmlContent;
                }

                // Append new points to live scrolling trend chart if new packet received
                if (data.recentLogs.length > 0) {
                    const newestLog = data.recentLogs[0];
                    if (newestLog.created_at !== lastTimestamp) {
                        lastTimestamp = newestLog.created_at;
                        
                        const timeLabel = new Date(newestLog.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'});
                        
                        liveChart.data.labels.push(timeLabel);
                        liveChart.data.datasets[0].data.push(newestLog.temperature);
                        liveChart.data.datasets[1].data.push(newestLog.humidity);
                        
                        // Keep a max viewport of 15 samples rolling dynamically
                        if (liveChart.data.labels.length > 15) {
                            liveChart.data.labels.shift();
                            liveChart.data.datasets[0].data.shift();
                            liveChart.data.datasets[1].data.shift();
                        }
                        
                        liveChart.update('none'); // smooth updates
                    }
                }
            })
            .catch(err => console.error("Error fetching live weather dashboard aggregates: ", err));
    }
    
    // Poll every 3 seconds for aggregates & charts
    setInterval(fetchLiveData, 3000);

    // 3. Setup Force Sync Action Handlers
    const forceSyncBtn = document.getElementById('btn-force-sync');
    const syncIcon = document.getElementById('sync-icon');
    
    if (forceSyncBtn && syncIcon) {
        forceSyncBtn.addEventListener('click', function() {
            syncIcon.classList.add('fa-spin');
            forceSyncBtn.disabled = true;
            forceSyncBtn.querySelector('span').innerText = 'Syncing...';
            
            fetchLiveData();
            
            setTimeout(() => {
                syncIcon.classList.remove('fa-spin');
                forceSyncBtn.disabled = false;
                forceSyncBtn.querySelector('span').innerText = 'Force Sync';
            }, 1000);
        });
    }
});
</script>
@endsection
