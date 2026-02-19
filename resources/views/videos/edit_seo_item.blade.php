@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<style>
    /* Modern Card Design */
    .seo-edit-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 30px;
        margin-bottom: 25px;
    }

    /* Section Headers */
    .section-header {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f0f0f0;
        display: flex;
        align-items: center;
    }

    .section-header::before {
        content: '';
        width: 4px;
        height: 24px;
        background: #0059b2;
        margin-right: 12px;
        border-radius: 2px;
    }

    /* Form Labels */
    .form-group h6 {
        font-size: 14px;
        font-weight: 600;
        color: #4a4a4a;
        margin-bottom: 8px;
    }

    /* Form Controls */
    .form-control {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #0059b2;
        box-shadow: 0 0 0 3px rgba(0, 89, 178, 0.1);
        outline: none;
    }

    /* Buttons */
    .btn-primary {
        background: #0059b2;
        border: none;
        border-radius: 6px;
        padding: 12px 28px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: #004a94;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 89, 178, 0.3);
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        border-radius: 6px;
        padding: 12px 28px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }

    /* Tags Input */
    .bootstrap-tagsinput {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 8px 12px;
        min-height: 45px;
        width: 100%;
        display: block;
    }

    .bootstrap-tagsinput .tag {
        background: #0059b2;
        color: white;
        padding: 6px 12px;
        border-radius: 4px;
        margin-right: 6px;
        margin-bottom: 6px;
        font-size: 13px;
        display: inline-block;
    }

    .bootstrap-tagsinput input {
        border: none;
        box-shadow: none;
        outline: none;
        background-color: transparent;
        padding: 6px;
        margin: 0;
        width: auto;
        max-width: inherit;
        min-width: 150px;
    }

    .bootstrap-tagsinput .tag [data-role="remove"] {
        margin-left: 8px;
        cursor: pointer;
        opacity: 0.8;
    }

    .bootstrap-tagsinput .tag [data-role="remove"]:hover {
        opacity: 1;
    }

    /* Autocomplete Dropdown Styling */
    .custom-autocomplete-dropdown {
        position: absolute;
        z-index: 1000;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        max-height: 250px;
        overflow-y: auto;
        margin-top: 4px;
        width: 100%;
    }

    .autocomplete-item {
        padding: 10px 14px;
        cursor: pointer;
        border-bottom: 1px solid #f5f5f5;
        transition: background 0.2s ease;
    }

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .autocomplete-item:hover {
        background: #f8f9fa;
        color: #0059b2;
    }

    /* Form Group for Tags */
    .form-group {
        position: relative;
        margin-bottom: 20px;
    }

    .form-group small.text-muted {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        color: #6c757d;
    }

    /* Select Boxes */
    .selectpicker,
    .custom-select2 {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
    }

    /* Textarea */
    textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    /* Row Spacing */
    .row {
        margin-bottom: 15px;
    }

    /* HR Styling */
    hr {
        border: 0;
        height: 1px;
        background: #f0f0f0;
        margin: 25px 0;
    }

    /* Action Buttons Container */
    .action-buttons {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #f0f0f0;
        display: flex;
        gap: 12px;
    }

    /* Color Picker Container */
    .color_tags {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    #colorPicker {
        width: 50px;
        height: 45px;
        padding: 4px;
        cursor: pointer;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    #colorPicker:hover {
        border-color: #0059b2;
        box-shadow: 0 0 0 3px rgba(0, 89, 178, 0.1);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .seo-edit-card {
            padding: 20px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-primary,
        .btn-secondary {
            width: 100%;
        }
    }
</style>
<div class="main-container seo-access-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="seo-edit-card">
                <form id="editVideoSeoForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $dataArray['item']->id }}">

                    <div class="section-header">Basic Information</div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Video Name</h6>
                                <input class="form-control" type="text" value="{{ $dataArray['item']->video_name }}"
                                    id="video_name" name="video_name" required>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group category-dropbox-wrap">
                                <h6>Select Main Category</h6>
                                {{-- Main dropdown button --}}
                                <div class="input-subcategory-dropbox" id="parentCategoryInput">
                                    <span>
                                        @if (!empty($dataArray['select_category']) && isset($dataArray['select_category']['category_name']))
                                            {{ $dataArray['select_category']['category_name'] }}
                                        @else
                                            {{ '== none ==' }}
                                        @endif
                                    </span>
                                    <i style="font-size:18px" class="fa down-arrow-dropbox">&#xf107;</i>
                                </div>
                                {{-- Dropdown list --}}
                                <div class="custom-dropdown parent-category-input">
                                    <input type="text" id="categoryFilter" class="form-control-file form-control"
                                        placeholder="Search categories">
                                    <ul class="dropdown-menu-ul filter-wrap-list">
                                        <li class="category none-option">== none ==</li>
                                        @foreach ($dataArray['allCategories'] as $category)
                                            @php
                                                $classBold =
                                                    !empty($category['subcategories']) &&
                                                    isset($category['subcategories'][0])
                                                        ? 'has-children'
                                                        : 'has-parent';
                                                $selected =
                                                    $dataArray['item']['category_id'] == $category['id']
                                                        ? 'selected'
                                                        : '';
                                            @endphp
                                            <li class="category {{ $classBold }} {{ $selected }}"
                                                data-id="{{ $category['id'] }}"
                                                data-catname="{{ $category['category_name'] }}">
                                                <span>{{ $category['category_name'] }}</span>
                                                @if (!empty($category['subcategories']))
                                                    <ul class="subcategories">
                                                        @foreach ($category['subcategories'] as $subcategory)
                                                            @include('partials.subcategory-optgroup', [
                                                                'subcategory' => $subcategory,
                                                                'sub_category_id' => $subcategory['id'],
                                                                'sub_category_name' => $subcategory['category_name'],
                                                                'category' => $category
                                                            ])
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                {{-- Hidden field --}}
                                <input type="hidden" name="category_id" class="new_cat_id_item"
                                    value="{{ !empty($dataArray['select_category']) && isset($dataArray['select_category']['id']) ? $dataArray['select_category']['id'] : ($dataArray['item']->category_id ?? '0') }}">
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>String id</h6>
                                <input class="form-control" type="text" value="{{ $dataArray['item']->string_id }}"
                                    readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Keyword or Tags</h6>
                                <input type="text" data-role="tagsinput" class="form-control" id="keywords"
                                    name="keywords" placeholder="Add tags" value="{{ $dataArray['item']->keyword }}"
                                    autocomplete="off" required="" />
                                <small class="text-muted">Press Enter or comma to add a tag</small>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Premium Item</h6>
                                <select class="selectpicker form-control" data-style="btn-outline-primary"
                                    name="is_premium">
                                    @if ($dataArray['item']->is_premium == '1')
                                        <option value="1" selected>TRUE</option>
                                        <option value="0">FALSE</option>
                                    @else
                                        <option value="1">TRUE</option>
                                        <option value="0" selected>FALSE</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Status</h6>
                                <select class="selectpicker form-control" data-style="btn-outline-primary"
                                    name="status">
                                    @if ($dataArray['item']->status == '1')
                                        <option value="1" selected>LIVE</option>
                                        <option value="0">NOT LIVE</option>
                                    @else
                                        <option value="1">LIVE</option>
                                        <option value="0" selected>NOT LIVE</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>No Index</h6>
                                <select class="selectpicker form-control" data-style="btn-outline-primary"
                                    name="no_index">
                                    @if (($dataArray['item']->no_index ?? 1) == '1')
                                        <option value="1" selected>TRUE</option>
                                        <option value="0">FALSE</option>
                                    @else
                                        <option value="1">TRUE</option>
                                        <option value="0" selected>FALSE</option>
                                    @endif
                                </select>
                                <small class="text-muted">TRUE = noindex (not indexed by search engines)</small>
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="section-header">SEO Fields</div>
                    <div class="row">
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>ID Name</h6>
                                <input class="form-control" type="text" name="id_name"
                                    value="{{ $dataArray['item']->id_name ?? '' }}">
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>H2 Tag</h6>
                                <input type="text" class="form-control" id="h2_tag" name="h2_tag"
                                    value="{{ $dataArray['item']->h2_tag ?? '' }}" />
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Canonical Link</h6>
                                <input type="text" class="form-control" name="canonical_link"
                                    value="{{ $dataArray['item']->canonical_link ?? '' }}" />
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Meta Title</h6>
                                <input type="text" class="form-control" name="meta_title" id="meta_title"
                                    maxlength="60" value="{{ $dataArray['item']->meta_title ?? '' }}" />
                                <small id="metaCounter" class="text-muted">60 characters max</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Description</h6>
                                <textarea style="height: 150px" class="form-control" id="description" name="description">{{ $dataArray['item']->description ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Meta Description</h6>
                                <textarea style="height: 150px" class="form-control" id="meta_description" name="meta_description" maxlength="160">{{ $dataArray['item']->meta_description ?? '' }}</textarea>
                                <small class="text-muted">160 characters max</small>
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="section-header">Filter Row</div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Languages</h6>
                                @php
                                    if (isset($dataArray['item']->lang_id) && $dataArray['item']->lang_id != '') {
                                        $dataArray['item']->lang_id = is_array(json_decode($dataArray['item']->lang_id))
                                            ? $dataArray['item']->lang_id
                                            : json_encode([$dataArray['item']->lang_id]);
                                        $dataArray['langArray'] = $helperController::filterArrayOrder(
                                            $dataArray['item']->lang_id,
                                            $dataArray['langArray'],
                                            'id',
                                            1,
                                        );
                                    }
                                @endphp
                                <select class="custom-select2 form-control" data-style="btn-outline-primary"
                                    name="lang_id[]" multiple>
                                    @foreach ($dataArray['langArray'] as $lang)
                                        @if ($helperController::stringContain($dataArray['item']->lang_id ?? '', $lang->id))
                                            <option value="{{ $lang->id }}" selected="">
                                                {{ $lang->name }}
                                            </option>
                                        @else
                                            <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Theme</h6>
                                @php
                                    if (isset($dataArray['item']->theme_id) && $dataArray['item']->theme_id != '') {
                                        $dataArray['themeArray'] = $helperController::filterArrayOrder(
                                            $dataArray['item']->theme_id,
                                            $dataArray['themeArray'],
                                            'name',
                                        );
                                    }
                                @endphp
                                <select class="custom-select2 form-control" data-style="btn-outline-primary"
                                    multiple="multiple" name="theme_id[]">
                                    @foreach ($dataArray['themeArray'] as $theme)
                                        @if ($helperController::stringContain($dataArray['item']->theme_id ?? '', $theme->id))
                                            <option value="{{ $theme->id }}" selected="">
                                                {{ $theme->name }}
                                            </option>
                                        @else
                                            <option value="{{ $theme->id }}">{{ $theme->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Style</h6>
                                <select class="custom-select2 form-control" multiple="multiple"
                                    data-style="btn-outline-primary" name="styles[]">
                                    @foreach ($dataArray['styleArray'] as $style)
                                        @if ($helperController::stringContain($dataArray['item']->style_id ?? '', $style->id))
                                            <option value="{{ $style->id }}" selected="">
                                                {{ $style->name }}
                                            </option>
                                        @else
                                            <option value="{{ $style->id }}">{{ $style->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Orientation</h6>
                                <select class="form-control" data-style="btn-outline-primary" id="orientation"
                                    name="orientation">
                                    @foreach ($helperController::getOrientations() as $orientation)
                                        <option value="{{ $orientation }}"
                                            {{ $orientation == ($dataArray['item']->orientation ?? '') ? 'selected' : '' }}>
                                            {{ \Str::title($orientation) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Size</h6>
                                <select name="template_size" id="sizeInput" class="form-control">
                                    <option value="">== none ==</option>
                                    @foreach ($dataArray['sizes'] as $size)
                                        @php
                                            $orientation = $dataArray['item']->orientation ?? 'portrait';
                                            $currentOrientation = 'portrait';
                                            if ($size->width_ration == $size->height_ration) {
                                                $currentOrientation = 'square';
                                            } elseif ($size->width_ration > $size->height_ration) {
                                                $currentOrientation = 'landscape';
                                            }
                                        @endphp

                                        @if (isset($dataArray['item']->template_size))
                                            @if ($dataArray['item']->template_size == $size->id)
                                                <option value="{{ $size->id }}" selected>
                                                    {{ $size->size_name }}
                                                </option>
                                            @else
                                                <option value="{{ $size->id }}">
                                                    {{ $size->size_name }}</option>
                                            @endif
                                        @else
                                            <option value="{{ $size->id }}">
                                                {{ $size->size_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Religion</h6>
                                @php
                                    if (
                                        isset($dataArray['item']->religion_id) &&
                                        $dataArray['item']->religion_id != ''
                                    ) {
                                        $dataArray['religions'] = $helperController::filterArrayOrder(
                                            $dataArray['item']->religion_id,
                                            $dataArray['religions'],
                                            'id',
                                        );
                                    }
                                @endphp
                                <select class="custom-select2 form-control" data-style="btn-outline-primary"
                                    multiple="multiple" name="religion_id[]">
                                    @foreach ($dataArray['religions'] as $religion)
                                        @if ($helperController::stringContain($dataArray['item']->religion_id ?? '', $religion->id))
                                            <option value="{{ $religion->id }}" selected="">
                                                {{ $religion->religion_name }}
                                            </option>
                                        @else
                                            <option value="{{ $religion->id }}">{{ $religion->religion_name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Interest</h6>
                                @php
                                    if (isset($dataArray['item']->interest_id) && $dataArray['item']->interest_id != '') {
                                        $dataArray['interestArray'] = $helperController::filterArrayOrder(
                                            $dataArray['item']->interest_id,
                                            $dataArray['interestArray'],
                                            'id',
                                        );
                                    }
                                @endphp
                                <select class="custom-select2 form-control" multiple="multiple"
                                    data-style="btn-outline-primary" name="interest_id[]">
                                    @foreach ($dataArray['interestArray'] as $interest)
                                        @if ($helperController::stringContain($dataArray['item']->interest_id ?? '', $interest->id))
                                            <option value="{{ $interest->id }}" selected="">
                                                {{ $interest->name }}</option>
                                        @else
                                            <option value="{{ $interest->id }}">{{ $interest->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Is Premium</h6>
                                <select class="selectpicker form-control" data-style="btn-outline-primary"
                                    name="is_premium_filter">
                                    @if (($dataArray['item']->is_premium ?? '0') == '1')
                                        <option value="1" selected>TRUE</option>
                                        <option value="0">FALSE</option>
                                    @else
                                        <option value="1">TRUE</option>
                                        <option value="0" selected>FALSE</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Is Freemium</h6>
                                <select class="selectpicker form-control" data-style="btn-outline-primary"
                                    name="is_freemium">
                                    @if (($dataArray['item']->is_freemium ?? '0') == '1')
                                        <option value="1" selected>TRUE</option>
                                        <option value="0">FALSE</option>
                                    @else
                                        <option value="1">TRUE</option>
                                        <option value="0" selected>FALSE</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Select Date Range</h6>
                                @if (($dataArray['item']->start_date ?? null) != null && $dataArray['item']->start_date != '')
                                    @php
                                        try {
                                            $startDate = \Carbon\Carbon::parse($dataArray['item']->start_date)->format('m/d/Y');
                                            $endDate = \Carbon\Carbon::parse($dataArray['item']->end_date)->format('m/d/Y');
                                            $dateRangeValue = $startDate . ' - ' . $endDate;
                                        } catch (\Exception $e) {
                                            $dateRangeValue = $dataArray['item']->start_date . ' - ' . $dataArray['item']->end_date;
                                        }
                                    @endphp
                                    <input class="form-control datetimepicker-range" placeholder="Select Date"
                                        value="{{ $dateRangeValue }}"
                                        type="text" name="date_range" readonly>
                                @else
                                    <input class="form-control datetimepicker-range" placeholder="Select Date"
                                        type="text" name="date_range" readonly>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Colors</h6>
                                <div class="color_tags" style="display: flex; align-items: center; gap: 10px;">
                                    <div style="flex: 1;">
                                        <input type="text" id="colorTags" class="form-control" data-role="tagsinput"
                                            name="color_ids" value="{{ $dataArray['item']->color_ids ?? '' }}" 
                                            placeholder="Add color codes">
                                    </div>
                                    <input type="color" id="colorPicker" 
                                        style="width: 50px; height: 38px; padding: 2px; cursor: pointer; border: 1px solid #ddd; border-radius: 4px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fa fa-save"></i> Update SEO
                        </button>
                        <a href="{{ route('show_v_item') }}" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<datalist id="related_tag_list">
    @foreach ($dataArray['searchTagArray'] as $searchTag)
        <option value="{{ $searchTag->name }}"></option>
    @endforeach
</datalist>

@include('layouts.masterscript')
<style>
    /* Style for color tags */
    .bootstrap-tagsinput .tag {
        margin-right: 5px !important;
        margin-bottom: 5px !important;
        padding: 8px 12px !important;
        border-radius: 4px !important;
        font-weight: 500 !important;
        display: inline-block !important;
        color: white !important;
        border: none !important;
        font-size: 13px !important;
    }
    
    .bootstrap-tagsinput .tag[data-role="remove"] {
        margin-left: 8px !important;
        cursor: pointer !important;
        opacity: 0.8 !important;
    }
    
    .bootstrap-tagsinput .tag[data-role="remove"]:hover {
        opacity: 1 !important;
    }
    
    .bootstrap-tagsinput {
        width: 100% !important;
        min-height: 45px !important;
        padding: 8px !important;
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
    }
    
    .bootstrap-tagsinput input {
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
        background-color: transparent !important;
        padding: 0 6px !important;
        margin: 0 !important;
        width: auto !important;
        max-width: inherit !important;
    }
</style>
<script>
    $(document).ready(function() {
        $('#submitBtn').click(function(e) {
            e.preventDefault();

            var form = $('#editVideoSeoForm')[0];
            var formData = new FormData(form);

            $.ajax({
                url: "{{ route('v_item_seo.update', [$dataArray['item']->id]) }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function(response) {
                    if (response.status === false && response.error) {
                        alert(response.error);
                    } else {
                        alert('SEO data updated successfully!');
                        window.location.href = "{{ route('show_v_item') }}";
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 419) {
                        alert("Page expired or CSRF token mismatch. Please refresh and try again.");
                    } else if (xhr.status === 500) {
                        alert("Server error. Check backend.");
                    } else {
                        alert("Unexpected error occurred.");
                        console.log(xhr.responseText);
                    }
                }
            });
        });

        // Character counter for meta title
        $('#meta_title').on('input', function() {
            var remaining = 60 - $(this).val().length;
            $('#metaCounter').text(remaining + ' characters remaining');
        });

        // Initialize counter
        var metaTitleLength = $('#meta_title').val().length;
        $('#metaCounter').text((60 - metaTitleLength) + ' characters remaining');

        // Color picker functionality
        $('#colorPicker').on('change', function() {
            var color = $(this).val();
            var colorTags = $('#colorTags');
            
            // Add the color to the tags input
            colorTags.tagsinput('add', color);
            
            // Apply background color to the newly added tag
            applyColorToTags();
        });

        // Function to apply background colors to tags
        function applyColorToTags() {
            setTimeout(function() {
                $('.bootstrap-tagsinput .tag').each(function() {
                    var $tag = $(this);
                    var tagText = $tag.text().replace(/\s*×\s*$/, '').trim();
                    
                    // Check if it's a valid hex color
                    if (/^#[0-9A-F]{6}$/i.test(tagText)) {
                        $tag.css({
                            'background-color': tagText,
                            'border': 'none'
                        });
                        
                        // Calculate if we need light or dark text
                        var hex = tagText.replace('#', '');
                        var r = parseInt(hex.substr(0, 2), 16);
                        var g = parseInt(hex.substr(2, 2), 16);
                        var b = parseInt(hex.substr(4, 2), 16);
                        var brightness = ((r * 299) + (g * 587) + (b * 114)) / 1000;
                        
                        var textColor = brightness > 155 ? '#000000' : '#ffffff';
                        
                        $tag.css('color', textColor);
                        $tag.find('[data-role="remove"]').css('color', textColor);
                    }
                });
            }, 50);
        }

        // Apply colors on page load - multiple attempts to ensure it works
        setTimeout(applyColorToTags, 100);
        setTimeout(applyColorToTags, 300);
        setTimeout(applyColorToTags, 500);

        // Apply colors when tags are added or removed
        $('#colorTags').on('itemAdded itemRemoved', function() {
            applyColorToTags();
        });

        // Category dropdown functionality
        $(document).on('click', '#parentCategoryInput', function(e) {
            e.stopPropagation();
            if ($('.parent-category-input').hasClass('show')) {
                $('.parent-category-input').removeClass('show');
                $(this).removeClass('dropdown-open');
            } else {
                $('.parent-category-input').addClass('show');
                $(this).addClass('dropdown-open');
            }
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
                $('.custom-dropdown.parent-category-input.show').removeClass('show');
                $('#parentCategoryInput').removeClass('dropdown-open');
            }
        });

        // Category search filter
        $('#categoryFilter').on('input', function(e) {
            e.stopPropagation();
            var filterValue = $(this).val().toLowerCase();
            $('.category, .subcategory').each(function() {
                var text = $(this).text().toLowerCase();
                if (text.indexOf(filterValue) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Prevent dropdown from closing when clicking on search input
        $('#categoryFilter').on('click', function(e) {
            e.stopPropagation();
        });

        // Category selection
        $(document).on('click', '.filter-wrap-list .category', function(e) {
            e.stopPropagation();
            $('.category').removeClass('selected');
            $('.subcategory').removeClass('selected');
            
            var catId = $(this).data('id');
            var catName = $(this).data('catname');
            
            if (catId && catName) {
                // Update hidden input
                $('.new_cat_id_item').val(catId);
                
                // Update display
                $('#parentCategoryInput span').html(catName);
                
                // Add selected class to clicked item
                $(this).addClass('selected');
                
                // Close dropdown
                $('.parent-category-input').removeClass('show');
                $('#parentCategoryInput').removeClass('dropdown-open');
            }
        });

        // Subcategory selection
        $(document).on('click', '.filter-wrap-list .subcategory', function(e) {
            e.stopPropagation();
            $('.category').removeClass('selected');
            $('.subcategory').removeClass('selected');
            
            var catId = $(this).data('id');
            var catName = $(this).data('catname');
            
            if (catId && catName) {
                // Update hidden input
                $('.new_cat_id_item').val(catId);
                
                // Update display
                $('#parentCategoryInput span').html(catName);
                
                // Add selected class to clicked item
                $(this).addClass('selected');
                
                // Close dropdown
                $('.parent-category-input').removeClass('show');
                $('#parentCategoryInput').removeClass('dropdown-open');
            }
        });

        // Handle none option
        $(document).on('click', 'li.category.none-option', function(e) {
            e.stopPropagation();
            $('.new_cat_id_item').val('0');
            $('#parentCategoryInput span').html('== none ==');
            $('.filter-wrap-list .category, .filter-wrap-list .subcategory').removeClass('selected');
            $('.parent-category-input').removeClass('show');
            $('#parentCategoryInput').removeClass('dropdown-open');
        });

        // Tags input autocomplete with custom dropdown
        window.addEventListener("load", function() {
            // Style the tags input
            var tagsInputContainer = document.querySelector('.bootstrap-tagsinput');
            if (tagsInputContainer) {
                var tagsInput = tagsInputContainer.querySelector('input[type="text"]');
                if (tagsInput) {
                    tagsInput.setAttribute('autocomplete', 'off');
                    tagsInput.style.border = 'none';
                    tagsInput.style.outline = 'none';
                    tagsInput.style.boxShadow = 'none';
                    
                    // Create custom autocomplete dropdown
                    var dropdown = document.createElement('div');
                    dropdown.className = 'custom-autocomplete-dropdown';
                    dropdown.style.display = 'none';
                    tagsInputContainer.parentElement.appendChild(dropdown);
                    
                    // Get all available tags
                    var availableTags = [];
                    var datalist = document.getElementById('related_tag_list');
                    if (datalist) {
                        var options = datalist.querySelectorAll('option');
                        options.forEach(function(option) {
                            if (option.value) {
                                availableTags.push(option.value);
                            }
                        });
                    }
                    
                    // Handle input for autocomplete
                    tagsInput.addEventListener('input', function() {
                        var value = this.value.toLowerCase();
                        if (value.length < 2) {
                            dropdown.style.display = 'none';
                            return;
                        }
                        
                        var matches = availableTags.filter(function(tag) {
                            return tag.toLowerCase().indexOf(value) > -1;
                        }).slice(0, 10); // Limit to 10 suggestions
                        
                        if (matches.length > 0) {
                            dropdown.innerHTML = matches.map(function(tag) {
                                return '<div class="autocomplete-item">' + tag + '</div>';
                            }).join('');
                            dropdown.style.display = 'block';
                            
                            // Add click handlers
                            dropdown.querySelectorAll('.autocomplete-item').forEach(function(item) {
                                item.addEventListener('click', function() {
                                    $('#keywords').tagsinput('add', this.textContent);
                                    tagsInput.value = '';
                                    dropdown.style.display = 'none';
                                });
                            });
                        } else {
                            dropdown.style.display = 'none';
                        }
                    });
                    
                    // Hide dropdown when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!tagsInputContainer.contains(e.target) && !dropdown.contains(e.target)) {
                            dropdown.style.display = 'none';
                        }
                    });
                }
            }

            // Ensure tags input is properly styled
            $('#keywords').on('itemAdded itemRemoved', function() {
                var input = $('.bootstrap-tagsinput input[type="text"]');
                if (input.length) {
                    input.css({
                        'border': 'none',
                        'outline': 'none',
                        'box-shadow': 'none',
                        'min-width': '150px'
                    });
                }
            });
        });

        // Initialize date range picker
        if (typeof $.fn.daterangepicker !== 'undefined') {
            $('.datetimepicker-range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'MM/DD/YYYY'
                }
            });

            $('.datetimepicker-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            });

            $('.datetimepicker-range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        }
    });
</script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
</body>
</html>
