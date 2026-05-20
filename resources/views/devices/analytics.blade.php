@extends('layouts.app')

@section('title', 'Telemetry Analytics - Solar IoT Weather Platform')

@section('content')
<!-- Page Header -->
<div class="content-header">
    <div>
        <a href="{{ route('devices.index') }}" class="btn btn-premium-secondary btn-sm py-1.5 px-3 mb-3 d-inline-flex align-items-center gap-1.5" style="border-radius: 8px;">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Devices</span>
        </a>
        <div class="d-flex align-items-center gap-3">
            <h2 class="content-title m-0">{{ $device->device_name }} Analytics</h2>
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
        <p class="content-subtitle">Hardware ID: <code>{{ $device->device_id }}</code></p>
    </div>
    
    <!-- Timeline filter -->
    <div class="glass-panel p-1.5 d-flex gap-1" style="border-radius: 12px;">
        <a href="{{ route('devices.analytics', ['id' => $device->id, 'range' => '24h']) }}" 
           class="btn btn-sm py-1.5 px-3 {{ $range === '24h' ? 'btn-premium-primary' : 'btn-premium-secondary' }}" style="border-radius: 8px;">Last 24 Hours</a>
        <a href="{{ route('devices.analytics', ['id' => $device->id, 'range' => '7d']) }}" 
           class="btn btn-sm py-1.5 px-3 {{ $range === '7d' ? 'btn-premium-primary' : 'btn-premium-secondary' }}" style="border-radius: 8px;">Last 7 Days</a>
        <a href="{{ route('devices.analytics', ['id' => $device->id, 'range' => '30d']) }}" 
           class="btn btn-sm py-1.5 px-3 {{ $range === '30d' ? 'btn-premium-primary' : 'btn-premium-secondary' }}" style="border-radius: 8px;">Last 30 Days</a>
    </div>
</div>

@if(empty($chartData['timestamps']))
    <div class="glass-card p-5 text-center text-muted">
        <i class="fa-solid fa-chart-line mb-3" style="font-size: 3rem; color: var(--text-muted)"></i>
        <h4 class="h5 font-heading text-secondary">No Historical Data Found</h4>
        <p class="small">No logs were transmitted in the selected time range (Last {{ $range }}).</p>
    </div>
@else
    <!-- Real-time current widgets -->
    @if($latestLog)
    <div class="row g-4 mb-4">
        <!-- Temp -->
        <div class="col-6 col-md-3">
            <div class="glass-card p-3 text-center border-danger-subtle">
                <div class="text-secondary small">Latest Temp</div>
                <div class="h4 font-heading fw-bold text-danger mt-1 m-0">{{ $latestLog->temperature }}°C</div>
            </div>
        </div>
        <!-- Humidity -->
        <div class="col-6 col-md-3">
            <div class="glass-card p-3 text-center border-primary-subtle">
                <div class="text-secondary small">Latest Humidity</div>
                <div class="h4 font-heading fw-bold text-primary mt-1 m-0">{{ $latestLog->humidity }}%</div>
            </div>
        </div>
        <!-- Battery -->
        <div class="col-6 col-md-3">
            <div class="glass-card p-3 text-center border-success-subtle">
                <div class="text-secondary small">Latest Battery</div>
                <div class="h4 font-heading fw-bold text-success mt-1 m-0">{{ $latestLog->battery }}V</div>
            </div>
        </div>
        <!-- RSSI -->
        <div class="col-6 col-md-3">
            <div class="glass-card p-3 text-center border-warning-subtle">
                <div class="text-secondary small">Latest RSSI</div>
                <div class="h4 font-heading fw-bold text-warning mt-1 m-0">{{ $latestLog->rssi }} dBm</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Meteorological Telemetry Graphs (ApexCharts) -->
    <div class="row g-4 mb-4">
        <!-- Temperature Graph -->
        <div class="col-md-6 col-12">
            <div class="glass-card p-4">
                <h3 class="h6 font-heading fw-bold mb-3">Temperature Fluctuations (°C)</h3>
                <div id="temperatureChart" style="min-height: 250px;"></div>
            </div>
        </div>

        <!-- Humidity Graph -->
        <div class="col-md-6 col-12">
            <div class="glass-card p-4">
                <h3 class="h6 font-heading fw-bold mb-3">Relative Humidity (%)</h3>
                <div id="humidityChart" style="min-height: 250px;"></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Barometric Pressure Graph -->
        <div class="col-md-6 col-12">
            <div class="glass-card p-4">
                <h3 class="h6 font-heading fw-bold mb-3">Barometric Pressure (hPa)</h3>
                <div id="pressureChart" style="min-height: 250px;"></div>
            </div>
        </div>

        <!-- Solar Battery Drain Graph -->
        <div class="col-md-6 col-12">
            <div class="glass-card p-4">
                <h3 class="h6 font-heading fw-bold mb-3">Solar Charging Battery Voltage (V)</h3>
                <div id="batteryChart" style="min-height: 250px;"></div>
            </div>
        </div>
    </div>

    <!-- Network Signal Graph -->
    <div class="glass-card p-4 mb-4">
        <h3 class="h6 font-heading fw-bold mb-3">WiFi RSSI Strength (dBm)</h3>
        <div id="rssiChart" style="min-height: 200px;"></div>
    </div>
