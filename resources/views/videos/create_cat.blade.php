   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container designer-access-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="pd-20 card-box mb-30">
                <form method="post" action="submit_v_cat" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <h6>Category Name</h6>
                        <input class="form-control" type="textname" name="category_name" required>
                    </div>

                    <div class="form-group">
                        <h6>Category Thumb</h6>
                        <input type="file" class="form-control-file form-control height-auto" name="category_thumb">
                    </div>

                    <div class="form-group">
                        <h6>Sequence Number</h6>
                        <input class="form-control" type="textname" name="sequence_number" required>
                    </div>

                    <div class="form-group category-dropbox-wrap">
                        <h6>Parent Category</h6>

                        <div class="input-subcategory-dropbox" id="parentCategoryInput"><span>== none ==</span> <i
                                style="font-size:18px" class="fa down-arrow-dropbox">&#xf107;</i></div>
                        <div class="custom-dropdown parent-category-input">
                            <ul class="dropdown-menu-ul">
                                <li class="category none-option">== none ==</li>
                                @foreach ($allCategories as $category)
                                    @php
                                        $classBold =
                                            !empty($category['subcategories']) && isset($category['subcategories'][0])
                                                ? 'has-children'
                                                : 'has-parent';
                                    @endphp
                                    <li class="category {{ $classBold }}" data-id="{{ $category['id'] }}"
                                        data-catname="{{ $category['category_name'] }}">
                                        {{ $category['category_name'] }}
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
                    </div>
                    <input type="hidden" name="parent_category_id" value="0">

                    <div class="form-group">
                        <h6>Status</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary" name="status">
                                <option value="1">LIVE</option>
                                <option value="0">NOT LIVE</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <input class="btn btn-primary" type="submit" name="submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    window.onload = function() {
        console.log("category jjjjj");

        function showConfirmation() {
            return confirm("Are you sure you want to reload this page?");
        }
        window.addEventListener("beforeunload", function(event) {
            // Show the confirmation dialog
            if (showConfirmation()) {
                window.location.reload(true);
            } else {
                event.preventDefault();
            }
        });
    };


    $(document).on('click', '#parentCategoryInput', function() {
        if ($('.parent-category-input').hasClass('show')) {
            $('.parent-category-input').removeClass('show');
        } else {
            $(".parent-category-input").addClass('show');
        }
    });

    $(document).on("click", ".category", function(event) {
        $(".category").removeClass("selected");
        $(".subcategory").removeClass("selected");
        var id = $(this).data('id');
        $("input[name='parent_category_id']").val(id);
        $("#parentCategoryInput span").html($(this).data('catname'));
        $('.parent-category-input').removeClass('show');
        $(this).addClass("selected");
    });

    $(document).on("click", ".subcategory", function(event) {
        event.stopPropagation();
        $(".category").removeClass("selected");
        $(".subcategory").removeClass("selected");
        var id = $(this).data('id');
        var parentId = $(this).data('pid');
        $("input[name='parent_category_id']").val(id);
        $('.parent-category-input').removeClass('show');
        $("#parentCategoryInput span").html($(this).data('catname'));
        $(this).addClass("selected");
    });

    $(document).on("click", "li.category.none-option", function() {
        $("input[name='parent_category_id']").val("0");
        $('.parent-category-input').removeClass('show');
        $("#parentCategoryInput span").html('== none ==');
    });
</script>
</body>

</html>
