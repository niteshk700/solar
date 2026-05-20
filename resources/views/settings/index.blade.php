@extends('layouts.app')

@section('title', 'Platform Settings - Solar IoT Weather Platform')

@section('content')
<!-- Page Header -->
<div class="content-header">
    <div>
        <h2 class="content-title">System Settings</h2>
        <p class="content-subtitle">Manage administrative credentials, API access tokens, and core configurations</p>
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
            <span class="fw-bold">Errors Occurred</span>
        </div>
        <ul class="mb-0 small ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Dynamic API token single-reveal banner -->
@if(session('plain_token'))
    <div class="glass-panel p-4 mb-4 border-primary" style="background: rgba(99, 102, 241, 0.08);">
        <div class="d-flex align-items-center gap-2 text-primary mb-3">
            <i class="fa-solid fa-key" style="font-size: 1.2rem;"></i>
            <h4 class="h6 font-heading fw-bold mb-0">New Admin API Token Generated</h4>
        </div>
        <p class="small text-secondary mb-3">Please copy this token now. For your security, this token will only be displayed once and cannot be retrieved later.</p>
        
        <div class="d-flex gap-2 w-100 max-width-500">
            <input type="text" class="form-control form-control-glass font-monospace text-primary fw-bold" 
                   value="{{ session('plain_token') }}" readonly id="plainTokenField">
            <button class="btn btn-premium-primary" onclick="copyPlainToken()">
                <i class="fa-regular fa-copy"></i>
            </button>
        </div>
    </div>
@endif

<div class="row">
    <!-- Part 1: Security and Password settings -->
    <div class="col-lg-5 col-12 mb-4">
        <div class="glass-card p-4 h-100">
            <h3 class="h6 font-heading fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-shield-halved text-primary"></i>
                <span>Administrator Security</span>
            </h3>
            <p class="small text-secondary mb-4">Update the account password for administrative access to the platform.</p>

            <form action="{{ route('settings.password') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="current_password" class="form-label text-secondary small fw-semibold">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control form-control-glass" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label text-secondary small fw-semibold">New Password</label>
                    <input type="password" name="password" id="password" class="form-control form-control-glass" placeholder="At least 8 characters" required>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label text-secondary small fw-semibold">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-glass" required>
                </div>

                <button type="submit" class="btn btn-premium-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                    <i class="fa-solid fa-user-lock"></i>
                    <span>Update Security Password</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Part 2: Administrative API access keys -->
    <div class="col-lg-7 col-12 mb-4">
        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
            <div>
                <h3 class="h6 font-heading fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-key text-primary"></i>
                    <span>Administrative API Tokens</span>
                </h3>
                <p class="small text-secondary mb-4">Generate and revoke API access keys for connecting third-party systems or future automated AI analytics to the weather data stream.</p>

                <!-- Tokens Table -->
                <div class="table-responsive mb-4" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-sm table-glass" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th>Token Name</th>
                                <th>Hashed Signature</th>
                                <th>Created</th>
                                <th class="text-end">Revoke</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tokens as $token)
                                <tr>
                                    <td class="fw-semibold text-primary">{{ $token->name }}</td>
                                    <td><code class="text-muted" style="font-size: 0.75rem;">{{ substr($token->token, 0, 16) }}...</code></td>
                                    <td class="text-secondary">{{ $token->created_at->format('Y-m-d') }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('settings.tokens.delete', $token->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Are you sure you want to revoke this administrative API token? Any applications using it will be immediately cut off.')">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No administrative API keys registered.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create token form -->
            <div class="border-top pt-3">
                <form action="{{ route('settings.tokens.generate') }}" method="POST" autocomplete="off">
                    @csrf
                    <label class="form-label text-secondary small fw-semibold">Generate New Administrative API Token</label>
                    <div class="d-flex gap-2">
                        <input type="text" name="name" class="form-control form-control-glass" placeholder="e.g. AI-Model-Predictor" required>
                        <button type="submit" class="btn btn-premium-primary text-nowrap d-flex align-items-center gap-1.5 py-2 px-3">
                            <i class="fa-solid fa-plus"></i>
                            <span>Generate</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function copyPlainToken() {
        const copyText = document.getElementById("plainTokenField");
        if (copyText) {
            navigator.clipboard.writeText(copyText.value).then(() => {
                alert("New API token copied successfully!");
            }).catch(err => {
                console.error("Failed to copy plain token: ", err);
            });
        }
    }
</script>
@endsection
