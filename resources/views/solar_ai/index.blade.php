@extends('layouts.app')

@section('title', 'Solar AI & ML Forecasting Studio - Nitra 99.84 kW Plant')

@section('content')
<style>
.algo-btn {
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    background: var(--sidebar-active);
    color: var(--text-secondary);
    border: 1px solid var(--card-border);
    display: flex;
    align-items: center;
    gap: 8px;
}
.algo-btn:hover {
    color: var(--text-primary);
    border-color: var(--text-muted);
    transform: translateY(-1px);
}
.algo-btn.active {
    background: rgba(168, 85, 247, 0.15) !important;
    border-color: #a855f7 !important;
    color: #a855f7 !important;
    box-shadow: 0 0 12px rgba(168, 85, 247, 0.25);
}
.param-box {
    background: var(--sidebar-active);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    padding: 14px 16px;
    transition: border-color 0.2s ease;
}
.param-box:focus-within {
    border-color: #a855f7 !important;
}
.metric-badge-box {
    background: var(--sidebar-active);
    border: 1px solid var(--card-border);
    border-radius: 14px;
    padding: 18px;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s ease;
}
.metric-badge-box:hover {
    transform: translateY(-2px);
}
.forecast-pill {
    background: var(--sidebar-active);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    padding: 12px 14px;
    text-align: center;
    transition: all 0.2s ease;
}
.forecast-pill:hover {
    border-color: #a855f7;
    transform: translateY(-2px);
}
.training-spin {
    animation: spin-pulse 1s linear infinite;
}
@keyframes spin-pulse {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- Header & Title -->
<div class="content-header">
    <div>
        <h2 class="content-title d-flex flex-wrap align-items-center gap-2">
            <span>Solar AI &amp; Machine Learning Studio</span>
            <span class="badge font-heading" style="font-size: 0.68rem; border-radius: 9999px; padding: 4px 10px; background: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.35); color: #a855f7; font-weight: 600; letter-spacing: 0.02em;">
                <i class="fa-solid fa-brain me-1"></i>99.84 kW NITRO FORECASTING ENGINE
            </span>
        </h2>
        <p class="content-subtitle">
            Trained on <strong>{{ number_format($datasetStats['total_records']) }} real daily plant generation logs</strong> ({{ $datasetStats['earliest_date'] }} &ndash; {{ $datasetStats['latest_date'] }})
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('solar.ai.export') }}" class="btn btn-premium-secondary d-flex align-items-center gap-2" style="border-radius: 10px; padding: 9px 16px;">
            <i class="fa-solid fa-file-csv" style="color: #10b981;"></i>
            <span>Export 1,334 Days CSV</span>
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-premium-secondary d-flex align-items-center gap-2" style="border-radius: 10px; padding: 9px 16px;">
            <i class="fa-solid fa-solar-panel"></i>
            <span>Live Control Center</span>
        </a>
    </div>
</div>

<!-- Dataset Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 text-center" style="border-top: 3px solid #3b82f6 !important;">
            <div class="text-secondary small">Total Days Logged</div>
            <div class="h4 font-heading fw-bold mt-1 mb-0 text-primary">{{ number_format($datasetStats['total_records']) }}</div>
            <span class="text-muted small" style="font-size: 0.7rem;">Continuous 2022&ndash;2026</span>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 text-center" style="border-top: 3px solid #f59e0b !important;">
            <div class="text-secondary small">Total Generation</div>
            <div class="h4 font-heading fw-bold mt-1 mb-0" style="color: #f59e0b;">{{ number_format($datasetStats['total_generation_kwh'] / 1000, 1) }} MWh</div>
            <span class="text-muted small" style="font-size: 0.7rem;">{{ number_format($datasetStats['total_generation_kwh']) }} kWh</span>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 text-center" style="border-top: 3px solid #06b6d4 !important;">
            <div class="text-secondary small">Daily Avg Yield</div>
            <div class="h4 font-heading fw-bold mt-1 mb-0" style="color: #06b6d4;">{{ number_format($datasetStats['avg_daily_kwh'], 1) }}</div>
            <span class="text-muted small" style="font-size: 0.7rem;">kWh per day</span>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 text-center" style="border-top: 3px solid #f43f5e !important;">
            <div class="text-secondary small">Peak Daily Output</div>
            <div class="h4 font-heading fw-bold mt-1 mb-0" style="color: #f43f5e;">{{ number_format($datasetStats['max_generation_kwh'], 1) }}</div>
            <span class="text-muted small" style="font-size: 0.7rem;">kWh single-day record</span>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 text-center" style="border-top: 3px solid #10b981 !important;">
            <div class="text-secondary small">Avoided CO₂</div>
            <div class="h4 font-heading fw-bold mt-1 mb-0" style="color: #10b981;">{{ number_format($datasetStats['total_co2_tonnes'], 1) }} T</div>
            <span class="text-muted small" style="font-size: 0.7rem;">Clean solar energy</span>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 text-center" style="border-top: 3px solid #a855f7 !important;">
            <div class="text-secondary small">Plant Capacity</div>
            <div class="h4 font-heading fw-bold mt-1 mb-0" style="color: #a855f7;">99.84 kWp</div>
            <span class="text-muted small" style="font-size: 0.7rem;">Nitra Rooftop Array</span>
        </div>
    </div>
