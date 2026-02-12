@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')

<style>
    /* Professional Light Dashboard */
    :root {
        --primary: #6366F1;
        --primary-light: #818CF8;
        --secondary: #10B981;
        --accent: #F59E0B;
        --danger: #EF4444;
        --success: #10B981;
        --info: #3B82F6;
        --purple: #A855F7;
        --pink: #EC4899;
        --cyan: #06B6D4;
        --bg-main: #F8FAFC;
        --bg-card: #FFFFFF;
        --text-primary: #0F172A;
        --text-secondary: #64748B;
        --text-muted: #94A3B8;
        --border: #E2E8F0;
        --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        background: var(--bg-main);
        color: var(--text-primary);
        font-size: 14px !important;
        line-height: 1.6 !important;
        -webkit-font-smoothing: antialiased;
    }

    .dashboard-wrapper {
        padding: 24px;
        max-width: 1600px;
        margin: 0 auto;
    }

    /* Header */
    .dashboard-header {
        margin-bottom: 32px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--border);
    }

    .dashboard-title {
        font-size: 32px !important;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 10px;
        line-height: 1.3;
    }

    .dashboard-subtitle {
        font-size: 15px !important;
        color: var(--text-secondary);
        font-weight: 400;
        line-height: 1.5;
    }

    /* KPI Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 20px;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .kpi-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--primary-light));
    }

    .kpi-card.success::before {
        background: linear-gradient(90deg, var(--success), #34D399);
    }

    .kpi-card.warning::before {
        background: linear-gradient(90deg, var(--accent), #FBBF24);
    }

    .kpi-card.danger::before {
        background: linear-gradient(90deg, var(--danger), #F87171);
    }

    .kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .kpi-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
    }

    .kpi-badge {
        font-size: 12px !important;
        padding: 5px 12px;
        border-radius: 12px;
        font-weight: 600;
        background: #F1F5F9;
        color: var(--text-secondary);
    }

    .kpi-label {
        font-size: 13px !important;
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    .kpi-value {
        font-size: 32px !important;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1;
        margin-bottom: 8px;
    }

    .kpi-change {
        font-size: 13px !important;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .kpi-change.positive {
        color: var(--success);
    }

    .kpi-change.negative {
        color: var(--danger);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 20px;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
    }

    .stat-card a {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .stat-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
    }

    .stat-title {
        font-size: 15px !important;
        font-weight: 600;
        color: var(--text-primary);
    }

    .stat-metrics {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
    }

    .stat-label {
        font-size: 14px !important;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .stat-value {
        font-size: 18px !important;
        font-weight: 700;
        color: var(--text-primary);
    }

    .stat-value.success {
        color: var(--success);
    }

    .stat-value.danger {
        color: var(--danger);
    }

    /* Revenue Cards - KPI Style */
    .revenue-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .revenue-card {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 20px;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .revenue-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .revenue-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--primary-light));
    }

    .revenue-card.today::before {
        background: linear-gradient(90deg, #06B6D4, #22D3EE);
    }

    .revenue-card.yesterday::before {
        background: linear-gradient(90deg, #8B5CF6, #A78BFA);
    }

    .revenue-card.month::before {
        background: linear-gradient(90deg, #10B981, #34D399);
    }

    .revenue-card.last-month::before {
        background: linear-gradient(90deg, #F59E0B, #FBBF24);
    }

    .revenue-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .revenue-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
    }

    .revenue-badge {
        font-size: 12px !important;
        padding: 5px 12px;
        border-radius: 12px;
        font-weight: 600;
        background: #F1F5F9;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .revenue-title {
        font-size: 13px !important;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 14px;
    }

    .revenue-metrics {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .revenue-metric-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
    }

    .revenue-label {
        font-size: 13px !important;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .revenue-amount {
        font-size: 17px !important;
        font-weight: 700;
        color: var(--text-primary);
    }

    .revenue-divider {
        height: 1px;
        background: var(--border);
        margin: 14px 0;
    }

    .revenue-total-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        padding-top: 10px;
    }

    .revenue-total-label {
        font-size: 13px !important;
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .revenue-total {
        font-size: 24px !important;
        font-weight: 800;
        color: var(--text-primary);
    }

    /* Charts */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .chart-card {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 24px;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
    }

    .chart-card.span-6 {
        grid-column: span 6;
    }

    .chart-card.span-12 {
        grid-column: span 12;
    }

    .chart-header {
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--border);
    }

    .chart-title {
        font-size: 16px !important;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 6px;
        line-height: 1.4;
        text-transform: none;
    }

    .chart-subtitle {
        font-size: 13px !important;
        color: var(--text-secondary);
        line-height: 1.5;
    }

    .chart-container {
        position: relative;
        height: 300px;
    }

    .chart-card.span-4 {
        grid-column: span 4;
    }

    .chart-card.span-8 {
        grid-column: span 8;
    }

    /* Section Headers */
    .section-header {
        margin: 36px 0 24px;
        padding-bottom: 14px;
        border-bottom: 2px solid var(--border);
    }

    .section-title {
        font-size: 18px !important;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        line-height: 1.3;
    }

    .section-subtitle {
        font-size: 14px !important;
        color: var(--text-secondary);
        line-height: 1.5;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .chart-card.span-6 {
            grid-column: span 12;
        }

        .chart-card.span-4 {
            grid-column: span 6;
        }

        .chart-card.span-8 {
            grid-column: span 12;
        }
    }

    @media (max-width: 768px) {
        .dashboard-wrapper {
            padding: 16px;
        }

        .kpi-grid,
        .stats-grid,
        .revenue-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .kpi-value {
            font-size: 28px !important;
        }

        .chart-container {
            height: 280px;
        }

        .chart-card.span-4,
        .chart-card.span-6,
        .chart-card.span-8 {
            grid-column: span 12;
        }
    }
</style>

<div class="main-container">
    <div class="dashboard-wrapper">
        <!-- Header -->
        <div class="dashboard-header">
            <h1 class="dashboard-title">Dashboard Overview</h1>
            <p class="dashboard-subtitle">Real-time analytics and performance metrics</p>
        </div>

        @if ($roleManager::isAdmin(Auth::user()->user_type))
            <!-- KPI Cards -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon">
                            <i class="fa fa-file-alt"></i>
                        </div>
                        <span class="kpi-badge">Total</span>
                    </div>
                    <div class="kpi-label">Total Content</div>
                    <div class="kpi-value">{{ number_format($datas['item'] + $datas['cat'] + $datas['stk_item']) }}</div>
                    <div class="kpi-change positive">
                        <i class="fa fa-arrow-up"></i>
                        <span>Active Items</span>
                    </div>
                </div>

                <div class="kpi-card success">
                    <div class="kpi-header">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, var(--success), #34D399);">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <span class="kpi-badge">Live</span>
                    </div>
                    <div class="kpi-label">Published Content</div>
                    <div class="kpi-value">{{ number_format($datas['item_live'] + $datas['cat_live']) }}</div>
                    <div class="kpi-change positive">
                        <i class="fa fa-check"></i>
                        <span>Online</span>
                    </div>
                </div>

                <div class="kpi-card warning">
                    <div class="kpi-header">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, var(--accent), #FBBF24);">
                            <i class="fa fa-folder"></i>
                        </div>
                        <span class="kpi-badge">Categories</span>
                    </div>
                    <div class="kpi-label">Total Categories</div>
                    <div class="kpi-value">{{ number_format($datas['cat']) }}</div>
                    <div class="kpi-change">
                        <i class="fa fa-layer-group"></i>
                        <span>Organized</span>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, var(--info), #60A5FA);">
                            <i class="fa fa-images"></i>
                        </div>
                        <span class="kpi-badge">Templates</span>
                    </div>
                    <div class="kpi-label">Design Templates</div>
                    <div class="kpi-value">{{ number_format($datas['item']) }}</div>
                    <div class="kpi-change">
                        <i class="fa fa-palette"></i>
                        <span>Available</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Content Stats -->
        <div class="section-header">
            <h2 class="section-title">Content Management</h2>
            <p class="section-subtitle">Manage your templates, categories, and digital assets</p>
        </div>

        <div class="stats-grid">

            @if ($roleManager::isAdmin(Auth::user()->user_type))
                <div class="stat-card">
                    <a href="{{ route('show_app') }}">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fa fa-mobile-alt"></i>
                            </div>
                            <div class="stat-title">Applications</div>
                        </div>
                        <div class="stat-metrics">
                            <div class="stat-row">
                                <span class="stat-label">Total</span>
                                <span class="stat-value">{{ $datas['app'] }}</span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">Live</span>
                                <span class="stat-value success">{{ $datas['app_live'] }}</span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">Unlive</span>
                                <span class="stat-value danger">{{ $datas['app_unlive'] }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            @else
                <div class="stat-card">
                    <a href="{{ route('show_fonts') }}">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fa fa-font"></i>
                            </div>
                            <div class="stat-title">Fonts</div>
                        </div>
                        <div class="stat-metrics">
                            <div class="stat-row">
                                <span class="stat-label">Total</span>
                                <span class="stat-value">{{ $datas['fonts'] }}</span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">Live</span>
                                <span class="stat-value success">{{ $datas['fonts_live'] }}</span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">Unlive</span>
                                <span class="stat-value danger">{{ $datas['fonts_unlive'] }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            <div class="stat-card">
                <a href="{{ route('show_cat') }}">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #FBBF24);">
                            <i class="fa fa-folder-open"></i>
                        </div>
                        <div class="stat-title">Categories</div>
                    </div>
                    <div class="stat-metrics">
                        <div class="stat-row">
                            <span class="stat-label">Total</span>
                            <span class="stat-value">{{ $datas['cat'] }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Live</span>
                            <span class="stat-value success">{{ $datas['cat_live'] }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Unlive</span>
                            <span class="stat-value danger">{{ $datas['cat_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="stat-card">
                <a href="{{ route('show_item') }}">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #10B981, #34D399);">
                            <i class="fa fa-file-alt"></i>
                        </div>
                        <div class="stat-title">Templates</div>
                    </div>
                    <div class="stat-metrics">
                        <div class="stat-row">
                            <span class="stat-label">Total</span>
                            <span class="stat-value">{{ $datas['item'] }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Live</span>
                            <span class="stat-value success">{{ $datas['item_live'] }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Unlive</span>
                            <span class="stat-value danger">{{ $datas['item_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="stat-card">
                <a href="{{ route('show_sticker_cat.index') }}">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #EC4899, #F472B6);">
                            <i class="fa fa-smile"></i>
                        </div>
                        <div class="stat-title">Stickers</div>
                    </div>
                    <div class="stat-metrics">
                        <div class="stat-row">
                            <span class="stat-label">Total</span>
                            <span class="stat-value">{{ $datas['stk_item'] }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Live</span>
                            <span class="stat-value success">{{ $datas['stk_item_live'] }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Unlive</span>
                            <span class="stat-value danger">{{ $datas['stk_item_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="stat-card">
                <a href="{{ route('show_bg_cat.index') }}">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #A855F7, #C084FC);">
                            <i class="fa fa-image"></i>
                        </div>
                        <div class="stat-title">Backgrounds</div>
                    </div>
                    <div class="stat-metrics">
                        <div class="stat-row">
                            <span class="stat-label">Total</span>
                            <span class="stat-value">{{ $datas['bg_item'] }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Live</span>
                            <span class="stat-value success">{{ $datas['bg_item_live'] }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Unlive</span>
                            <span class="stat-value danger">{{ $datas['bg_item_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="stat-card">
                <a href="{{ route('show_v_item') }}">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #06B6D4, #22D3EE);">
                            <i class="fa fa-video"></i>
                        </div>
                        <div class="stat-title">Video Templates</div>
                    </div>
                    <div class="stat-metrics">
                        <div class="stat-row">
                            <span class="stat-label">Total</span>
                            <span class="stat-value">{{ $datas['video_template_item'] }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Live</span>
                            <span class="stat-value success">{{ $datas['video_template_item_live'] }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Unlive</span>
                            <span class="stat-value danger">{{ $datas['video_template_item_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="stat-card">
                <a href="{{ route('frame_items.index') }}">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #8B5CF6, #A78BFA);">
                            <i class="fa fa-shapes"></i>
                        </div>
                        <div class="stat-title">Shapes</div>
                    </div>
                    <div class="stat-metrics">
                        <div class="stat-row">
                            <span class="stat-label">Total</span>
                            <span class="stat-value">{{ $datas['frame_item'] }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Live</span>
                            <span class="stat-value success">{{ $datas['frame_item_live'] }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Unlive</span>
                            <span class="stat-value danger">{{ $datas['frame_item_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        @if ($roleManager::isAdmin(Auth::user()->user_type))
            <!-- Revenue Section -->
            <div class="section-header">
                <h2 class="section-title">Revenue Analytics</h2>
                <p class="section-subtitle">Financial performance and earnings overview</p>
            </div>

            <div class="revenue-grid">
                <div class="revenue-card today">
                    <div class="revenue-header">
                        <div class="revenue-icon" style="background: linear-gradient(135deg, #06B6D4, #22D3EE);">
                            <i class="fa fa-calendar-day"></i>
                        </div>
                        <span class="revenue-badge">Today</span>
                    </div>
                    <div class="revenue-title">Today's Revenue</div>
                    <div class="revenue-metrics">
                        <div class="revenue-metric-row">
                            <span class="revenue-label">Subscriptions</span>
                            <span class="revenue-amount">{{ $datas['today_subs_inr'] }}</span>
                        </div>
                        <div class="revenue-metric-row">
                            <span class="revenue-label">Templates</span>
                            <span class="revenue-amount">{{ $datas['today_templates_inr'] }}</span>
                        </div>
                        <div class="revenue-metric-row">
                            <span class="revenue-label">Videos</span>
                            <span class="revenue-amount">{{ $datas['today_video_inr'] }}</span>
                        </div>
                    </div>
                    <div class="revenue-divider"></div>
                    <div class="revenue-total-row">
                        <span class="revenue-total-label">Total</span>
                        <span class="revenue-total">{{ $datas['today_inr'] }}</span>
                    </div>
                </div>

                <div class="revenue-card yesterday">
                    <div class="revenue-header">
                        <div class="revenue-icon" style="background: linear-gradient(135deg, #8B5CF6, #A78BFA);">
                            <i class="fa fa-calendar-minus"></i>
                        </div>
                        <span class="revenue-badge">Yesterday</span>
                    </div>
                    <div class="revenue-title">Yesterday's Revenue</div>
                    <div class="revenue-metrics">
                        <div class="revenue-metric-row">
                            <span class="revenue-label">Subscriptions</span>
                            <span class="revenue-amount">{{ $datas['yesterday_subs_inr'] }}</span>
                        </div>
                        <div class="revenue-metric-row">
                            <span class="revenue-label">Templates</span>
                            <span class="revenue-amount">{{ $datas['yesterday_templates_inr'] }}</span>
                        </div>
                        <div class="revenue-metric-row">
                            <span class="revenue-label">Videos</span>
                            <span class="revenue-amount">{{ $datas['yesterday_video_inr'] }}</span>
                        </div>
                    </div>
                    <div class="revenue-divider"></div>
                    <div class="revenue-total-row">
                        <span class="revenue-total-label">Total</span>
                        <span class="revenue-total">{{ $datas['yesterday_inr'] }}</span>
                    </div>
                </div>

                <div class="revenue-card month">
                    <div class="revenue-header">
                        <div class="revenue-icon" style="background: linear-gradient(135deg, #10B981, #34D399);">
                            <i class="fa fa-calendar-week"></i>
                        </div>
                        <span class="revenue-badge">This Month</span>
                    </div>
                    <div class="revenue-title">This Month's Revenue</div>
                    <div class="revenue-metrics">
                        <div class="revenue-metric-row">
                            <span class="revenue-label">Subscriptions</span>
                            <span class="revenue-amount">{{ $datas['this_month_subs_inr'] }}</span>
                        </div>
                        <div class="revenue-metric-row">
                            <span class="revenue-label">Templates</span>
                            <span class="revenue-amount">{{ $datas['this_month_templates_inr'] }}</span>
                        </div>
                        <div class="revenue-metric-row">
                            <span class="revenue-label">Videos</span>
                            <span class="revenue-amount">{{ $datas['this_month_video_inr'] }}</span>
                        </div>
                    </div>
                    <div class="revenue-divider"></div>
                    <div class="revenue-total-row">
                        <span class="revenue-total-label">Total</span>
                        <span class="revenue-total">{{ $datas['this_month_inr'] }}</span>
                    </div>
                </div>

                <div class="revenue-card last-month">
                    <div class="revenue-header">
                        <div class="revenue-icon" style="background: linear-gradient(135deg, #F59E0B, #FBBF24);">
                            <i class="fa fa-calendar-alt"></i>
                        </div>
                        <span class="revenue-badge">Last Month</span>
                    </div>
                    <div class="revenue-title">Last Month's Revenue</div>
                    <div class="revenue-metrics">
                        <div class="revenue-metric-row">
                            <span class="revenue-label">Subscriptions</span>
                            <span class="revenue-amount">{{ $datas['last_month_subs_inr'] }}</span>
                        </div>
                        <div class="revenue-metric-row">
                            <span class="revenue-label">Templates</span>
                            <span class="revenue-amount">{{ $datas['last_month_templates_inr'] }}</span>
                        </div>
                        <div class="revenue-metric-row">
                            <span class="revenue-label">Videos</span>
                            <span class="revenue-amount">{{ $datas['last_month_video_inr'] }}</span>
                        </div>
                    </div>
                    <div class="revenue-divider"></div>
                    <div class="revenue-total-row">
                        <span class="revenue-total-label">Total</span>
                        <span class="revenue-total">{{ $datas['last_month_inr'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="section-header">
                <h2 class="section-title">Visual Analytics</h2>
                <p class="section-subtitle">Data insights and performance trends</p>
            </div>

            <div class="charts-grid">
                <div class="chart-card span-4">
                    <div class="chart-header">
                        <h3 class="chart-title">Content Distribution</h3>
                        <p class="chart-subtitle">Breakdown by content type</p>
                    </div>
                    <div class="chart-container">
                        <canvas id="contentChart"></canvas>
                    </div>
                </div>

                <div class="chart-card span-4">
                    <div class="chart-header">
                        <h3 class="chart-title">Status Overview</h3>
                        <p class="chart-subtitle">Live vs Unlive content</p>
                    </div>
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <div class="chart-card span-4">
                    <div class="chart-header">
                        <h3 class="chart-title">Category Performance</h3>
                        <p class="chart-subtitle">Top performing categories</p>
                    </div>
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

                <div class="chart-card span-8">
                    <div class="chart-header">
                        <h3 class="chart-title">Revenue Trends</h3>
                        <p class="chart-subtitle">Revenue performance across time periods</p>
                    </div>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <div class="chart-card span-4">
                    <div class="chart-header">
                        <h3 class="chart-title">Content Growth</h3>
                        <p class="chart-subtitle">Live content by category</p>
                    </div>
                    <div class="chart-container">
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if ($roleManager::isAdmin(Auth::user()->user_type))
        // Content Distribution Chart
        const contentCtx = document.getElementById('contentChart');
        if (contentCtx) {
            new Chart(contentCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Templates', 'Categories', 'Stickers', 'Backgrounds', 'Videos', 'Shapes'],
                    datasets: [{
                        data: [
                            {{ $datas['item'] }},
                            {{ $datas['cat'] }},
                            {{ $datas['stk_item'] }},
                            {{ $datas['bg_item'] }},
                            {{ $datas['video_template_item'] }},
                            {{ $datas['frame_item'] }}
                        ],
                        backgroundColor: [
                            '#6366F1',
                            '#10B981',
                            '#EC4899',
                            '#F59E0B',
                            '#06B6D4',
                            '#8B5CF6'
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 12,
                                font: {
                                    size: 12,
                                    family: 'Inter',
                                    weight: '500'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: '#64748B',
                                boxWidth: 10,
                                boxHeight: 10
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            padding: 12,
                            titleFont: {
                                size: 13,
                                weight: '600'
                            },
                            bodyFont: {
                                size: 12
                            },
                            cornerRadius: 6,
                            displayColors: true,
                            boxWidth: 12,
                            boxHeight: 12
                        }
                    }
                }
            });
        }

        // Status Overview Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            const totalLive = {{ $datas['item_live'] + $datas['cat_live'] + $datas['stk_item_live'] + $datas['bg_item_live'] }};
            const totalUnlive = {{ $datas['item_unlive'] + $datas['cat_unlive'] + $datas['stk_item_unlive'] + $datas['bg_item_unlive'] }};
            
            new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: ['Live Content', 'Unlive Content'],
                    datasets: [{
                        data: [totalLive, totalUnlive],
                        backgroundColor: ['#10B981', '#EF4444'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 12,
                                font: {
                                    size: 12,
                                    family: 'Inter',
                                    weight: '500'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: '#64748B',
                                boxWidth: 10,
                                boxHeight: 10
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            padding: 12,
                            titleFont: {
                                size: 13,
                                weight: '600'
                            },
                            bodyFont: {
                                size: 12
                            },
                            cornerRadius: 6
                        }
                    }
                }
            });
        }

        // Category Performance Chart
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            new Chart(categoryCtx, {
                type: 'polarArea',
                data: {
                    labels: ['Templates', 'Stickers', 'Backgrounds', 'Videos', 'Shapes'],
                    datasets: [{
                        data: [
                            {{ $datas['item'] }},
                            {{ $datas['stk_item'] }},
                            {{ $datas['bg_item'] }},
                            {{ $datas['video_template_item'] }},
                            {{ $datas['frame_item'] }}
                        ],
                        backgroundColor: [
                            'rgba(99, 102, 241, 0.7)',
                            'rgba(236, 72, 153, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(6, 182, 212, 0.7)',
                            'rgba(139, 92, 246, 0.7)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 10,
                                font: {
                                    size: 11,
                                    family: 'Inter',
                                    weight: '500'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: '#64748B',
                                boxWidth: 8,
                                boxHeight: 8
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            padding: 10,
                            titleFont: {
                                size: 12,
                                weight: '600'
                            },
                            bodyFont: {
                                size: 11
                            },
                            cornerRadius: 6
                        }
                    },
                    scales: {
                        r: {
                            ticks: {
                                display: false
                            },
                            grid: {
                                color: '#E2E8F0'
                            }
                        }
                    }
                }
            });
        }

        // Revenue Trends Chart
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: ['Today', 'Yesterday', 'This Month', 'Last Month'],
                    datasets: [
                        {
                            label: 'Subscriptions',
                            data: [
                                parseFloat('{{ $datas["today_subs_inr"] }}'.replace(/[^0-9.-]+/g,"") || 0),
                                parseFloat('{{ $datas["yesterday_subs_inr"] }}'.replace(/[^0-9.-]+/g,"") || 0),
                                parseFloat('{{ $datas["this_month_subs_inr"] }}'.replace(/[^0-9.-]+/g,"") || 0),
                                parseFloat('{{ $datas["last_month_subs_inr"] }}'.replace(/[^0-9.-]+/g,"") || 0)
                            ],
                            backgroundColor: '#6366F1',
                            borderRadius: 4,
                            barThickness: 28
                        },
                        {
                            label: 'Templates',
                            data: [
                                parseFloat('{{ $datas["today_templates_inr"] }}'.replace(/[^0-9.-]+/g,"") || 0),
                                parseFloat('{{ $datas["yesterday_templates_inr"] }}'.replace(/[^0-9.-]+/g,"") || 0),
                                parseFloat('{{ $datas["this_month_templates_inr"] }}'.replace(/[^0-9.-]+/g,"") || 0),
                                parseFloat('{{ $datas["last_month_templates_inr"] }}'.replace(/[^0-9.-]+/g,"") || 0)
                            ],
                            backgroundColor: '#10B981',
                            borderRadius: 4,
                            barThickness: 28
                        },
                        {
                            label: 'Videos',
                            data: [
                                parseFloat('{{ $datas["today_video_inr"] }}'.replace(/[^0-9.-]+/g,"") || 0),
                                parseFloat('{{ $datas["yesterday_video_inr"] }}'.replace(/[^0-9.-]+/g,"") || 0),
                                parseFloat('{{ $datas["this_month_video_inr"] }}'.replace(/[^0-9.-]+/g,"") || 0),
                                parseFloat('{{ $datas["last_month_video_inr"] }}'.replace(/[^0-9.-]+/g,"") || 0)
                            ],
                            backgroundColor: '#F59E0B',
                            borderRadius: 4,
                            barThickness: 28
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                padding: 10,
                                font: {
                                    size: 11,
                                    family: 'Inter',
                                    weight: '500'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: '#64748B',
                                boxWidth: 8,
                                boxHeight: 8
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            padding: 10,
                            titleFont: {
                                size: 12,
                                weight: '600'
                            },
                            bodyFont: {
                                size: 11
                            },
                            cornerRadius: 6
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#E2E8F0',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    family: 'Inter'
                                },
                                color: '#94A3B8'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    family: 'Inter',
                                    weight: '500'
                                },
                                color: '#64748B'
                            }
                        }
                    }
                }
            });
        }

        // Content Growth Chart (Radar)
        const growthCtx = document.getElementById('growthChart');
        if (growthCtx) {
            new Chart(growthCtx, {
                type: 'radar',
                data: {
                    labels: ['Templates', 'Categories', 'Stickers', 'Backgrounds', 'Videos'],
                    datasets: [{
                        label: 'Live Content',
                        data: [
                            {{ $datas['item_live'] }},
                            {{ $datas['cat_live'] }},
                            {{ $datas['stk_item_live'] }},
                            {{ $datas['bg_item_live'] }},
                            {{ $datas['video_template_item_live'] }}
                        ],
                        backgroundColor: 'rgba(99, 102, 241, 0.2)',
                        borderColor: '#6366F1',
                        borderWidth: 2,
                        pointBackgroundColor: '#6366F1',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#6366F1',
                        pointRadius: 3,
                        pointHoverRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 10,
                                font: {
                                    size: 11,
                                    family: 'Inter',
                                    weight: '500'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: '#64748B',
                                boxWidth: 8,
                                boxHeight: 8
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            padding: 10,
                            titleFont: {
                                size: 12,
                                weight: '600'
                            },
                            bodyFont: {
                                size: 11
                            },
                            cornerRadius: 6
                        }
                    },
                    scales: {
                        r: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: 10
                                },
                                color: '#94A3B8',
                                backdropColor: 'transparent'
                            },
                            grid: {
                                color: '#E2E8F0'
                            },
                            pointLabels: {
                                font: {
                                    size: 11,
                                    family: 'Inter',
                                    weight: '500'
                                },
                                color: '#64748B'
                            }
                        }
                    }
                }
            });
        }
    @endif
});
</script>

@include('layouts.masterscript')
