 
        @guest

        @else
<style>
/* Custom styles for the sidebar */
#sidebar {
    min-width: 250px;
    max-width: 250px;
    background: unset !important;
    color: #fff;
    transition: all 0.3s;
}
#sidebar.collapsed {
    min-width: 80px;
    max-width: 80px;
    text-align: center;
}
#sidebar .sidebar-header {
    padding: 20px;
    background: #6d7fcc;
}
#sidebar ul.components {
    padding: 20px 0;
}
#sidebar ul li a {
    padding: 10px;
    display: block;
    font-size: 16px;
    color: #fff;
    font-weight: 300;
    letter-spacing: .03em;
    font-family: 'Inter', sans-serif;
    -webkit-transition: all .3s ease-in-out;
}
#sidebar ul li a:hover {
    color: #7386D5;
    background: #fff;
}
#sidebar ul li.active > a, a[aria-expanded="true"] {
    color: #fff;
    background: #6d7fcc;
}
#content {
    width: 100%;
    padding: 20px;
    min-height: 100vh;
    transition: all 0.3s;
}
#sidebarCollapse {
    width: 40px;
    height: 40px;
    background: #6d7fcc;
    color: #fff;
    border: none;
}
#sidebarCollapse:hover {
    background: #6d7fcc;
}
.navbar {
    padding: 10px 10px;
    background: #6d7fcc;
    color: #fff;
}
.navbar .btn {
    background: #6d7fcc;
    color: #fff;
}
.navbar .btn:hover {
    background: #343a40;
}

#sidebar ul li a .fa-angle-down {
    transition: transform 0.3s;
}
#sidebar ul li a[aria-expanded="true"] .fa-angle-down {
    transform: rotate(180deg);
}