</div>

<!-- Main Interactive AI Training & Forecasting Lab -->
<div class="row g-4 mb-4">
    <!-- Left Column: Algorithm Selector & Hyperparameter Tuning -->
    <div class="col-12 col-xl-5">
        <div class="glass-card p-4 h-100" style="border-left: 4px solid #a855f7 !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h5 font-heading fw-bold m-0 d-flex align-items-center gap-2 text-primary">
                    <i class="fa-solid fa-sliders" style="color: #a855f7;"></i>
                    <span>Model &amp; Hyperparameters</span>
                </h3>
                <span class="badge px-2.5 py-1 small" style="background: rgba(168, 85, 247, 0.12); color: #a855f7; border-radius: 9999px; font-size: 0.68rem; font-weight: 600;">
                    LIVE TRAINING
                </span>
            </div>

            <p class="text-secondary small mb-3">Select a forecasting architecture and adjust tuning parameters to train on the 1,334-day solar time series.</p>

            <!-- Algorithm Selector Grid -->
            <label class="text-secondary small fw-semibold uppercase tracking-wider mb-2 d-block" style="font-size: 0.72rem;">Select Algorithm</label>
            <div class="d-grid gap-2 mb-4" style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));">
                <button type="button" class="algo-btn active" data-algo="random_forest">
                    <i class="fa-solid fa-tree text-success"></i>
                    <span>Random Forest</span>
                </button>
                <button type="button" class="algo-btn" data-algo="svr">
                    <i class="fa-solid fa-bolt text-warning"></i>
                    <span>SVR (Kernel)</span>
                </button>
                <button type="button" class="algo-btn" data-algo="vanilla_lstm">
                    <i class="fa-solid fa-brain" style="color: #0ea5e9;"></i>
                    <span>Vanilla LSTM</span>
                </button>
                <button type="button" class="algo-btn" data-algo="stacked_lstm">
                    <i class="fa-solid fa-layer-group text-primary"></i>
                    <span>Stacked LSTM</span>
                </button>
                <button type="button" class="algo-btn" data-algo="bidirectional_lstm">
                    <i class="fa-solid fa-arrows-left-right text-info"></i>
                    <span>Bi-LSTM</span>
                </button>
                <button type="button" class="algo-btn" data-algo="arima">
                    <i class="fa-solid fa-chart-area text-danger"></i>
                    <span>ARIMA (Time)</span>
                </button>
                <button type="button" class="algo-btn" data-algo="linear_regression">
                    <i class="fa-solid fa-chart-line text-secondary"></i>
                    <span>Linear Reg.</span>
                </button>
                <button type="button" class="algo-btn" data-algo="q_learning">
                    <i class="fa-solid fa-gamepad text-purple" style="color: #a855f7;"></i>
                    <span>Q-Learning (RL)</span>
                </button>
            </div>

            <!-- Dynamic Parameter Sliders & Inputs -->
            <div class="mb-4">
                <label class="text-secondary small fw-semibold uppercase tracking-wider mb-2 d-block" style="font-size: 0.72rem;">Training &amp; Architecture Parameters</label>
                
                <div class="row g-3">
                    <!-- Train/Test Split Ratio -->
                    <div class="col-12">
                        <div class="param-box">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-secondary small fw-medium">Train / Test Split Ratio</span>
                                <span class="fw-bold text-primary font-monospace" id="label-split-ratio">80% / 20%</span>
                            </div>
                            <input type="range" class="form-range" id="param-split-ratio" min="0.5" max="0.9" step="0.05" value="0.8">
                            <div class="d-flex justify-content-between text-muted small" style="font-size: 0.65rem;">
                                <span>50/50</span>
                                <span>70/30</span>
                                <span>80/20 (Optimal)</span>
                                <span>90/10</span>
                            </div>
                        </div>
                    </div>

                    <!-- Estimators (Trees) -->
                    <div class="col-6 param-field param-rf">
                        <div class="param-box">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-secondary small fw-medium">Estimators</span>
                                <span class="fw-bold text-primary font-monospace" id="label-estimators">100</span>
                            </div>
                            <input type="range" class="form-range" id="param-estimators" min="10" max="200" step="10" value="100">
                        </div>
                    </div>

                    <!-- Tree Depth -->
                    <div class="col-6 param-field param-rf">
                        <div class="param-box">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-secondary small fw-medium">Max Depth</span>
                                <span class="fw-bold text-primary font-monospace" id="label-tree-depth">12</span>
                            </div>
                            <input type="range" class="form-range" id="param-tree-depth" min="2" max="25" step="1" value="12">
                        </div>
                    </div>

                    <!-- LSTM Units -->
                    <div class="col-6 param-field param-lstm">
                        <div class="param-box">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-secondary small fw-medium">LSTM Units</span>
                                <span class="fw-bold text-primary font-monospace" id="label-lstm-units">64</span>
                            </div>
                            <input type="range" class="form-range" id="param-lstm-units" min="16" max="256" step="16" value="64">
                        </div>
                    </div>

                    <!-- Epochs -->
                    <div class="col-6 param-field param-lstm param-rl">
                        <div class="param-box">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-secondary small fw-medium">Epochs</span>
                                <span class="fw-bold text-primary font-monospace" id="label-epochs">50</span>
                            </div>
                            <input type="range" class="form-range" id="param-epochs" min="10" max="150" step="5" value="50">
                        </div>
                    </div>

                    <!-- Batch Size -->
                    <div class="col-6 param-field param-lstm">
                        <div class="param-box">
                            <span class="text-secondary small fw-medium d-block mb-1">Batch Size</span>
                            <select class="form-select form-select-sm" id="param-batch-size" style="background: var(--card-bg); color: var(--text-primary); border-color: var(--card-border);">
                                <option value="8">8 samples</option>
                                <option value="16">16 samples</option>
                                <option value="32" selected>32 samples (Standard)</option>
                                <option value="64">64 samples</option>
                            </select>
                        </div>
                    </div>

                    <!-- Learning Rate -->
                    <div class="col-6 param-field param-lstm param-rl">
                        <div class="param-box">
                            <span class="text-secondary small fw-medium d-block mb-1">Learning Rate (&alpha;)</span>
                            <select class="form-select form-select-sm" id="param-learning-rate" style="background: var(--card-bg); color: var(--text-primary); border-color: var(--card-border);">
                                <option value="0.0005">0.0005 (Fine)</option>
                                <option value="0.001" selected>0.001 (Adam Default)</option>
                                <option value="0.005">0.005</option>
                                <option value="0.01">0.01</option>
                                <option value="0.05">0.05</option>
                            </select>
                        </div>
                    </div>

                    <!-- SVR Parameters (C & Epsilon) -->
                    <div class="col-6 param-field param-svr" style="display: none;">
                        <div class="param-box">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-secondary small fw-medium">C Regularizer</span>
                                <span class="fw-bold text-primary font-monospace" id="label-svr-c">10.0</span>
                            </div>
                            <input type="range" class="form-range" id="param-svr-c" min="1" max="50" step="1" value="10">
                        </div>
                    </div>
                    <div class="col-6 param-field param-svr" style="display: none;">
                        <div class="param-box">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-secondary small fw-medium">Epsilon (&epsilon;) Tube</span>
                                <span class="fw-bold text-primary font-monospace" id="label-svr-eps">0.10</span>
                            </div>
                            <input type="range" class="form-range" id="param-svr-eps" min="0.01" max="0.50" step="0.01" value="0.10">
                        </div>
                    </div>

                    <!-- ARIMA Parameters (p, d, q) -->
                    <div class="col-12 param-field param-arima" style="display: none;">
                        <div class="param-box">
                            <div class="row text-center g-2">
                                <div class="col-4">
                                    <span class="text-muted small">p (AR)</span>
                                    <input type="number" class="form-control form-control-sm text-center" id="param-arima-p" value="3" min="1" max="7" style="background: var(--card-bg); color: var(--text-primary); border-color: var(--card-border);">
                                </div>
                                <div class="col-4">
                                    <span class="text-muted small">d (Diff)</span>
                                    <input type="number" class="form-control form-control-sm text-center" id="param-arima-d" value="1" min="0" max="2" style="background: var(--card-bg); color: var(--text-primary); border-color: var(--card-border);">
                                </div>
                                <div class="col-4">
                                    <span class="text-muted small">q (MA)</span>
                                    <input type="number" class="form-control form-control-sm text-center" id="param-arima-q" value="2" min="0" max="5" style="background: var(--card-bg); color: var(--text-primary); border-color: var(--card-border);">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Q-Learning Parameters -->
                    <div class="col-12 param-field param-rl" style="display: none;">
                        <div class="param-box">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-secondary small fw-medium">Discount Factor (&gamma;)</span>
                                <span class="fw-bold text-primary font-monospace" id="label-discount">0.90</span>
                            </div>
                            <input type="range" class="form-range" id="param-discount" min="0.5" max="0.99" step="0.01" value="0.90">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Train Model Action Button -->
            <button type="button" class="btn btn-premium-primary w-100 py-3 d-flex align-items-center justify-content-center gap-2" id="btn-train-model" style="border-radius: 12px; font-size: 0.95rem; font-weight: 600;">
                <i class="fa-solid fa-bolt" id="btn-train-icon"></i>
                <span id="btn-train-text">Train Model &amp; Forecast</span>
            </button>
        </div>
    </div>

    <!-- Right Column: Model Metrics & Actual vs Predicted Visual Chart -->
    <div class="col-12 col-xl-7">
        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
            <!-- Selected Model Title & Live Metrics Header -->
            <div>
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <span class="text-muted small" style="font-size: 0.75rem;">CURRENT ACTIVE MODEL</span>
                        <h4 class="h5 font-heading fw-bold m-0 text-primary" id="active-model-title">
                            {{ $initialResult['algorithm_name'] }}
                        </h4>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge px-3 py-1.5 small font-monospace" id="badge-train-time" style="background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 8px;">
                            <i class="fa-solid fa-stopwatch me-1"></i>{{ $initialResult['metrics']['train_time_ms'] }} ms
                        </span>
                        <span class="badge px-3 py-1.5 small font-monospace" id="badge-split-summary" style="background: var(--sidebar-active); border: 1px solid var(--card-border); color: var(--text-secondary); border-radius: 8px;">
                            Train: {{ $initialResult['metrics']['train_count'] }} | Test: {{ $initialResult['metrics']['test_count'] }}
                        </span>
                    </div>
                </div>

                <!-- 4 Core Evaluation Metrics Cards -->
                <div class="row g-2 mb-4 text-center">
                    <!-- R2 Score -->
                    <div class="col-6 col-md-3">
                        <div class="metric-badge-box" style="border-top: 3px solid #10b981 !important;">
                            <span class="text-secondary small fw-medium" style="font-size: 0.72rem;">R² Score (Fit)</span>
                            <div class="h3 font-heading fw-bold m-0 mt-1" style="color: #10b981;" id="metric-r2">
                                {{ number_format($initialResult['metrics']['r2'], 4) }}
                            </div>
                            <span class="text-muted small" style="font-size: 0.65rem;">Coefficient of Det.</span>
                        </div>
                    </div>
                    <!-- RMSE -->
                    <div class="col-6 col-md-3">
                        <div class="metric-badge-box" style="border-top: 3px solid #f43f5e !important;">
                            <span class="text-secondary small fw-medium" style="font-size: 0.72rem;">RMSE Error</span>
                            <div class="h3 font-heading fw-bold m-0 mt-1" style="color: #f43f5e;" id="metric-rmse">
                                {{ number_format($initialResult['metrics']['rmse'], 2) }}
                            </div>
                            <span class="text-muted small" style="font-size: 0.65rem;">kWh Root Mean Sq.</span>
                        </div>
                    </div>
                    <!-- MAE -->
                    <div class="col-6 col-md-3">
                        <div class="metric-badge-box" style="border-top: 3px solid #0ea5e9 !important;">
                            <span class="text-secondary small fw-medium" style="font-size: 0.72rem;">MAE Error</span>
                            <div class="h3 font-heading fw-bold m-0 mt-1" style="color: #0ea5e9;" id="metric-mae">
                                {{ number_format($initialResult['metrics']['mae'], 2) }}
                            </div>
                            <span class="text-muted small" style="font-size: 0.65rem;">kWh Mean Absolute</span>
                        </div>
                    </div>
                    <!-- MAPE -->
                    <div class="col-6 col-md-3">
                        <div class="metric-badge-box" style="border-top: 3px solid #f59e0b !important;">
                            <span class="text-secondary small fw-medium" style="font-size: 0.72rem;">MAPE Error</span>
                            <div class="h3 font-heading fw-bold m-0 mt-1" style="color: #f59e0b;" id="metric-mape">
                                {{ number_format($initialResult['metrics']['mape'], 1) }}%
                            </div>
                            <span class="text-muted small" style="font-size: 0.65rem;">Mean Percentage Err.</span>
                        </div>
                    </div>
                </div>

                <!-- Actual vs Predicted Line Chart Container -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-semibold uppercase tracking-wider" style="font-size: 0.75rem;">
                            Test Split Evaluation: Actual Generation vs. Model Predictions
                        </span>
                        <span class="text-muted small font-monospace" style="font-size: 0.7rem;">99.84 kWp Daily Array</span>
                    </div>
                    <div style="height: 320px; position: relative;" id="testPredictionChart"></div>
                </div>
            </div>

            <!-- Footer Legend Cue -->
            <div class="border-top pt-2 mt-2 d-flex justify-content-between text-muted small" style="font-size: 0.72rem; border-color: var(--card-border) !important;">
                <span><i class="fa-solid fa-circle me-1" style="color: #10b981; font-size: 0.55rem;"></i>Actual Measured Solar kWh</span>
                <span><i class="fa-solid fa-circle me-1" style="color: #a855f7; font-size: 0.55rem;"></i>AI Model Predicted kWh</span>
            </div>
        </div>
    </div>
