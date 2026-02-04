   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container  designer-access-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="pd-20 card-box mb-30">
                <form id="editVideoForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $dataArray['item']->id }}">
                    @csrf

                    <div class="row">

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Video Name</h6>
                                <input class="form-control" type="textname" value="{{ $dataArray['item']->video_name }}"
                                    id="video_name" name="video_name" required>
                            </div>
                        </div>


                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Template id</h6>
                                <input class="form-control" type="textname"
                                    value="{{ $dataArray['item']->relation_id }}" id="relation_id" name="relation_id"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>String id</h6>
                                <input class="form-control" type="textname" value="{{ $dataArray['item']->string_id }}"
                                    readonly>
                            </div>
                        </div>

                    </div>

                    <div class="form-group">
                        <h6>Video Thumb</h6>
                        <input type="file" id="video_thumb" class="form-control-file form-control height-auto"
                            name="video_thumb">
                        <img src="{{ config('filesystems.storage_url') }}{{ $dataArray['item']->video_thumb }}"
                            width="100" />
                    </div>

                    <div class="form-group">
                        <h6>Video File</h6>
                        <input type="file" id="video_file" class="form-control-file form-control height-auto"
                            name="video_file">
                        <label> {{ $dataArray['item']->video_url }} </label>
                    </div>

                    <div class="form-group">
                        <h6>Zip File</h6>
                        <input type="file" id="zip_file" class="form-control-file form-control height-auto"
                            name="zip_file">
                        <label> {{ $dataArray['item']->video_zip_url }} </label>
                    </div>

                    <div class="row">
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Pages</h6>
                                <input class="form-control" id="pages" type="number"
                                    value="{{ $dataArray['item']->pages }}" name="pages" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Width</h6>
                                <input class="form-control" id="width" type="number"
                                    value="{{ $dataArray['item']->width }}" name="width" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Height</h6>
                                <input class="form-control" id="height" type="number"
                                    value="{{ $dataArray['item']->height }}" name="height" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Watermark Height</h6>
                                <input class="form-control" id="watermark_height" type="number"
                                    value="{{ $dataArray['item']->watermark_height }}" name="watermark_height"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Video Type</h6>
                                <select id="template_type" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="template_type" required>
                                    @foreach ($dataArray['templateType'] as $templateType)
                                        @if ($dataArray['item']->template_type == $templateType->value)
                                            <option value="{{ $templateType->value }}" selected="">
                                                {{ $templateType->type }}
                                            </option>
                                        @else
                                            <option value="{{ $templateType->value }}">{{ $templateType->type }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Show Text Front</h6>
                                <select id="do_front_lottie" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="do_front_lottie" required>
                                    @if ($dataArray['item']->do_front_lottie == 1 || $dataArray['item']->do_front_lottie == '1')
                                        <option value="1" selected>True</option>
                                        <option value="0">False</option>
                                    @else
                                        <option value="1">True</option>
                                        <option value="0" selected>False</option>
                                    @endif

                                </select>
                            </div>
                        </div>

                    </div>



                    <div class="pd-20 card-box mb-30" style="background-color: #eaeaea;">
                        <div class="form-group">
                            <br />
                            <div class="row">
                                <div class="col-md-10 col-sm-12">
                                    <h6>Images</h6>
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <button type="button" name="add_img_id" id="add_img_id"
                                        class="btn btn-primary form-control-file">Add</button>
                                </div>
                            </div>

                        </div>

                        <div id="dynamic_img_field">
                            @for ($i = 0; $i < count($dataArray['editable_image']); $i++)
                                <div class="row">
                                    <hr size="8" width="100%" color="black">
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <h6>Id</h6>
                                            <input type="text" class="form-control" placeholder="key"
                                                id="img_key" name="img_key[]"
                                                value="{{ $dataArray['editable_image'][$i]['key'] }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <h6>Is Shape</h6>
                                            <select class="selectpicker form-control" data-style="btn-outline-primary"
                                                name="img_shape[]" required>
                                                @if ($dataArray['editable_image'][$i]['isShape'] == 0)
                                                    <option value="0" selected="true">False</option>
                                                    <option value="1">True</option>
                                                @else
                                                    <option value="0">False</option>
                                                    <option value="1" selected="true">True</option>
                                                @endif

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 pd-19">
                                        <div class="form-group">
                                            <h6 style="opacity: 0;">.</h6>
                                            <button type="button" name="remove_img_id" id="remove_img_id"
                                                class="btn btn-danger form-control-file">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            @endfor

                        </div>

                    </div>


                    <div class="pd-20 card-box mb-30" style="background-color: #eaeaea;">
                        <div class="form-group">
                            <br />
                            <div class="row">
                                <div class="col-md-10 col-sm-12">
                                    <h6>Text</h6>
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <button type="button" name="add_text_id" id="add_text_id"
                                        class="btn btn-primary form-control-file">Add</button>
                                </div>
                            </div>

                        </div>

                        <div id="dynamic_text_field">
                            @for ($i = 0; $i < count($dataArray['editable_text']); $i++)
                                <div class="row">
                                    <hr size="8" width="100%" color="black">
                                    <div class="col-md-2 col-sm-12">
                                        <div class="form-group">
                                            <h6>Key</h6>
                                            <input type="text" class="form-control" placeholder="key"
                                                id="key" name="key[]"
                                                value="{{ $dataArray['editable_text'][$i]['key'] }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-12">
                                        <div class="form-group">
                                            <h6>Title</h6>
                                            <input type="text" class="form-control" placeholder="Title"
                                                id="title" name="title[]"
                                                value="{{ $dataArray['editable_text'][$i]['title'] }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-12">
                                        <div class="form-group">
                                            <h6>Font Family</h6>
                                            <input type="text" class="form-control" placeholder="Font Family"
                                                id="font_family" name="font_family[]"
                                                value="{{ $dataArray['editable_text'][$i]['font_family'] }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <h6>Value</h6>
                                            <textarea style="height: 80px" class="form-control" id="editable_text_id" name="editable_text_id[]" required>{{ $dataArray['editable_text'][$i]['value'] }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-2 pd-19">
                                        <div class="form-group">
                                            <h6 style="opacity: 0;">.</h6>
                                            <button type="button" name="remove_text_id" id="remove_text_id"
                                                class="btn btn-danger form-control-file">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            @endfor

                        </div>

                    </div>

                    <div class="form-group">
                        <h6>Encrypted</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary"
                                name="encrypted" id="encrypted">
                                @if ($dataArray['item']->encrypted == '1')
                                    <option value="0">FALSE</option>
                                    <option value="1" selected="">TRUE</option>
                                @else
                                    <option value="0" selected="">FALSE</option>
                                    <option value="1">TRUE</option>
                                @endif
                            </select>
                        </div>
                    </div>


                    @if ($dataArray['item']->encrypted == '1')
                        <div id="encryption_field">
                            <div class="form-group">
                                <h6>Encryption Key</h6>
                                <input class="form-control" type="textname" id="encryption_key"
                                    name="encryption_key" value="{{ $dataArray['item']->encryption_key }}" required>
                            </div>
                        </div>
                    @else
                        <div id="encryption_field" style="display: none;">
                            <div class="form-group">
                                <h6>Encryption Key</h6>
                                <input class="form-control" type="textname" id="encryption_key"
                                    name="encryption_key">
                            </div>
                        </div>
                    @endif

                    <div class="form-group">
                        <h6>Change Music</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary"
                                name="change_music">
                                @if ($dataArray['item']->change_music == '1')
                                    <option value="0">FALSE</option>
                                    <option value="1" selected="">TRUE</option>
                                @else
                                    <option value="0" selected="">FALSE</option>
                                    <option value="1">TRUE</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Select Main Category</h6>
                        <div class="col-sm-20">
                            <select id="category_id" class="selectpicker form-control"
                                data-style="btn-outline-primary" name="category_id" required>

                                @foreach ($dataArray['cat'] as $cat)
                                    @if ($dataArray['item']->category_id == $cat->id)
                                        <option value="{{ $cat->id }}" selected="">{{ $cat->category_name }}
                                        </option>
                                    @else
                                        <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                    @endif
                                @endforeach


                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Keyword or Tags</h6>
                        <div class="col-sm-20">
                            <input type="text" data-role="tagsinput" class="form-control" id="keywords"
                                name="keywords" placeholder="Add tags" value="{{ $dataArray['item']->keyword }}"
                                autocomplete="on" required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Premium Item</h6>
                        <div class="col-sm-20">
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

                    <div class="form-group">
                        <h6>Status</h6>
                        <div class="col-sm-20">
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

                    <div>
                        <input class="btn btn-primary" type="submit" id="submitBtn" name="submit">
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
<script>
    $(document).ready(function() {
        $('#submitBtn').click(function(e) {
            e.preventDefault();

            var form = $('#editVideoForm')[0];
            var formData = new FormData(form);

            $.ajax({
                url: "{{ route('v_item.update', [$dataArray['item']->id]) }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function(response) {
                    if (response.status === false && response.error) {
                        alert(response.error); // Access error shown here
                    } else {
                        window.location.href = "{{ route('show_v_item') }}";
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 419) {
                        alert(
                            "Page expired or CSRF token mismatch (419 error). Please refresh and try again.");
                    } else if (xhr.status === 500) {
                        alert("Server error (500). Check backend.");
                    } else {
                        alert("Unexpected error occurred.");
                        console.log(xhr.responseText);
                    }
                }
            });
        });
    });

    window.addEventListener("load", function() {
        var tagsInputContainer = document.querySelector('.bootstrap-tagsinput');
        var tagsInput = tagsInputContainer.querySelector('input[type="text"]');

        if (tagsInput) {
            tagsInput.setAttribute('list', 'related_tag_list');
            tagsInput.setAttribute('autocomplete', 'on');
            tagsInput.style.width = '100%';
            tagsInput.style.height = '45px';
            tagsInput.style.border = '1px solid #000000';
            tagsInput.style.borderRadius = '5px';
            tagsInput.style.marginTop = '5px';
        }

    });

    $(document).ready(function() {
        var textCount = $("#dynamic_text_field").children().length + 1;

        $('#encrypted').change(function() {
            if ($(this).val() == '1') {
                $('#encryption_key').attr('required', '');
                var x = document.getElementById("encryption_field");
                x.style.display = "block";

            } else {
                $('#encryption_key').removeAttr('required');
                var x = document.getElementById("encryption_field");
                x.style.display = "none";
            }
        });


        $(document).on('click', '#add_text_id', function() {
            dynamic_text_field();
        });

        $(document).on('click', '#remove_text_id', function() {
            $(this).closest(".row").remove();
        });

        function dynamic_text_field() {
            var lastInputValue = $("input[name='key[]']:last").val();
            if (typeof lastInputValue !== 'undefined') {
                var parts = lastInputValue.split('_');
                var numericValue = parseInt(parts[parts.length - 1]);
                textCount = numericValue + 1;
            } else {
                textCount++;
            }

            html =
                '<div class="row"><hr size="8" width="100%" color="black"><div class="col-md-2 col-sm-12"><div class="form-group"><h6>Key</h6><input type="text" class="form-control" placeholder="key" id="key" name="key[]" value="editable_text_' +
                textCount +
                '"  required></div></div><div class="col-md-2 col-sm-12"><div class="form-group"><h6>Title</h6><input type="text" class="form-control" placeholder="Title" id="title" name="title[]" required></div></div><div class="col-md-2 col-sm-12"><div class="form-group"><h6>Font Family</h6><input type="text" class="form-control" placeholder="Font Family" id="font_family" name="font_family[]" required></div></div><div class="col-md-4 col-sm-12"><div class="form-group"> <h6>Value</h6><textarea style="height: 80px" class="form-control" id="editable_text_id" name="editable_text_id[]" required></textarea></div></div><div class="col-md-2 pd-19"><div class="form-group"><button type="button" name="remove_text_id" id="remove_text_id" class="btn btn-danger form-control-file">Remove</button></div></div></div>';
            $('#dynamic_text_field').append(html);
        }


        $(document).on('click', '#add_img_id', function() {
            dynamic_img_field();
        });

        $(document).on('click', '#remove_img_id', function() {
            $(this).closest(".row").remove();
        });

        function dynamic_img_field() {
            html =
                '<div class="row"><hr size="8" width="100%" color="black"><div class="col-md-4 col-sm-12"><div class="form-group"><h6>Id</h6><input type="text" class="form-control" placeholder="key" id="key" name="img_key[]" required></div></div><div class="col-md-4 col-sm-12"><div class="form-group"><h6>Is Shape</h6><select class="selectpicker form-control" data-style="btn-outline-primary" name="img_shape[]" required><option value="0">False</option><option value="1">True</option></select></div></div><div class="col-md-4 pd-19"><div class="form-group"><h6 style="opacity: 0;">.</h6><button type="button" name="remove_img_id" id="remove_img_id" class="btn btn-danger form-control-file">Remove</button></div></div></div>';
            $('#dynamic_img_field').append(html);
        }
    });
</script>
</body>

</html>