</style>
<div class="header">
    <div class="header-left">
        <div class="menu-icon dw dw-menu"></div>
    </div>
    <div class="header-right pd-ltr-20">
    </i> {{ Auth::user()->name }} &nbsp &nbsp &nbsp</i>
        <a href="{{ route('logout') }}"onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="dw dw-logout"></i>{{ __('Log Out') }}
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</div>
@endguest
<div class="left-side-bar">
    <div class="menu-block customscroll">
        <nav id="sidebar" class="bg-dark">
            <div class="brand-logo">
                <a href="{{route('dashboard')}}">
                    <img src="{{asset('assets/vendors/images/deskapp-logo.svg')}}" alt="" class="dark-logo">
                    <img src="{{asset('assets/vendors/images/deskapp-logo-white.svg')}}" alt="" class="light-logo">
                </a>
                <div class="close-sidebar" data-toggle="left-sidebar-close">
                    <i class="ion-close-round"></i>
                </div>
            </div>

            <ul class="list-unstyled components">
                <li class="{{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}">
                    <a href="{{route('dashboard')}}">
                        <span class="micon dw dw-house-1"></span>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>
                {{-- @if ($helperController::isAdmin(Auth::user()->user_type))
                    <li>
                        <a href="{{route('show_app')}}">
                            <span class="micon dw dw-house-1"></span>
                            <span class="menu-text">Application</span>
                        </a>
                    </li>
                @endif --}}

                <li class="{{ ( Route::currentRouteName() == 'show_fonts' || Route::currentRouteName() == 'font_families' || Route::currentRouteName() == 'font_list' ) ? 'active' : '' }}">
                    <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="{{( Route::currentRouteName() == 'show_fonts' || Route::currentRouteName() == 'font_families' || Route::currentRouteName() == 'font_list' ) ? 'true':'false'}}" class="dropdown-toggle">
                        <span class="micon dw dw-house-1"></span>
                        <span class="menu-text">Fonts</span>
                    </a>
                    <ul class="collapse list-unstyled {{( Route::currentRouteName() == 'show_fonts' || Route::currentRouteName() == 'font_families' || Route::currentRouteName() == 'font_list' ) ? 'show' : ''}}" id="homeSubmenu">
                        <li class="{{(Route::currentRouteName() == 'show_fonts') ? 'select-menu' : ''}}">
                            <a href="{{route('show_fonts')}}">Mobile Fonts</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'font_families') ? 'select-menu' : ''}}">
                            <a href="{{route('font_families')}}">Web Font Familes</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'font_list') ? 'select-menu' : ''}}">
                            <a href="{{route('font_list')}}">Web Font List</a>
                        </li>
                    </ul>
                </li>
                {{-- @if ($helperController::isAdmin(Auth::user()->user_type))
                    <li class="{{ ( Route::currentRouteName() == 'show_orders' ) ? 'active' : '' }}">
                        <a href="{{route('show_orders')}}">
                            <span class="micon dw dw-house-1"></span>
                            <span class="menu-text">Custom Orders</span>
                        </a>
                    </li>
                @endif --}}

                @if ($helperController::isAdmin(Auth::user()->user_type))
                    <li class="{{ ( Route::currentRouteName() == 'show_v_cat' || Route::currentRouteName() == 'show_v_item' ) ? 'active' : '' }}">
                        <a href="#videoSubmenu" data-toggle="collapse" aria-expanded="{{ ( Route::currentRouteName() == 'show_v_cat' || Route::currentRouteName() == 'show_v_item' ) ? 'true' : 'false' }}" class="dropdown-toggle">
                            <span class="micon dw dw-house-1"></span>
                            <span class="menu-text">Video</span>
                        </a>
                        <ul class="collapse list-unstyled {{ ( Route::currentRouteName() == 'show_v_cat' || Route::currentRouteName() == 'show_v_item' ) ? 'show' : '' }}" id="videoSubmenu">
                            <li class="{{(Route::currentRouteName() == 'show_v_cat') ? 'select-menu' : ''}}">
                                <a href="{{route('show_v_cat')}}">Video Categories</a>
                            </li>
                            <li class="{{(Route::currentRouteName() == 'show_v_item') ? 'select-menu' : ''}}">
                                <a href="{{route('show_v_item')}}">Video Templates</a>
                            </li>
                        </ul>
                    </li>
                @endif

                <li class="{{ ( Route::currentRouteName() == 'show_style' || Route::currentRouteName() == 'show_theme'  || Route::currentRouteName() == 'show_keyword' || Route::currentRouteName() == 'show_search_tag' || Route::currentRouteName() == 'show_interest' || Route::currentRouteName() == 'show_lang' || Route::currentRouteName() == 'sizes.index' || Route::currentRouteName() == 'colors.index' || Route::currentRouteName() == 'religions.index')  ? 'active' : '' }}">
                    <a href="#filterSubmenu" data-toggle="collapse" aria-expanded="{{ ( Route::currentRouteName() == 'show_style' || Route::currentRouteName() == 'show_theme' || Route::currentRouteName() == 'show_keyword' || Route::currentRouteName() == 'show_search_tag' || Route::currentRouteName() == 'show_interest' || Route::currentRouteName() == 'show_lang' || Route::currentRouteName() == 'sizes.index' || Route::currentRouteName() == 'colors.index' || Route::currentRouteName() == 'religions.index') ? 'true' : 'false' }}" class="dropdown-toggle">
                        <span class="micon dw dw-house-1"></span>
                        <span class="menu-text">Filters</span>
                    </a>
                    <ul class="collapse list-unstyled {{ ( Route::currentRouteName() == 'show_style' || Route::currentRouteName() == 'show_theme'  || Route::currentRouteName() == 'show_keyword' || Route::currentRouteName() == 'show_search_tag' || Route::currentRouteName() == 'show_lang' || Route::currentRouteName() == 'sizes.index' || Route::currentRouteName() == 'colors.index' || Route::currentRouteName() == 'religions.index' || Route::currentRouteName() == 'show_interest') ? 'show' : '' }}" id="filterSubmenu">

                        <li class="{{(Route::currentRouteName() == 'show_style') ? 'select-menu' : ''}}">
                            <a href="{{route('show_style')}}">Style</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'show_theme') ? 'select-menu' : ''}}">
                            <a href="{{route('show_theme')}}">Theme</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'show_keyword') ? 'select-menu' : ''}}">
                            <a href="{{route('show_keyword')}}">Keywords</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'show_search_tag') ? 'select-menu' : ''}}">
                            <a href="{{route('show_search_tag')}}">Search Tag</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'show_interest') ? 'select-menu' : ''}}">
                            <a href="{{route('show_interest')}}">Interest</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'show_lang') ? 'select-menu' : ''}}">
                            <a href="{{route('show_lang')}}">Language</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'sizes.index') ? 'select-menu' : ''}}">
                            <a href="{{route('sizes.index')}}">Size</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'colors.index') ? 'select-menu' : ''}}">
                            <a href="{{route('colors.index')}}">Color</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'religions.index') ? 'select-menu' : ''}}">
                            <a href="{{route('religions.index')}}">Relegion</a>
                        </li>
                    </ul>
                </li>

                <li class="{{ ( Route::currentRouteName() == 'create_editable_mode' ) ? 'active' : '' }}">
                    <a href="{{route('create_editable_mode')}}">
                        <span class="micon dw dw-house-1"></span>
                        <span class="menu-text">Editable Title</span>
                    </a>
                </li>
                <li class="{{ ( Route::currentRouteName() == 'show_item' || Route::currentRouteName() == 'show_cat' || Route::currentRouteName() == 'show_new_cat' ) ? 'active' : '' }}">
                    <a href="#TemplatesSubmenu" data-toggle="collapse" aria-expanded="{{ ( Route::currentRouteName() == 'show_item' || Route::currentRouteName() == 'show_cat' || Route::currentRouteName() == 'show_new_cat' ) ? 'true' : 'false' }}" class="dropdown-toggle">
                        <span class="micon dw dw-house-1"></span>
                        <span class="menu-text">Templates</span>
                    </a>
                    <ul class="collapse list-unstyled {{ ( Route::currentRouteName() == 'show_item' || Route::currentRouteName() == 'show_cat' || Route::currentRouteName() == 'show_new_cat' ) ? 'show' : '' }}" id="TemplatesSubmenu">
                        <li class="{{(Route::currentRouteName() == 'show_item') ? 'select-menu' : ''}}">
                            <a href="{{route('show_item')}}">Show Templates</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'show_cat') ? 'select-menu' : ''}}">
                            <a href="{{route('show_cat')}}">Category</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'show_new_cat') ? 'select-menu' : ''}}">
                            <a href="{{route('show_new_cat')}}">NewCategory</a>
                        </li>
                    </ul>
                </li>

                <li class="{{ ( Route::currentRouteName() == 'shape_categories.index' || Route::currentRouteName() == 'shape_items.index' || Route::currentRouteName() == 'show_sticker_cat' || Route::currentRouteName() == 'show_sticker_item'   ) ? 'active' : '' }}">
                    <a href="#ElementSubmenu" data-toggle="collapse" aria-expanded="{{ ( Route::currentRouteName() == 'shape_categories.index' || Route::currentRouteName() == 'shape_items.index' || Route::currentRouteName() == 'show_sticker_cat' || Route::currentRouteName() == 'show_sticker_item'   ) ? 'true' : 'false' }}" class="dropdown-toggle">
                        <span class="micon dw dw-house-1"></span>
                        <span class="menu-text">Element</span>
                    </a>

                    <ul class="collapse list-unstyled {{ ( Route::currentRouteName() == 'shape_categories.index' || Route::currentRouteName() == 'shape_items.index' || Route::currentRouteName() == 'show_sticker_cat' || Route::currentRouteName() == 'show_sticker_item' ) ? 'show' : '' }}" id="ElementSubmenu">
                        <li class="{{ ( Route::currentRouteName() == 'shape_categories.index' || Route::currentRouteName() == 'shape_items.index'  ) ? 'active' : '' }}">
                            <a href="#ShapeSubmenu" data-toggle="collapse" aria-expanded="{{ ( Route::currentRouteName() == 'shape_categories.index' || Route::currentRouteName() == 'shape_items.index'  ) ? 'true' : 'false' }}" class="dropdown-toggle">
                                <span class="micon dw dw-house-1"></span>
                                <span class="menu-text">Shape</span>
                            </a>
                            <ul class="collapse list-unstyled {{ ( Route::currentRouteName() == 'shape_categories.index' || Route::currentRouteName() == 'shape_items.index'  ) ? 'show' : '' }}" id="ShapeSubmenu">
                                <li class="{{(Route::currentRouteName() == 'shape_categories.index') ? 'select-menu' : ''}}">
                                    <a href="{{route('shape_categories.index')}}">Category</a>
                                </li>
                                <li class="{{(Route::currentRouteName() == 'shape_items.index') ? 'select-menu' : ''}}">
                                    <a href="{{route('shape_items.index')}}">Item</a>
                                </li>
                            </ul>
                        </li>
                        <li class="{{ ( Route::currentRouteName() == 'show_sticker_cat' || Route::currentRouteName() == 'show_sticker_item'  ) ? 'active' : '' }}">
                            <a href="#StickerSubmenu" data-toggle="collapse" aria-expanded="{{ ( Route::currentRouteName() == 'show_sticker_cat' || Route::currentRouteName() == 'show_sticker_item'  ) ? 'true' : 'false' }}" class="dropdown-toggle">
                                <span class="micon dw dw-house-1"></span>
                                <span class="menu-text">Sticker</span>
                            </a>
                            <ul class="collapse list-unstyled {{ ( Route::currentRouteName() == 'show_sticker_cat' || Route::currentRouteName() == 'show_sticker_item'  ) ? 'show' : '' }}" id="StickerSubmenu">
                                <li class="{{(Route::currentRouteName() == 'show_sticker_cat') ? 'select-menu' : ''}}">
                                    <a href="{{route('show_sticker_cat')}}">Category</a>
                                </li>
                                <li class="{{(Route::currentRouteName() == 'show_sticker_item') ? 'select-menu' : ''}}">
                                    <a href="{{route('show_sticker_item')}}">Item</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="{{ ( Route::currentRouteName() == 'show_bg_cat' || Route::currentRouteName() == 'show_bg_item' ) ? 'active' : '' }}">
                    <a href="#BackgroundSubMenu" data-toggle="collapse" aria-expanded="{{ ( Route::currentRouteName() == 'show_bg_cat' || Route::currentRouteName() == 'show_bg_item' ) ? 'true' : 'false' }}" class="dropdown-toggle">
                        <span class="micon dw dw-house-1"></span>
                        <span class="menu-text">Background</span>
                    </a>
                    <ul class="collapse list-unstyled {{ ( Route::currentRouteName() == 'show_bg_cat' || Route::currentRouteName() == 'show_bg_item' ) ? 'show' : '' }}" id="BackgroundSubMenu">
                        <li class="{{(Route::currentRouteName() == 'show_bg_cat') ? 'select-menu' : ''}}">
                            <a href="{{route('show_bg_cat')}}">Category</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'show_bg_item') ? 'select-menu' : ''}}">
                            <a href="{{route('show_bg_item')}}">Item</a>
                        </li>
                    </ul>
                </li>

                <li class="{{ ( Route::currentRouteName() == 'show_packages' || Route::currentRouteName() == 'payment_setting'  || Route::currentRouteName() == 'transcation_logs' ) ? 'active' : '' }}">
                    <a href="#SubscriptionSubMenu" data-toggle="collapse" aria-expanded="{{ ( Route::currentRouteName() == 'show_packages' || Route::currentRouteName() == 'payment_setting' || Route::currentRouteName() == 'transcation_logs'  ) ? 'true' : 'false' }}" class="dropdown-toggle">
                        <span class="micon dw dw-house-1"></span>
                        <span class="menu-text">Old Plan Subscription</span>
                    </a>
                    <ul class="collapse list-unstyled {{ ( Route::currentRouteName() == 'show_packages' || Route::currentRouteName() == 'payment_setting' || Route::currentRouteName() == 'transcation_logs'  ) ? 'show' : '' }}" id="SubscriptionSubMenu">
                        <li class="{{(Route::currentRouteName() == 'show_packages') ? 'select-menu' : ''}}">
                            <a href="{{route('show_packages')}}">Subscription Package</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'payment_setting') ? 'select-menu' : ''}}">
                            <a href="{{route('payment_setting')}}">Payment Setting</a>
                        </li>
                        <li class="{{(Route::currentRouteName() == 'transcation_logs') ? 'select-menu' : ''}}">
                            <a href="{{route('transcation_logs')}}">Transaction Logs</a>
                        </li>
                    </ul>
                </li>

                <li class="{{ ( Route::currentRouteName() == 'categoryFeatures.index' || Route::currentRouteName() == 'features.index' || Route::currentRouteName() == 'plans.index' || Route::currentRouteName() == 'plans.create' ) ? 'active' : '' }}">
                    <a href="#PricePlanSubmenu" data-toggle="collapse" aria-expanded="{{ ( Route::currentRouteName() == 'categoryFeatures.index' || Route::currentRouteName() == 'features.index' || Route::currentRouteName() == 'plans.index' || Route::currentRouteName() == 'plans.create' ) ? 'true' : 'false' }}" class="dropdown-toggle">
                        <span class="micon dw dw-house-1"></span>
                        <span class="menu-text">New Subscription</span>
                    </a>
                    <ul class="collapse list-unstyled {{ ( Route::currentRouteName() == 'categoryFeatures.index' || Route::currentRouteName() == 'features.index' || Route::currentRouteName() == 'plans.index' || Route::currentRouteName() == 'plans.create' || Route::currentRouteName() == 'plans.edit' ) ? 'show' : '' }}" id="PricePlanSubmenu">

                        <li class="{{ ( Route::currentRouteName() == 'categoryFeatures.index' || Route::currentRouteName() == 'features.index' || Route::currentRouteName() == 'plans.index' || Route::currentRouteName() == 'plans.create' || Route::currentRouteName() == 'plans.edit' ) ? 'active' : '' }}">
                            <a href="#PricePlanSubMenu" data-toggle="collapse" aria-expanded="{{ ( Route::currentRouteName() == 'categoryFeatures.index' || Route::currentRouteName() == 'features.index' || Route::currentRouteName() == 'plans.index' || Route::currentRouteName() == 'plans.create' || Route::currentRouteName() == 'plans.edit' ) ? 'true' : 'false' }}" class="dropdown-toggle">
                                <span class="micon dw dw-house-1"></span>
                                <span class="menu-text">New Subscription Package</span>
                            </a>
                            <ul class="collapse list-unstyled {{ ( Route::currentRouteName() == 'categoryFeatures.index' || Route::currentRouteName() == 'features.index' || Route::currentRouteName() == 'plans.index' || Route::currentRouteName() == 'plans.create' || Route::currentRouteName() == 'plans.edit' ) ? 'show' : '' }}" id="PricePlanSubMenu">
                                <li class="{{(Route::currentRouteName() == 'categoryFeatures.index') ? 'select-menu' : ''}}">
                                    <a href="{{route('categoryFeatures.index')}}">Category Feature</a>
                                </li>
                                <li class="{{(Route::currentRouteName() == 'features.index') ? 'select-menu' : ''}}">
                                    <a href="{{route('features.index')}}">Feature</a>
                                </li>
                                <li class="{{(Route::currentRouteName() == 'plans.index' || Route::currentRouteName() == 'plans.create' || Route::currentRouteName() == 'plans.edit') ? 'select-menu' : ''}}">
                                    <a href="{{route('plans.index')}}">Plans</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="{{ ( Route::currentRouteName() == 'import_json' ) ? 'active' : '' }}">
                    <a href="{{route('import_json')}}">
                        <span class="micon dw dw-house-1"></span>
                        <span class="menu-text">Import Json</span>
                    </a>
                </li>
                @if ($helperController::isAdmin(Auth::user()->user_type))
                    <li class="{{ ( Route::currentRouteName() == 'show_users' ) ? 'active' : '' }}">
                        <a href="{{route('show_users')}}">
                            <span class="micon dw dw-house-1"></span>
                            <span class="menu-text">Users</span>
                        </a>
                    </li>

                    <li class="{{ ( Route::currentRouteName() == 'notification_setting' ) ? 'active' : '' }}">
                        <a href="{{route('notification_setting')}}">
                            <span class="micon dw dw-house-1"></span>
                            <span class="menu-text">Notification Setting</span>
                        </a>
                    </li>
                    <li class="{{ ( Route::currentRouteName() == 'show_messages' ) ? 'active' : '' }}">
                        <a href="{{route('show_messages')}}">
                            <span class="micon dw dw-house-1"></span>
                            <span class="menu-text">In App Message</span>
                        </a>
                    </li>
                    <li class="{{ ( Route::currentRouteName() == 'show_feedbacks' ) ? 'active' : '' }}">
                        <a href="{{route('show_feedbacks')}}">
                            <span class="micon dw dw-house-1"></span>
                            <span class="menu-text">Feedbacks</span>
                        </a>
                    </li>

                    <li class="{{ ( Route::currentRouteName() == 'show_reviews' ) ? 'active' : '' }}">
                        <a href="{{route('show_reviews')}}">
                            <span class="micon dw dw-house-1"></span>
                            <span class="menu-text">Reviews</span>
                        </a>
                    </li>

                @endif
                @if ( $helperController::getUserType(Auth::user()->user_type) == "Admin" || $helperController::getUserType(Auth::user()->user_type) == "Manager" )
                    <li class="{{ ( Route::currentRouteName() == 'show_employee' ) ? 'active' : '' }}">
                        <a href="{{route('show_employee')}}">
                            <span class="micon dw dw-house-1"></span>
                            <span class="menu-text">Employees</span>
                        </a>
                    </li>
                    <li class="{{ ( Route::currentRouteName() == 'show_contacts' ) ? 'active' : '' }}">
                        <a href="{{route('show_contacts')}}">
                            <span class="micon dw dw-house-1"></span>
                            <span class="menu-text">Contacts</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</div>
<div class="mobile-menu-overlay"></div>


