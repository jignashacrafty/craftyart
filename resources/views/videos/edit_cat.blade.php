   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')

<div class="main-container designer-access-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            <div class="pd-20 card-box mb-30">
                <form id="edit_v_cat_form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $datas['cat']->id }}">

                    <div class="form-group">
                        <h6>Category Name</h6>
                        <input class="form-control" type="text" name="category_name"
                            value="{{ $datas['cat']->category_name }}" required>
                    </div>

                    <div class="form-group">
                        <h6>Category Thumb</h6>
                        <input type="file" class="form-control" name="category_thumb">
                        @if ($datas['cat']->category_thumb)
                            <br>
                            <img src="{{ config('filesystems.storage_url') . $datas['cat']->category_thumb }}"
                                width="100">
                        @endif
                    </div>

                    <div class="form-group">
                        <h6>Sequence Number</h6>
                        <input class="form-control" type="text" name="sequence_number"
                            value="{{ $datas['cat']->sequence_number }}" required>
                    </div>

                    <div class="form-group category-dropbox-wrap">
                        <h6>Parent Category</h6>
                        <div class="input-subcategory-dropbox" id="parentCategoryInput">
                            <span>
                                {{ \App\Http\Controllers\HelperController::getParentCatName($datas['cat']->parent_category_id, \App\Models\Video\VideoCat::query()) ?? '== none ==' }}
                            </span>
                            <i class="fa down-arrow-dropbox">&#xf107;</i>
                        </div>

                        <div class="custom-dropdown parent-category-input">
                            <ul class="dropdown-menu-ul">
                                <li class="category none-option">== none ==</li>
                                @foreach ($datas['allCategories'] as $category)
                                    @php
                                        $classBold = !empty($category['subcategories']) ? 'has-children' : 'has-parent';
                                        $selected =
                                            $datas['cat']->parent_category_id == $category['id'] ? 'selected' : '';
                                    @endphp
                                    <li class="category {{ $classBold }} {{ $selected }}"
                                        data-id="{{ $category['id'] }}" data-catname="{{ $category['category_name'] }}">
                                        <span>{{ $category['category_name'] }}</span>
                                        @if (!empty($category['subcategories']))
                                            <ul class="subcategories">
                                                @foreach ($category['subcategories'] as $subcategory)
                                                    @include('partials.subcategory-optgroup', [
                                                        'subcategory' => $subcategory,
                                                        'sub_category_id' => $subcategory['id'],
                                                        'sub_category_name' => $subcategory['category_name'],
                                                    ])
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <input type="hidden" name="parent_category_id"
                            value="{{ $datas['cat']->parent_category_id ?? 0 }}">
                    </div>

                    <div class="form-group">
                        <h6>Status</h6>
                        <select class="form-control" name="status">
                            <option value="1" {{ $datas['cat']->status == 1 ? 'selected' : '' }}>LIVE</option>
                            <option value="0" {{ $datas['cat']->status == 0 ? 'selected' : '' }}>NOT LIVE</option>
                        </select>
                    </div>

                    <button class="btn btn-primary" type="submit">Update</button>
                </form>
            </div>

        </div>
    </div>
</div>

@include('layouts.masterscript')

<script>
    // Dropdown toggle
    $(document).on('click', '#parentCategoryInput', function() {
        $('.parent-category-input').toggleClass('show');
    });

    // Category selection
    $(document).on('click', ".category", function(event) {
        $(".category, .subcategory").removeClass("selected");
        var id = $(this).data('id');
        $("input[name='parent_category_id']").val(id);
        $("#parentCategoryInput span").text($(this).data('catname'));
        $('.parent-category-input').removeClass('show');
        $(this).addClass("selected");
    });

    $(document).on('click', '.subcategory', function(event) {
        event.stopPropagation();
        $(".category, .subcategory").removeClass("selected");
        var id = $(this).data('id');
        $("input[name='parent_category_id']").val(id);
        $("#parentCategoryInput span").text($(this).data('catname'));
        $('.parent-category-input').removeClass('show');
        $(this).addClass("selected");
    });

    $(document).on('click', 'li.category.none-option', function() {
        $("input[name='parent_category_id']").val("0");
        $("#parentCategoryInput span").text('== none ==');
        $('.parent-category-input').removeClass('show');
    });

    // Close dropdown on outside click
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
            $('.custom-dropdown.parent-category-input.show').removeClass('show');
        }
    });

    // AJAX Form Submit
    $('#edit_v_cat_form').on('submit', function(e) {
        e.preventDefault();

        let form = $(this)[0];
        let formData = new FormData(form);

        // CSRF Token setup for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let $btn = $(this).find("button[type='submit']");
        $btn.prop('disabled', true);

        $.ajax({
            url: "{{ route('v_cat.update', $datas['cat']->id) }}",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                showAlert('success', 'Category updated successfully.');
                setTimeout(() => {
                    window.location.href = "{{ route('show_v_cat') }}";
                }, 1200);
            },
            error: function(xhr) {
                let message = 'Something went wrong.';

                if (xhr.status === 422) {
                    // Validation errors
                    let errors = xhr.responseJSON.errors;
                    message = Object.values(errors).flat().join('\n');
                } else if (xhr.status === 403) {
                    // Access denied
                    message = xhr.responseJSON?.message || 'Access denied.';
                } else if (xhr.responseJSON?.message) {
                    // Other errors
                    message = xhr.responseJSON.message;
                }

                alert(message); // <-- popup box
            }

        });
    });
</script>

</body>

</html>