</div>

<!-- 7-Day & 30-Day Future Projection Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="glass-card p-4" style="border-left: 4px solid #06b6d4 !important;">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h3 class="h5 font-heading fw-bold m-0 d-flex align-items-center gap-2 text-primary">
                        <i class="fa-solid fa-calendar-days" style="color: #06b6d4;"></i>
                        <span>Upcoming Solar Generation Projections</span>
                    </h3>
                    <p class="text-muted small m-0">Predicted daily output and specific yield calculated by the active AI model</p>
                </div>
                <div>
                    <span class="badge px-3 py-1.5 small" style="background: rgba(6, 182, 212, 0.12); color: #06b6d4; border: 1px solid rgba(6, 182, 212, 0.25); border-radius: 9999px; font-weight: 600;">
                        30-DAY FORWARD HORIZON
                    </span>
                </div>
            </div>

            <!-- Next 7 Days Cards Carousel/Grid -->
            <div class="row g-2 mb-4" id="future-forecast-grid">
                @foreach(array_slice($initialResult['future_forecast'], 0, 7) as $f)
                    <div class="col-6 col-md-4 col-lg-3 col-xl">
                        <div class="forecast-pill">
                            <span class="badge px-2 py-0.5 mb-1" style="background: rgba(168, 85, 247, 0.12); color: #a855f7; font-size: 0.65rem;">{{ $f['day'] }} &bull; {{ $f['date_formatted'] }}</span>
                            <div class="h4 font-heading fw-bold m-0" style="color: #06b6d4;">{{ number_format($f['predicted_kwh'], 1) }}</div>
                            <span class="text-muted small" style="font-size: 0.65rem;">kWh predicted</span>
                            <div class="border-top pt-1 mt-1 d-flex justify-content-between text-secondary" style="font-size: 0.68rem; border-color: var(--card-border) !important;">
                                <span>SY: {{ $f['specific_yield'] }}</span>
                                <span>CUF: {{ $f['cuf_percent'] }}%</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- All-Model Benchmark Comparison Leaderboard -->
