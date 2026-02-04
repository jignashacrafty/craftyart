   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
   @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
   @inject('helperController', 'App\Http\Controllers\HelperController')
   @include('layouts.masterhead')
   <div class="main-container designer-access-container">
       <div class="">
           <div class="min-height-200px">
               <div class="card-box">
                   <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                       <div class="row justify-content-between">
                           <!-- Left: Add Button -->
                           <div class="col-md-3">
                               @if ($roleManager::onlyDesignerAccess(Auth::user()->user_type))
                                   <button type="button" class="btn btn-primary m-1 item-form-input" id="addNewvideoItemBtn">
                                       + Add video Item
                                   </button>
                               @endif
                           </div>

                           <!-- Right: Filter Form -->
                           <div class="col-md-7">
                               @include('partials.filter_form', [
                                   'action' => route('video_item.index'),
                               ])
                           </div>
                       </div>

                       <div class="scroll-wrapper table-responsive tableFixHead"
                           style="max-height: calc(110vh - 220px) !important">
                           <table id="temp_table" style="table-layout: fixed; width: 100%;"
                               class="table table-striped table-bordered mb-0">
                               <thead>
                                   <tr>
                                       <th>Id</th>
                                       <th>User</th>
                                       <th>Name</th>
                                       <th>Parent Category</th>
                                       <th>Thumb</th>
                                       <th style="width: 150px;">Video</th>
                                       <th>Is Premium</th>
                                       <th>Status</th>
                                       <th style="width:130px;">Action</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   @foreach ($videoItems as $videoItem)
                                       <tr>
                                           <td class="table-plus">{{ $videoItem->id }}</td>
                                           <td class="table-plus">
                                               {{ $roleManager::getUploaderName($videoItem->emp_id) }}</td>
                                           <td>{{ $videoItem->name ? $videoItem->name : '' }}</td>
                                           <td>{{ $videoItem->VideoCategory->name ?? '-' }}</td>
                                           <td><img src="{{ $contentManager::getStorageLink($videoItem->thumb) }}"
                                                   style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                           </td>
                                           <td><video controls
                                                   style="max-width: 150px; max-height: 150px; width: auto; height: auto"
                                                   src="{{ $contentManager::getStorageLink($videoItem->file) }}" />
                                           </td>

                                           @if ($videoItem->is_premium == '1')
                                               <td>
                                                   <label id="premium_label_{{ $videoItem->id }}"
                                                       style="display: none;">TRUE</label>
                                                   <label class="switch-new">
                                                       <input type="checkbox" checked class="hidden-checkbox"
                                                           onclick="premium_click('{{ $videoItem->id }}')">
                                                       <span class="slider round"></span>
                                                   </label>
                                               </td>
                                           @else
                                               <td>
                                                   <label id="premium_label_{{ $videoItem->id }}"
                                                       style="display: none;">FALSE</label>
                                                   <label class="switch-new">
                                                       <input type="checkbox" class="hidden-checkbox"
                                                           onclick="premium_click('{{ $videoItem->id }}')">
                                                       <span class="slider round"></span>
                                                   </label>
                                               </td>
                                           @endif

                                           @if ($videoItem->status == '1')
                                               <td>LIVE</td>
                                           @else
                                               <td>NOT LIVE</td>
                                           @endif

                                           <td>
                                               <div class="d-flex">
                                                   <button class="dropdown-item edit-video-item-btn"
                                                       data-id="{{ $videoItem->id }}">
                                                       <i class="dw dw-edit2"></i> Edit
                                                   </button>
                                                   @if ($roleManager::isAdminOrDesignerManager(Auth::user()->user_type))
                                                       <a class="dropdown-item" href="#"
                                                           onclick="deletevideoItem({{ $videoItem['id'] }})">
                                                           <i class="dw dw-delete-3"></i> Delete
                                                       </a>
                                                   @endif
                                               </div>
                                           </td>
                                       </tr>
                                   @endforeach
                               </tbody>
                           </table>
                       </div>
                   </div>
                   <hr class="my-1">

                   @include('partials.pagination', ['items' => $videoItems])
               </div>
           </div>
       </div>
   </div>

   <div class="modal fade designer-access-container" id="add_video_item_model" tabindex="-1">
       <div class="modal-dialog modal-lg modal-dialog-centered">
           <div class="modal-content">
               <div class="modal-body">

                   <form id="video_item_form" enctype="multipart/form-data">
                       @csrf
                       <input type="hidden" name="id" id="video_item_id">
                       <div class="form-group">
                           <h6>video item Name</h6>
                           <input class="form-control" type="textname" id="name" name="name" required>
                       </div>

                       <div class="form-group category-dropbox-wrap">
                           <h6>video Category</h6>
                           <select id="video_category_id" class="form-control" name="video_category_id" required>
                               <option value="" disabled selected>== Select Category ==</option>
                               @foreach ($VideoCategory as $cat)
                                   <option value="{{ $cat->id }}">
                                       {{ $cat->name }}
                                   </option>
                               @endforeach
                           </select>
                       </div>

                       <div class="form-group">
                           <h6>video Items Thumb</h6>
                           <input type="file" class="form-control-file form-control dynamic-file height-auto"
                               data-accept=".jpg, .jpeg, .webp, .svg" data-imgstore-id="thumb" data-nameset="true"
                               id="thumb_files">
                       </div>

                       <div class="form-group">
                           <h6>video Items File</h6>
                           <span class="tooltip-container">
                               <svg class="svg-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                   fill="currentColor">
                                   <path d="..." />
                               </svg>
                               <span class="tooltip-text">Allow SVG Formate File For Upload Svg</span>
                           </span>
                           <input type="file" accept="video/*" class="form-control-file form-control height-auto"
                               id="item_file" name="file">
                       </div>

                       <div class="form-group">
                           <label>Is Premium</label>
                           <select name="is_premium" id="is_premium" class="form-control">
                               <option value="0">FALSE</option>
                               <option value="1">TRUE</option>
                           </select>
                       </div>

                       <div class="form-group">
                           <label>Status</label>
                           <select name="status" id="status" class="form-control">
                               <option value="1">LIVE</option>
                               <option value="0">NOT LIVE</option>
                           </select>
                       </div>

                       <button type="submit" class="btn btn-primary">Submit</button>
                   </form>
               </div>
           </div>
       </div>
   </div>

   <script>
       const STORAGE_URL = "{{ env('STORAGE_URL') }}";
       // *Debug
       const storageUrl = "{{ config('filesystems.storage_url') }}";
   </script>
   @include('layouts.masterscript')
   <script>
       function toggleCheckbox(button, parameter) {
           var checkbox = button.querySelector('input[type="checkbox"]');
           checkbox.checked = !checkbox.checked;
           premium_click(parameter);
       }
       $(document).ready(function() {
           $('#addNewvideoItemBtn').on('click', function() {
               resetForm();
               $('#add_video_item_model').modal('show');
           });

           $(document).on('click', '.edit-video-item-btn', function() {
               const id = $(this).data('id');

               $.get(`{{ url('video_item') }}/${id}/edit`, function(res) {
                   if (res.status) {
                       const data = res.data;
                       $('#add_video_item_model').modal('show');
                       $('#name').val(data.name);
                       $('#video_item_id').val(data.id);
                       $('#video_category_id').val(data.video_category_id);
                       $('#is_premium').val(data.is_premium);
                       $('#status').val(data.status);
                       $('#selectedCategoryText').text(data.video_category?.name || '== none ==');

                       if (data.thumb) {
                           const imageUrl = getStorageLink(data.thumb);
                           $('#thumb_files').attr('data-value', imageUrl);
                           dynamicFileCmp();
                       }
                   }
               });
           });

           $(document).on('click', '.category', function() {
               const categoryId = $(this).data('id');
               const categoryName = $(this).data('catname');
               $('#video_category_id').val(categoryId);
               $('#selectedCategoryText').text(categoryName);
               $('.category').removeClass('selected');
               $(this).addClass('selected');
           });

           $('#video_item_form').on('submit', function(e) {
               e.preventDefault();

               const formData = new FormData(this);
               const isEdit = formData.get('id');
               const file = formData.get('file');

               if (!isEdit && (!file || file.size === 0)) {
                   alert('Please select an video file.');
                   return;
               }

               $.ajaxSetup({
                   headers: {
                       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   }
               });

               $.ajax({
                   url: `{{ route('video_item.store') }}`,
                   type: 'POST',
                   data: formData,
                   contentType: false,
                   processData: false,
                   success: function(data) {
                       if (data.status) {
                           location.reload();
                       } else {
                           alert(data.error || 'Error occurred!');
                       }
                   },
                   error: function(xhr) {
                       alert(xhr.responseText);
                   }
               });
           });


           function resetForm() {
               $('#video_item_form')[0].reset();
               $('#video_item_id').val('');
               $('#video_category_id').val(0);
               resetDynamicFileValue("thumb_files")
               $('#selectedCategoryText').text('== none ==');
           }
       });

       function deletevideoItem(id) {
           event.preventDefault();
           if (confirm('Are you sure you want to delete this video item?')) {
               $.ajax({
                   url: "{{ route('video_item.destroy', ':id') }}".replace(':id', id),
                   type: 'DELETE',
                   headers: {
                       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   },
                   success: function(response) {
                       if (response.success) {
                           window.location.reload();
                       } else {
                           alert(response.error);
                       }
                   },
                   error: function(xhr) {

                   }
               });
           }
       }

       function premium_click($id) {
           $.ajaxSetup({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
               }
           });
           var status = $id;
           var url = "{{ route('videoItem.premium', ': status ') }}";
           url = url.replace(":status", status);
           var formData = new FormData();
           formData.append('id', $id);
           $.ajax({
               url: url,
               type: 'POST',
               data: formData,
               success: function(data) {
                   if (data.error) {
                       window.alert(data.error);
                   } else {
                       var x = document.getElementById("premium_label_" + $id);
                       if (x.innerHTML === "TRUE") {
                           x.innerHTML = "FALSE";
                       } else {
                           x.innerHTML = "TRUE";
                       }
                   }

               },
               error: function(error) {
                   var main_loading_screen = document.getElementById("main_loading_screen");
                   main_loading_screen.style.display = "none";
                   window.alert(error.responseText);
               },
               cache: false,
               contentType: false,
               processData: false
           })
       }
   </script>
   </body>

   </html>
