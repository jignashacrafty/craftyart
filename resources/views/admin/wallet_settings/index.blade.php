@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@include('layouts.masterhead')

<style>
    .designer-system-container {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px;
    }
    
    .system-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .system-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        background: white;
    }
    
    .system-title {
        font-size: 20px;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }
    
    .system-subtitle {
        font-size: 14px;
        color: #6c757d;
        margin: 5px 0 0 0;
    }
    
    .system-table-wrapper {
        overflow-x: auto;
    }
    
    .system-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .system-table thead th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        padding: 15px 12px;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    
    .system-table tbody td {
        padding: 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
        color: #495057;
    }
    
    .system-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .btn-action {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        margin: 2px;
        display: inline-block;
        white-space: nowrap;
    }
    
    .actions-cell {
        white-space: nowrap;
        min-width: 150px;
    }
    
    .btn-info-action {
        background: #17a2b8;
        color: white;
    }
    
    .btn-info-action:hover {
        background: #138496;
    }
    
    .btn-edit {
        background: #ffc107;
        color: #212529;
    }
    
    .btn-edit:hover {
        background: #e0a800;
    }
    
    .btn-toggle {
        background: #6c757d;
        color: white;
    }
    
    .btn-toggle:hover {
        background: #5a6268;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }
</style>

<div class="main-container">
    <div class="designer-system-container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <div class="system-card">
            <div class="system-header">
                <h1 class="system-title">Wallet Configuration</h1>
                <p class="system-subtitle">Manage withdrawal thresholds, commission rates, and payout schedules for designer earnings</p>
            </div>

            <div class="system-table-wrapper">
                <table class="system-table">
                    <thead>
                        <tr>
                            <th>SETTING</th>
                            <th>MIN WITHDRAWAL</th>
                            <th>MAX WITHDRAWAL</th>
                            <th>COMMISSION</th>
                            <th>PAYOUT SCHEDULE</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settings as $setting)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                        <i class="fa fa-wallet" style="color: white; font-size: 18px;"></i>
                                    </div>
                                    <div>
                                        <strong style="font-size: 15px; color: #212529;">{{ $setting->setting_name }}</strong><br>
                                        <small style="color: #6c757d; font-size: 12px;">
                                            <i class="fa fa-key" style="font-size: 10px;"></i> {{ $setting->setting_key }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 15px; font-weight: 600; color: #28a745;">
                                    ₹{{ number_format($setting->min_withdrawal_threshold, 0) }}
                                </div>
                                <small style="color: #6c757d;">Minimum</small>
                            </td>
                            <td>
                                <div style="font-size: 15px; font-weight: 600; color: #dc3545;">
                                    ₹{{ number_format($setting->max_withdrawal_limit, 0) }}
                                </div>
                                <small style="color: #6c757d;">Maximum</small>
                            </td>
                            <td>
                                <div style="display: inline-block; background: #fff3cd; color: #856404; padding: 6px 14px; border-radius: 20px; font-weight: 600; font-size: 14px;">
                                    {{ $setting->platform_commission_rate }}%
                                </div>
                            </td>
                            <td>
                                <div style="margin-bottom: 4px;">
                                    <span style="display: inline-block; background: #d1ecf1; color: #0c5460; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                        <i class="fa fa-calendar"></i> {{ ucfirst($setting->payout_frequency) }}
                                    </span>
                                </div>
                                <small style="color: #6c757d;">
                                    <i class="fa fa-clock-o"></i> Day {{ $setting->payout_day_of_month }} of month
                                </small>
                            </td>
                            <td>
                                @if($setting->is_active)
                                    <span class="status-badge status-active">
                                        <i class="fa fa-check-circle"></i> Active
                                    </span>
                                @else
                                    <span class="status-badge status-inactive">
                                        <i class="fa fa-times-circle"></i> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="actions-cell">
                                <button type="button" class="btn-action btn-info-action" 
                                        data-toggle="modal" 
                                        data-target="#editModal{{ $setting->id }}">
                                    <i class="fa fa-edit"></i> Edit
                                </button>
                                <form action="{{ route('designer_system.wallet_settings.toggle', $setting->id) }}" 
                                      method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-toggle" title="Toggle Status">
                                        <i class="fa fa-toggle-{{ $setting->is_active ? 'on' : 'off' }}"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal for this setting -->
                        <div class="modal fade" id="editModal{{ $setting->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <form action="{{ route('designer_system.wallet_settings.update', $setting->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fa fa-edit"></i> Edit Wallet Configuration
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Setting Name *</label>
                                                        <input type="text" name="setting_name" class="form-control" value="{{ $setting->setting_name }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Setting Key *</label>
                                                        <input type="text" name="setting_key" class="form-control" value="{{ $setting->setting_key }}" readonly style="background: #f8f9fa;">
                                                        <small class="text-muted">Unique identifier (cannot be changed)</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>
                                                            <i class="fa fa-arrow-down text-success"></i> Min Withdrawal (₹) *
                                                        </label>
                                                        <input type="number" name="min_withdrawal_threshold" class="form-control" value="{{ $setting->min_withdrawal_threshold }}" step="0.01" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>
                                                            <i class="fa fa-arrow-up text-danger"></i> Max Withdrawal (₹)
                                                        </label>
                                                        <input type="number" name="max_withdrawal_limit" class="form-control" value="{{ $setting->max_withdrawal_limit }}" step="0.01">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>
                                                            <i class="fa fa-percent text-warning"></i> Commission Rate (%) *
                                                        </label>
                                                        <input type="number" name="platform_commission_rate" class="form-control" value="{{ $setting->platform_commission_rate }}" step="0.01" min="0" max="100" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>
                                                            <i class="fa fa-calendar text-info"></i> Payout Day *
                                                        </label>
                                                        <input type="number" name="payout_day_of_month" class="form-control" value="{{ $setting->payout_day_of_month }}" min="1" max="31" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>
                                                    <i class="fa fa-clock-o text-secondary"></i> Payout Frequency *
                                                </label>
                                                <select name="payout_frequency" class="form-control" required>
                                                    <option value="daily" {{ $setting->payout_frequency == 'daily' ? 'selected' : '' }}>Daily</option>
                                                    <option value="weekly" {{ $setting->payout_frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                    <option value="monthly" {{ $setting->payout_frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                <i class="fa fa-times"></i> Cancel
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-save"></i> Update Configuration
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fa fa-cog"></i>
                                    <p>No wallet settings configured</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