<div class="row g-4 mb-4">
    <div class="col-12 col-xl-8">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h5 font-heading fw-bold m-0 d-flex align-items-center gap-2 text-primary">
                    <i class="fa-solid fa-trophy" style="color: #f59e0b;"></i>
                    <span>Model Performance Leaderboard</span>
                </h3>
                <span class="badge px-3 py-1 small" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 9999px; font-weight: 600;">
                    8 ALGORITHMS EVALUATED
                </span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="color: var(--text-primary);">
                    <thead>
                        <tr class="text-secondary small font-monospace" style="border-bottom: 2px solid var(--card-border); font-size: 0.72rem;">
                            <th>RANK &amp; MODEL</th>
                            <th>TYPE</th>
                            <th class="text-center">R² SCORE</th>
                            <th class="text-center">RMSE (kWh)</th>
                            <th class="text-center">MAE (kWh)</th>
                            <th class="text-center">MAPE (%)</th>
                            <th class="text-end">TRAIN TIME</th>
                        </tr>
                    </thead>
                    <tbody id="benchmark-table-body">
                        @foreach($initialResult['benchmark'] as $idx => $b)
                            <tr style="border-bottom: 1px solid var(--card-border); font-size: 0.85rem;">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle p-1" style="width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; background: {{ $idx === 0 ? '#f59e0b' : ($idx === 1 ? '#94a3b8' : ($idx === 2 ? '#b45309' : 'var(--sidebar-active)')) }}; color: #fff; font-size: 0.65rem;">
                                            {{ $idx + 1 }}
                                        </span>
                                        <span class="fw-bold">{{ $b['name'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge form-control-glass text-secondary small px-2 py-0.5" style="font-size: 0.68rem;">{{ $b['type'] }}</span>
                                </td>
                                <td class="text-center fw-bold font-monospace" style="color: {{ $b['r2'] > 0.85 ? '#10b981' : ($b['r2'] > 0.70 ? '#3b82f6' : '#f59e0b') }};">
                                    {{ number_format($b['r2'], 4) }}
                                </td>
                                <td class="text-center font-monospace">{{ number_format($b['rmse'], 2) }}</td>
                                <td class="text-center font-monospace">{{ number_format($b['mae'], 2) }}</td>
                                <td class="text-center font-monospace">{{ number_format($b['mape'], 1) }}%</td>
                                <td class="text-end text-muted font-monospace small">{{ $b['latency_ms'] }} ms</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Recent Actual Solar Plant Records Preview -->
    <div class="col-12 col-xl-4">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h5 font-heading fw-bold m-0 d-flex align-items-center gap-2 text-primary">
                    <i class="fa-solid fa-database" style="color: #10b981;"></i>
                    <span>Real Plant Records</span>
                </h3>
                <a href="{{ route('solar.ai.export') }}" class="btn btn-premium-secondary btn-sm py-1 px-2.5 text-success" style="border-radius: 8px; font-size: 0.75rem;">
                    <i class="fa-solid fa-download me-1"></i>CSV
                </a>
            </div>

            <p class="text-muted small mb-3" style="font-size: 0.75rem;">Showing latest 15 logged generation days from the 99.84 kW Nitra RTU664 array.</p>

            <div style="max-height: 400px; overflow-y: auto;">
                <div class="d-flex flex-column gap-2">
                    @foreach($recentRecords as $rec)
                        <div class="p-2.5 rounded-3 form-control-glass border-0" style="background: var(--sidebar-active); border: 1px solid var(--card-border) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold small text-primary">{{ $rec->record_date->format('M d, Y') }}</span>
                                <span class="fw-bold text-success font-monospace" style="font-size: 0.85rem;">{{ number_format($rec->generation_kwh, 1) }} kWh</span>
                            </div>
                            <div class="d-flex justify-content-between text-secondary" style="font-size: 0.7rem;">
                                <span>Specific Yield: <strong class="text-primary">{{ $rec->specific_yield ?? round($rec->generation_kwh / 99.84, 2) }}</strong></span>
                                <span>CO₂: <strong class="text-primary">{{ $rec->co2_tonnes }} T</strong></span>
                                <span>CUF: <strong class="text-primary">{{ $rec->cuf_percent }}%</strong></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import ApexCharts for Rich Interactive Visuals -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const trainUrl = "{{ route('solar.ai.train') }}";
    let activeAlgorithm = 'random_forest';
    let predictionChart = null;

    // Theme detection
    function isDarkTheme() {
        return document.documentElement.getAttribute('data-theme') === 'dark';
    }

    // Sync input sliders with value label spans
    function syncSlider(sliderId, labelId, suffix = '') {
        const slider = document.getElementById(sliderId);
        const label = document.getElementById(labelId);
        if (slider && label) {
            slider.addEventListener('input', function() {
                label.innerText = this.value + suffix;
            });
        }
    }

    syncSlider('param-split-ratio', 'label-split-ratio', '');
    document.getElementById('param-split-ratio').addEventListener('input', function() {
        const trainPct = Math.round(this.value * 100);
        const testPct = 100 - trainPct;
        document.getElementById('label-split-ratio').innerText = `${trainPct}% / ${testPct}%`;
    });

    syncSlider('param-estimators', 'label-estimators');
    syncSlider('param-tree-depth', 'label-tree-depth');
    syncSlider('param-lstm-units', 'label-lstm-units');
    syncSlider('param-epochs', 'label-epochs');
    syncSlider('param-svr-c', 'label-svr-c');
    syncSlider('param-svr-eps', 'label-svr-eps');
    syncSlider('param-discount', 'label-discount');

    // Algorithm selection handler
    const algoButtons = document.querySelectorAll('.algo-btn');
    algoButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            algoButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activeAlgorithm = this.getAttribute('data-algo');
            updateVisibleParameterFields(activeAlgorithm);
        });
    });

    function updateVisibleParameterFields(algo) {
        document.querySelectorAll('.param-field').forEach(el => el.style.display = 'none');

        if (algo === 'random_forest') {
            document.querySelectorAll('.param-rf').forEach(el => el.style.display = 'block');
        } else if (algo === 'svr') {
            document.querySelectorAll('.param-svr').forEach(el => el.style.display = 'block');
        } else if (algo.includes('lstm')) {
            document.querySelectorAll('.param-lstm').forEach(el => el.style.display = 'block');
        } else if (algo === 'arima') {
            document.querySelectorAll('.param-arima').forEach(el => el.style.display = 'block');
        } else if (algo === 'q_learning') {
            document.querySelectorAll('.param-rl').forEach(el => el.style.display = 'block');
        }
    }

    // Initialize ApexCharts for Actual vs Predicted
    const initialPoints = @json($initialResult['test_results']);
    renderChart(initialPoints);

    function renderChart(points) {
        const categories = points.map(p => p.date);
        const actualData = points.map(p => p.actual);
        const predData = points.map(p => p.predicted);

        const options = {
            series: [
                {
                    name: 'Actual Measured kWh',
                    data: actualData,
                },
                {
                    name: 'AI Model Predicted kWh',
                    data: predData,
                }
            ],
            chart: {
                height: 320,
                type: 'line',
                background: 'transparent',
                toolbar: {
                    show: true,
                    tools: { download: true, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true }
                },
                animations: { enabled: true, easing: 'easeinout', speed: 600 }
            },
            colors: ['#10b981', '#a855f7'],
            stroke: {
                curve: 'smooth',
                width: [2.5, 2]
            },
            dataLabels: { enabled: false },
            grid: {
                borderColor: isDarkTheme() ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.06)',
            },
            xaxis: {
                categories: categories,
                labels: {
                    show: true,
                    style: { colors: isDarkTheme() ? '#a1a1aa' : '#71717a', fontSize: '10px' },
                    rotate: -45,
                    rotateAlways: false,
                    hideOverlappingLabels: true,
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                title: {
                    text: 'Generation (kWh)',
                    style: { color: isDarkTheme() ? '#a1a1aa' : '#71717a', fontSize: '11px', fontWeight: 600 }
                },
                labels: {
                    style: { colors: isDarkTheme() ? '#a1a1aa' : '#71717a', fontSize: '10px' }
                },
                min: 0,
                max: 520
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                labels: { colors: isDarkTheme() ? '#fafafa' : '#09090b' },
                markers: { radius: 12 }
            },
            tooltip: {
                theme: isDarkTheme() ? 'dark' : 'light',
                y: {
                    formatter: function(val) {
                        return val.toFixed(2) + ' kWh';
                    }
                }
            }
        };

        if (predictionChart) {
            predictionChart.destroy();
        }
        predictionChart = new ApexCharts(document.querySelector("#testPredictionChart"), options);
        predictionChart.render();
    }

    // Train button handler
    const trainBtn = document.getElementById('btn-train-model');
    const trainIcon = document.getElementById('btn-train-icon');
    const trainText = document.getElementById('btn-train-text');

    trainBtn.addEventListener('click', function() {
        trainBtn.disabled = true;
        trainIcon.className = 'fa-solid fa-arrows-rotate training-spin';
        trainText.innerText = 'Training Model...';

        const payload = {
            _token: '{{ csrf_token() }}',
            algorithm: activeAlgorithm,
            split_ratio: document.getElementById('param-split-ratio').value,
            estimators: document.getElementById('param-estimators').value,
            tree_depth: document.getElementById('param-tree-depth').value,
            lstm_units: document.getElementById('param-lstm-units').value,
            epochs: document.getElementById('param-epochs').value,
            batch_size: document.getElementById('param-batch-size').value,
            learning_rate: document.getElementById('param-learning-rate').value,
            c: document.getElementById('param-svr-c').value,
            epsilon: document.getElementById('param-svr-eps').value,
            p: document.getElementById('param-arima-p').value,
            d: document.getElementById('param-arima-d').value,
            q: document.getElementById('param-arima-q').value,
            discount_factor: document.getElementById('param-discount').value,
        };

        fetch(trainUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update header info
                document.getElementById('active-model-title').innerText = data.algorithm_name;
                document.getElementById('badge-train-time').innerHTML = `<i class="fa-solid fa-stopwatch me-1"></i>${data.metrics.train_time_ms} ms`;
                document.getElementById('badge-split-summary').innerText = `Train: ${data.metrics.train_count} | Test: ${data.metrics.test_count}`;

                // Update metric cards
                document.getElementById('metric-r2').innerText = data.metrics.r2.toFixed(4);
                document.getElementById('metric-rmse').innerText = data.metrics.rmse.toFixed(2);
                document.getElementById('metric-mae').innerText = data.metrics.mae.toFixed(2);
                document.getElementById('metric-mape').innerText = data.metrics.mape.toFixed(1) + '%';

                // Re-render chart with new predictions
                renderChart(data.test_results);

                // Update future forecast 7-day row
                if (data.future_forecast && data.future_forecast.length >= 7) {
                    const grid = document.getElementById('future-forecast-grid');
                    let pills = '';
                    data.future_forecast.slice(0, 7).forEach(f => {
                        pills += `
                            <div class="col-6 col-md-4 col-lg-3 col-xl">
                                <div class="forecast-pill">
                                    <span class="badge px-2 py-0.5 mb-1" style="background: rgba(168, 85, 247, 0.12); color: #a855f7; font-size: 0.65rem;">${f.day} &bull; ${f.date_formatted}</span>
                                    <div class="h4 font-heading fw-bold m-0" style="color: #06b6d4;">${parseFloat(f.predicted_kwh).toFixed(1)}</div>
                                    <span class="text-muted small" style="font-size: 0.65rem;">kWh predicted</span>
                                    <div class="border-top pt-1 mt-1 d-flex justify-content-between text-secondary" style="font-size: 0.68rem; border-color: var(--card-border) !important;">
                                        <span>SY: ${f.specific_yield}</span>
                                        <span>CUF: ${f.cuf_percent}%</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    grid.innerHTML = pills;
                }

                // Update benchmark table
                if (data.benchmark && data.benchmark.length > 0) {
                    const tbody = document.getElementById('benchmark-table-body');
                    let rows = '';
                    data.benchmark.forEach((b, idx) => {
                        const rankColor = idx === 0 ? '#f59e0b' : (idx === 1 ? '#94a3b8' : (idx === 2 ? '#b45309' : 'var(--sidebar-active)'));
                        const r2Color = b.r2 > 0.85 ? '#10b981' : (b.r2 > 0.70 ? '#3b82f6' : '#f59e0b');
                        rows += `
                            <tr style="border-bottom: 1px solid var(--card-border); font-size: 0.85rem;">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle p-1" style="width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; background: ${rankColor}; color: #fff; font-size: 0.65rem;">
                                            ${idx + 1}
                                        </span>
                                        <span class="fw-bold">${b.name}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge form-control-glass text-secondary small px-2 py-0.5" style="font-size: 0.68rem;">${b.type}</span>
                                </td>
                                <td class="text-center fw-bold font-monospace" style="color: ${r2Color};">
                                    ${parseFloat(b.r2).toFixed(4)}
                                </td>
                                <td class="text-center font-monospace">${parseFloat(b.rmse).toFixed(2)}</td>
                                <td class="text-center font-monospace">${parseFloat(b.mae).toFixed(2)}</td>
                                <td class="text-center font-monospace">${parseFloat(b.mape).toFixed(1)}%</td>
                                <td class="text-end text-muted font-monospace small">${b.latency_ms} ms</td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = rows;
                }
            }
        })
        .catch(err => {
            console.error("Training error: ", err);
            alert("Error running model training. Please check parameters.");
        })
        .finally(() => {
            trainBtn.disabled = false;
            trainIcon.className = 'fa-solid fa-bolt';
            trainText.innerText = 'Train Model & Forecast';
        });
    });

    // Dark/Light theme changes observer to repaint chart theme
    const themeObserver = new MutationObserver(() => {
        if (predictionChart) {
            predictionChart.updateOptions({
                grid: { borderColor: isDarkTheme() ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.06)' },
                xaxis: { labels: { style: { colors: isDarkTheme() ? '#a1a1aa' : '#71717a' } } },
                yaxis: {
                    title: { style: { color: isDarkTheme() ? '#a1a1aa' : '#71717a' } },
                    labels: { style: { colors: isDarkTheme() ? '#a1a1aa' : '#71717a' } }
                },
                legend: { labels: { colors: isDarkTheme() ? '#fafafa' : '#09090b' } },
                tooltip: { theme: isDarkTheme() ? 'dark' : 'light' }
            });
        }
    });
    themeObserver.observe(document.documentElement, { attributes: true });
});
</script>
@endsection