@endif
@endsection

@section('scripts')
@if(!empty($chartData['timestamps']))
    <!-- ApexCharts Javascript -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // General theme colors
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            
            // Standard Chart Config template
            const baseOptions = {
                chart: {
                    type: 'area',
                    height: 250,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    background: 'transparent'
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: {!! json_encode($chartData['timestamps']) !!},
                    labels: {
                        style: { colors: 'var(--text-secondary)' }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: { colors: 'var(--text-secondary)' }
                    }
                },
                grid: {
                    borderColor: 'var(--glass-border)',
                    strokeDashArray: 4
                },
                theme: {
                    mode: isDark ? 'dark' : 'light'
                }
            };

            // 1. Temperature Chart
            const tempOptions = {
                ...baseOptions,
                colors: ['#ef4444'],
                series: [{
                    name: 'Temperature',
                    data: {!! json_encode($chartData['temperature']) !!}
                }],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 95]
                    }
                },
                yaxis: {
                    ...baseOptions.yaxis,
                    title: { text: 'Temperature (°C)', style: { color: 'var(--text-secondary)' } }
                }
            };
            new ApexCharts(document.querySelector("#temperatureChart"), tempOptions).render();

            // 2. Humidity Chart
            const humOptions = {
                ...baseOptions,
                colors: ['#3b82f6'],
                series: [{
                    name: 'Humidity',
                    data: {!! json_encode($chartData['humidity']) !!}
                }],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 95]
                    }
                },
                yaxis: {
                    ...baseOptions.yaxis,
                    title: { text: 'Humidity (%)', style: { color: 'var(--text-secondary)' } }
                }
            };
            new ApexCharts(document.querySelector("#humidityChart"), humOptions).render();

            // 3. Pressure Chart
            const pressOptions = {
                ...baseOptions,
                chart: { ...baseOptions.chart, type: 'line' },
                colors: ['#06b6d4'],
                series: [{
                    name: 'Pressure',
                    data: {!! json_encode($chartData['pressure']) !!}
                }],
                stroke: { curve: 'smooth', width: 4 },
                yaxis: {
                    ...baseOptions.yaxis,
                    title: { text: 'Pressure (hPa)', style: { color: 'var(--text-secondary)' } }
                }
            };
            new ApexCharts(document.querySelector("#pressureChart"), pressOptions).render();

            // 4. Battery Chart
            const batOptions = {
                ...baseOptions,
                chart: { ...baseOptions.chart, type: 'line' },
                colors: ['#10b981'],
                series: [{
                    name: 'Battery Voltage',
                    data: {!! json_encode($chartData['battery']) !!}
                }],
                stroke: { curve: 'smooth', width: 3 },
                yaxis: {
                    ...baseOptions.yaxis,
                    title: { text: 'Voltage (V)', style: { color: 'var(--text-secondary)' } },
                    min: 3.2,
                    max: 4.3
                }
            };
            new ApexCharts(document.querySelector("#batteryChart"), batOptions).render();

            // 5. RSSI Chart
            const rssiOptions = {
                ...baseOptions,
                chart: { ...baseOptions.chart, height: 200, type: 'bar' },
                colors: ['#f59e0b'],
                series: [{
                    name: 'WiFi RSSI',
                    data: {!! json_encode($chartData['rssi']) !!}
                }],
                plotOptions: {
                    bar: { borderRadius: 4, columnWidth: '60%' }
                },
                yaxis: {
                    ...baseOptions.yaxis,
                    title: { text: 'Signal (dBm)', style: { color: 'var(--text-secondary)' } },
                    min: -100,
                    max: -30
                }
            };
            new ApexCharts(document.querySelector("#rssiChart"), rssiOptions).render();

            // Redraw charts on theme change to match background modes
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'data-theme') {
                        const newTheme = document.documentElement.getAttribute('data-theme');
                        ApexCharts.exec('temperatureChart', 'updateOptions', { theme: { mode: newTheme } });
                        ApexCharts.exec('humidityChart', 'updateOptions', { theme: { mode: newTheme } });
                        ApexCharts.exec('pressureChart', 'updateOptions', { theme: { mode: newTheme } });
                        ApexCharts.exec('batteryChart', 'updateOptions', { theme: { mode: newTheme } });
                        ApexCharts.exec('rssiChart', 'updateOptions', { theme: { mode: newTheme } });
                    }
                });
            });
            observer.observe(document.documentElement, { attributes: true });
        });
    </script>
@endif
@endsection
