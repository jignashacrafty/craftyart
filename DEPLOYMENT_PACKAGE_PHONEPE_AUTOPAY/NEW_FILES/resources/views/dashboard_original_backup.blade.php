@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')

<style>
    /* Modern Admin Dashboard Design System */
    :root {
        --primary-color: #6366f1;
        --primary-dark: #4f46e5;
        --primary-light: #818cf8;
        --secondary-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --info-color: #3b82f6;
        --success-color: #10b981;
        --bg-primary: #f8fafc;
        --bg-secondary: #ffffff;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --text-muted: #94a3b8;
        --border-color: #e2e8f0;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--bg-primary);
        color: var(--text-primary);
        line-height: 1.6;
    }

    /* Dashboard Container */
    .modern-dashboard {
        background: var(--bg-primary);
        min-height: 100vh;
        padding: 32px 24px;
    }

    /* Welcome Header */
    .welcome-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        border-radius: var(--radius-lg);
        padding: 40px 32px;
        margin-bottom: 32px;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }

    .welcome-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .welcome-content {
        position: relative;
        z-index: 1;
    }

    .welcome-title {
        color: #ffffff;
        font-size: 32px;
        font-weight: 700;
        margin: 0 0 8px 0;
        letter-spacing: -0.5px;
    }

    .welcome-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 16px;
        font-weight: 400;
        margin: 0;
    }

    /* Section Headers */
    .section-header {
        margin: 48px 0 24px 0;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-title::before {
        content: '';
        width: 4px;
        height: 24px;
        background: var(--primary-color);
        border-radius: 2px;
    }

    .section-subtitle {
        font-size: 14px;
        color: var(--text-secondary);
        margin: 0;
        padding-left: 16px;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        padding: 24px;
        border: 1px solid var(--border-color);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
        border-color: var(--primary-light);
    }

    .stat-card:hover::before {
        transform: scaleX(1);
    }

    .stat-card a {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .stat-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }

    .stat-body {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
    }

    .stat-label {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-secondary);
    }

    .stat-value {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .stat-value.success {
        color: var(--success-color);
    }

    .stat-value.danger {
        color: var(--danger-color);
    }

    .stat-divider {
        height: 1px;
        background: var(--border-color);
        margin: 4px 0;
    }

    /* Revenue Cards */
    .revenue-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .revenue-card {
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .revenue-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }

    .revenue-header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        padding: 20px 24px;
        text-align: center;
    }

    .revenue-title {
        font-size: 14px;
        font-weight: 700;
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .revenue-body {
        padding: 24px;
    }

    .revenue-item {
        padding: 16px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .revenue-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .revenue-item a {
        text-decoration: none;
        color: inherit;
        display: block;
        transition: all 0.2s ease;
    }

    .revenue-item a:hover .revenue-item-label {
        color: var(--primary-color);
    }

    .revenue-item-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        transition: color 0.2s ease;
    }

    .revenue-values {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .revenue-amount {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .revenue-footer {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 24px;
        text-align: center;
        border-top: 2px solid var(--primary-color);
    }

    .revenue-total-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--primary-color);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
    }

    .revenue-total-amount {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.2;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 64px 24px;
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        border: 2px dashed var(--border-color);
    }

    .empty-state-icon {
        font-size: 48px;
        color: var(--text-muted);
        margin-bottom: 16px;
    }

    .empty-state-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .empty-state-text {
        font-size: 14px;
        color: var(--text-secondary);
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .modern-dashboard {
            padding: 20px 16px;
        }

        .welcome-header {
            padding: 32px 24px;
        }

        .welcome-title {
            font-size: 24px;
        }

        .welcome-subtitle {
            font-size: 14px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .revenue-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .section-header {
            margin: 32px 0 16px 0;
        }
    }

    @media (max-width: 480px) {
        .welcome-header {
            padding: 24px 20px;
        }

        .stat-card {
            padding: 20px;
        }

        .revenue-body {
            padding: 20px;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card, .revenue-card {
        animation: fadeInUp 0.5s ease-out;
    }

    /* Utility Classes */
    .mb-32 { margin-bottom: 32px; }
    .mt-32 { margin-top: 32px; }
    .text-center { text-align: center; }
</style>

<div class="main-container">
    <div class="dashboard-wrapper">
        <div class="dashboard-header">
            <h1>
                <i class="icon-copy fa fa-dashboard" aria-hidden="true"></i>
                Dashboard
            </h1>
            <p class="dashboard-subtitle">Welcome back! Here's your overview</p>
        </div>
        <div class="row">
            @if ($roleManager::isAdmin(Auth::user()->user_type))
                <div class="col-xl-3 col-md-6 mb-30">
                    <a href="{{ route('show_app') }}">
                        <div class="stats-card">
                            <div class="stats-title">Applications</div>
                            <div class="stats-value">
                                <span class="stats-label">Total</span>
                                <span class="stats-number">{{ $datas['app'] }}</span>
                            </div>
                            <div class="stats-value">
                                <span class="stats-label">Live</span>
                                <span class="stats-number" style="color: #1abc9c;">{{ $datas['app_live'] }}</span>
                            </div>
                            <div class="stats-value">
                                <span class="stats-label">Unlive</span>
                                <span class="stats-number" style="color: #e74c3c;">{{ $datas['app_unlive'] }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            @else
                <div class="col-xl-3 col-md-6 mb-30">
                    <a href="{{ route('show_fonts') }}">
                        <div class="stats-card">
                            <div class="stats-title">Fonts</div>
                            <div class="stats-value">
                                <span class="stats-label">Total</span>
                                <span class="stats-number">{{ $datas['fonts'] }}</span>
                            </div>
                            <div class="stats-value">
                                <span class="stats-label">Live</span>
                                <span class="stats-number" style="color: #1abc9c;">{{ $datas['fonts_live'] }}</span>
                            </div>
                            <div class="stats-value">
                                <span class="stats-label">Unlive</span>
                                <span class="stats-number" style="color: #e74c3c;">{{ $datas['fonts_unlive'] }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
            <div class="col-xl-3 col-md-6 mb-30">
                <a href="{{ route('show_cat') }}">
                    <div class="stats-card">
                        <div class="stats-title">Categories</div>
                        <div class="stats-value">
                            <span class="stats-label">Total</span>
                            <span class="stats-number">{{ $datas['cat'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Live</span>
                            <span class="stats-number" style="color: #1abc9c;">{{ $datas['cat_live'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Unlive</span>
                            <span class="stats-number" style="color: #e74c3c;">{{ $datas['cat_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 mb-30">
                <a href="{{ route('show_new_cat') }}">
                    <div class="stats-card">
                        <div class="stats-title">New Categories</div>
                        <div class="stats-value">
                            <span class="stats-label">Total</span>
                            <span class="stats-number">{{ $datas['new_categories_item'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Live</span>
                            <span class="stats-number" style="color: #1abc9c;">{{ $datas['new_categories_item_live'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Unlive</span>
                            <span class="stats-number" style="color: #e74c3c;">{{ $datas['new_categories_item_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 mb-30">
                <a href="{{ route('show_item') }}">
                    <div class="stats-card">
                        <div class="stats-title">Templates</div>
                        <div class="stats-value">
                            <span class="stats-label">Total</span>
                            <span class="stats-number">{{ $datas['item'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Live</span>
                            <span class="stats-number" style="color: #1abc9c;">{{ $datas['item_live'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Unlive</span>
                            <span class="stats-number" style="color: #e74c3c;">{{ $datas['item_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-30">
                <a href="{{ route('show_sticker_cat.index') }}">
                    <div class="stats-card">
                        <div class="stats-title">Sticker Categories</div>
                        <div class="stats-value">
                            <span class="stats-label">Total</span>
                            <span class="stats-number">{{ $datas['stk_cat'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Live</span>
                            <span class="stats-number" style="color: #1abc9c;">{{ $datas['stk_cat_live'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Unlive</span>
                            <span class="stats-number" style="color: #e74c3c;">{{ $datas['stk_cat_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 mb-30">
                <a href="{{ route('sticker_item.index') }}">
                    <div class="stats-card">
                        <div class="stats-title">Sticker Items</div>
                        <div class="stats-value">
                            <span class="stats-label">Total</span>
                            <span class="stats-number">{{ $datas['stk_item'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Live</span>
                            <span class="stats-number" style="color: #1abc9c;">{{ $datas['stk_item_live'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Unlive</span>
                            <span class="stats-number" style="color: #e74c3c;">{{ $datas['stk_item_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 mb-30">
                <a href="{{ route('show_bg_cat.index') }}">
                    <div class="stats-card">
                        <div class="stats-title">Background Categories</div>
                        <div class="stats-value">
                            <span class="stats-label">Total</span>
                            <span class="stats-number">{{ $datas['bg_cat'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Live</span>
                            <span class="stats-number" style="color: #1abc9c;">{{ $datas['bg_cat_live'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Unlive</span>
                            <span class="stats-number" style="color: #e74c3c;">{{ $datas['bg_cat_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 mb-30">
                <a href="{{ route('show_bg_item.index') }}">
                    <div class="stats-card">
                        <div class="stats-title">Background Items</div>
                        <div class="stats-value">
                            <span class="stats-label">Total</span>
                            <span class="stats-number">{{ $datas['bg_item'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Live</span>
                            <span class="stats-number" style="color: #1abc9c;">{{ $datas['bg_item_live'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Unlive</span>
                            <span class="stats-number" style="color: #e74c3c;">{{ $datas['bg_item_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-md-6 mb-30">
                <a href="{{ route('frame_categories.index') }}">
                    <div class="stats-card">
                        <div class="stats-title">Shape Categories</div>
                        <div class="stats-value">
                            <span class="stats-label">Total</span>
                            <span class="stats-number">{{ $datas['frame_cat_item'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Live</span>
                            <span class="stats-number" style="color: #1abc9c;">{{ $datas['frame_cat_item_live'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Unlive</span>
                            <span class="stats-number" style="color: #e74c3c;">{{ $datas['frame_cat_item_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 mb-30">
                <a href="{{ route('frame_items.index') }}">
                    <div class="stats-card">
                        <div class="stats-title">Shape Items</div>
                        <div class="stats-value">
                            <span class="stats-label">Total</span>
                            <span class="stats-number">{{ $datas['frame_item'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Live</span>
                            <span class="stats-number" style="color: #1abc9c;">{{ $datas['frame_item_live'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Unlive</span>
                            <span class="stats-number" style="color: #e74c3c;">{{ $datas['frame_item_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 mb-30">
                <a href="{{ route('show_v_cat') }}">
                    <div class="stats-card">
                        <div class="stats-title">Video Templates Category</div>
                        <div class="stats-value">
                            <span class="stats-label">Total</span>
                            <span class="stats-number">{{ $datas['video_cat_item'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Live</span>
                            <span class="stats-number" style="color: #1abc9c;">{{ $datas['video_cat_item_live'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Unlive</span>
                            <span class="stats-number" style="color: #e74c3c;">{{ $datas['video_cat_item_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 mb-30">
                <a href="{{ route('show_v_item') }}">
                    <div class="stats-card">
                        <div class="stats-title">Video Templates Items</div>
                        <div class="stats-value">
                            <span class="stats-label">Total</span>
                            <span class="stats-number">{{ $datas['video_template_item'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Live</span>
                            <span class="stats-number" style="color: #1abc9c;">{{ $datas['video_template_item_live'] }}</span>
                        </div>
                        <div class="stats-value">
                            <span class="stats-label">Unlive</span>
                            <span class="stats-number" style="color: #e74c3c;">{{ $datas['video_template_item_unlive'] }}</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        @if ($roleManager::isSeoExecutive(Auth::user()->user_type))
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-30">
                    <a href="{{ route('rejecte_task') }}">
                        <div class="stats-card">
                            <div class="stats-title">Rejected Tasks</div>
                            <div class="stats-value">
                                <span class="stats-label">Total</span>
                                <span class="stats-number" style="color: #e74c3c;">{{ $datas['pending_task'] }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        @endif

        @if ($roleManager::isAdmin(Auth::user()->user_type))
            <h2 class="section-title">Revenue Overview</h2>
            
            <div class="row">
                <div class="col-xl-3 col-lg-4 col-md-6 mb-30">
                    <div class="revenue-card">
                        <div class="revenue-title">Today's Revenue</div>
                        <div class="revenue-body">
                            <div class="revenue-item">
                                <a href="{{ route('transcation_logs') }}">
                                    <div class="revenue-item-label">Subscriptions</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['today_subs_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['today_subs_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('purchases') }}">
                                    <div class="revenue-item-label">Templates</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['today_templates_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['today_templates_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('video_transcation_logs') }}">
                                    <div class="revenue-item-label">Videos</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['today_video_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['today_video_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="revenue-total">
                            <div class="revenue-total-label">Total</div>
                            <div class="revenue-total-value">{{ $datas['today_inr'] }}</div>
                            <div class="revenue-total-value">{{ $datas['today_usd'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 mb-30">
                    <div class="revenue-card">
                        <div class="revenue-title">Yesterday's Revenue</div>
                        <div class="revenue-body">
                            <div class="revenue-item">
                                <a href="{{ route('transcation_logs') }}">
                                    <div class="revenue-item-label">Subscriptions</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['yesterday_subs_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['yesterday_subs_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('purchases') }}">
                                    <div class="revenue-item-label">Templates</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['yesterday_templates_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['yesterday_templates_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('video_transcation_logs') }}">
                                    <div class="revenue-item-label">Videos</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['yesterday_video_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['yesterday_video_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="revenue-total">
                            <div class="revenue-total-label">Total</div>
                            <div class="revenue-total-value">{{ $datas['yesterday_inr'] }}</div>
                            <div class="revenue-total-value">{{ $datas['yesterday_usd'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 mb-30">
                    <div class="revenue-card">
                        <div class="revenue-title">This Month's Revenue</div>
                        <div class="revenue-body">
                            <div class="revenue-item">
                                <a href="{{ route('transcation_logs') }}">
                                    <div class="revenue-item-label">Subscriptions</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['this_month_subs_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['this_month_subs_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('purchases') }}">
                                    <div class="revenue-item-label">Templates</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['this_month_templates_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['this_month_templates_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('video_transcation_logs') }}">
                                    <div class="revenue-item-label">Videos</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['this_month_video_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['this_month_video_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="revenue-total">
                            <div class="revenue-total-label">Total</div>
                            <div class="revenue-total-value">{{ $datas['this_month_inr'] }}</div>
                            <div class="revenue-total-value">{{ $datas['this_month_usd'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 mb-30">
                    <div class="revenue-card">
                        <div class="revenue-title">Last Month's Revenue</div>
                        <div class="revenue-body">
                            <div class="revenue-item">
                                <a href="{{ route('transcation_logs') }}">
                                    <div class="revenue-item-label">Subscriptions</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['last_month_subs_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['last_month_subs_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('purchases') }}">
                                    <div class="revenue-item-label">Templates</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['last_month_templates_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['last_month_templates_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('video_transcation_logs') }}">
                                    <div class="revenue-item-label">Videos</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['last_month_video_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['last_month_video_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="revenue-total">
                            <div class="revenue-total-label">Total</div>
                            <div class="revenue-total-value">{{ $datas['last_month_inr'] }}</div>
                            <div class="revenue-total-value">{{ $datas['last_month_usd'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-3 col-lg-4 col-md-6 mb-30">
                    <div class="revenue-card">
                        <div class="revenue-title">This Year's Revenue</div>
                        <div class="revenue-body">
                            <div class="revenue-item">
                                <a href="{{ route('transcation_logs') }}">
                                    <div class="revenue-item-label">Subscriptions</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['this_year_subs_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['this_year_subs_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('purchases') }}">
                                    <div class="revenue-item-label">Templates</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['this_year_templates_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['this_year_templates_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('video_transcation_logs') }}">
                                    <div class="revenue-item-label">Videos</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['this_year_video_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['this_year_video_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="revenue-total">
                            <div class="revenue-total-label">Total</div>
                            <div class="revenue-total-value">{{ $datas['this_year_inr'] }}</div>
                            <div class="revenue-total-value">{{ $datas['this_year_usd'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 mb-30">
                    <div class="revenue-card">
                        <div class="revenue-title">Last Year's Revenue</div>
                        <div class="revenue-body">
                            <div class="revenue-item">
                                <a href="{{ route('transcation_logs') }}">
                                    <div class="revenue-item-label">Subscriptions</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['last_year_subs_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['last_year_subs_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('purchases') }}">
                                    <div class="revenue-item-label">Templates</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['last_year_templates_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['last_year_templates_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('video_transcation_logs') }}">
                                    <div class="revenue-item-label">Videos</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['last_year_video_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['last_year_video_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="revenue-total">
                            <div class="revenue-total-label">Total</div>
                            <div class="revenue-total-value">{{ $datas['last_year_inr'] }}</div>
                            <div class="revenue-total-value">{{ $datas['last_year_usd'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 mb-30">
                    <div class="revenue-card">
                        <div class="revenue-title">Total Revenue</div>
                        <div class="revenue-body">
                            <div class="revenue-item">
                                <a href="{{ route('transcation_logs') }}">
                                    <div class="revenue-item-label">Subscriptions</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['total_subs_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['total_subs_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('purchases') }}">
                                    <div class="revenue-item-label">Templates</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['total_templates_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['total_templates_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="revenue-item">
                                <a href="{{ route('video_transcation_logs') }}">
                                    <div class="revenue-item-label">Videos</div>
                                    <div class="revenue-item-values">
                                        <div class="revenue-value">{{ $datas['total_video_inr'] }}</div>
                                        <div class="revenue-value">{{ $datas['total_video_usd'] }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="revenue-total">
                            <div class="revenue-total-label">Grand Total</div>
                            <div class="revenue-total-value">{{ $datas['total_inr'] }}</div>
                            <div class="revenue-total-value">{{ $datas['total_usd'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 mb-30">
                    <div class="revenue-card">
                        <div class="revenue-title">Razorpay & Links</div>
                        <div class="revenue-body">
                            <div class="revenue-item">
                                <div class="revenue-item-label">Today Razorpay</div>
                                <div class="revenue-item-values">
                                    <div class="revenue-value">{{ $datas['today_razorpay_inr'] }}</div>
                                </div>
                            </div>
                            
                            <div class="revenue-item">
                                <div class="revenue-item-label">Yesterday Razorpay</div>
                                <div class="revenue-item-values">
                                    <div class="revenue-value">{{ $datas['yesterday_razorpay_inr'] }}</div>
                                </div>
                            </div>
                            
                            <div class="revenue-item">
                                <div class="revenue-item-label">Editor History</div>
                                <div class="revenue-item-values">
                                    <div class="revenue-value">{{ $datas['editor_history'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="revenue-links">
                            <div class="revenue-link-item">
                                <a href="https://panel.craftyartapp.com/templates/export-datas" target="_blank">
                                    📊 Get Purchase Excel
                                </a>
                            </div>
                            <div class="revenue-link-item">
                                <a href="https://panel.craftyartapp.com/templates/export-sub-datas" target="_blank">
                                    📊 Get Subs Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div style="display: none;">
            <form method="post" id="dynamic_form" enctype="multipart/form-data">
                <span id="result"></span>
                @csrf
                <div class="row">
                    <div class="col-md-2 col-sm-12">
                        <div class="form-group">
                            <h6>Cache Version</h6>
                            <input class="form-control-file form-control" type="number" name="cache_ver"
                                value="{{ $datas['cache'] }}" required>
                        </div>
                    </div>
                    <div class="col-md-1 col-sm-12">
                        <div class="form-group">
                            <h6 style="opacity: 0;">.</h6>
                            <input class="btn btn-primary" type="submit" name="submit" value="update">
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    $('#dynamic_form').on('submit', function(event) {
        event.preventDefault();
        count = 0;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);
        $.ajax({
            url: 'update_cache_ver',
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                hideFields();
                if (data.error) {
                    var error_html = '';
                    for (var count = 0; count < data.error.length; count++) {
                        error_html += '<p>' + data.error[count] + '</p>';
                    }
                    $('#result').html('<div class="alert alert-danger">' + error_html + '</div>');
                } else {
                    $('#result').html('<div class="alert alert-success">' + data.success +
                        '</div>');
                }
                setTimeout(function() {
                    $('#result').html('');
                }, 3000);
            },
            error: function(error) {
                hideFields();
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    });

    function hideFields() {
        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";
    }
</script>
</body>

</html>
