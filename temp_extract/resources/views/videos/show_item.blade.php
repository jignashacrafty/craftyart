 @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
 <div class="main-container">
     <div class="">
         <div class="min-height-200px">
             <div class="card-box">
                 <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                     <div class="row align-items-center justify-content-between px-3 py-2 m-1">
                         <div class="col-auto">
                             <button class="btn btn-primary" onclick="appSelection()">Add New Item</button>
                         </div>

                         <div class="col-auto">
                             <form method="GET" action="{{ route('show_v_item') }}" id="filter-form">
                                 <div class="row align-items-end g-2">
                                     <div class="col-auto">
                                         <input type="text" name="query" class="form-control"
                                             placeholder="Search..." value="{{ request('query') }}">
                                     </div>

                                     <div class="col-auto">
                                         <select name="per_page" class="form-control">
                                             <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>
                                                 10</option>
                                             <option value="20" {{ request('per_page') == '20' ? 'selected' : '' }}>
                                                 20</option>
                                             <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>
                                                 50</option>
                                             <option value="100"
                                                 {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                                             <option value="500"
                                                 {{ request('per_page') == '500' ? 'selected' : '' }}>500</option>
                                             <option value="1000"
                                                 {{ request('per_page') == '1000' ? 'selected' : '' }}>1000</option>
                                         </select>
                                     </div>

                                     <div class="col-auto">
                                         <button type="submit" class="btn btn-success">Apply</button>
                                     </div>
                                 </div>
                             </form>
                         </div>
                     </div>

                     <form id="create_item_action" action="create_v_item" method="POST" enctype="multipart/form-data"
                         style="display: none;">
                         <input type="text" id="passingAppId" name="passingAppId">
                         @csrf
                     </form>

                     <div class="scroll-wrapper table-responsive tableFixHead"
                         style="max-height: calc(110vh - 220px) !important">
                         <table id="temp_table" style="table-layout: fixed; width: 100%;"
                             class="table table-striped table-bordered mb-0">

                             <thead>
                                 <tr>
                                     <th>Item Id</th>
                                     <th>User</th>
                                     <th>Template Id</th>
                                     <th>Category Name</th>
                                     <th>Video Name</th>
                                     <th class="datatable-nosort">Video Thumb</th>
                                     @if ($roleManager::isAdmin(Auth::user()->user_type))
                                         <th>Views</th>
                                         <th>Purchases</th>
                                     @endif
                                     <th>Is Premium</th>
                                     <th>Status</th>
                                     <th class="datatable-nosort">Action</th>
                                 </tr>
                             </thead>
                             <tbody id="item_table">
                                 @foreach ($itemArray['item'] as $item)
                                     <tr>
                                         <td class="table-plus">{{ $item->id }} ({{ $item->string_id }})</td>

                                         <td>{{ $roleManager::getEmployeeName($item->emp_id) }}</td>

                                         <td class="table-plus">{{ $item->relation_id }}</td>

                                         <td>{{ $helperController::getVCatName($item->category_id) }}</td>

                                         <td>{{ $item->video_name }}</td>

                                         <td><img src="{{ config('filesystems.storage_url') }}{{ $item->video_thumb }}"
                                                 width="100" /></td>

                                         @if ($roleManager::isAdmin(Auth::user()->user_type))
                                             <td>{{ $item->views }}</td>
                                             <td>{{ $helperController::getVPurchaseTemplateCount($item->string_id) }}
                                             </td>
                                         @endif

                                         @if ($item->is_premium == '1')
                                             <td>TRUE</td>
                                         @else
                                             <td>FALSE</td>
                                         @endif

                                         @if ($item->status == '1')
                                             <td>LIVE</td>
                                         @else
                                             <td>NOT LIVE</td>
                                         @endif
                                         <td>
                                             <div class="d-flex">

                                                 <a class="dropdown-item" href="edit_v_item/{{ $item->id }}"><i
                                                         class="dw dw-edit2"></i> Edit</a>
                                                 @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                     <Button class="dropdown-item"
                                                         onclick="set_delete_id('{{ $item->id }}')"
                                                         data-backdrop="static" data-toggle="modal"
                                                         data-target="#delete_model"><i
                                                             class="dw dw-delete-3"></i>Delete</Button>
                                                 @endif
                                                 <!-- <a class="dropdown-item" href="delete_v_item/{{ $item->id }}"><i class="dw dw-delete-3"></i> Delete</a> -->
                                                 <!--<a class="dropdown-item" data-toggle="modal" data-target="#Medium-modal" href="#"><i class="dw dw-delete-3"></i> Delete</a>-->
                                             </div>

                                         </td>

                                     </tr>
                                 @endforeach
                             </tbody>
                         </table>
                     </div>
                 </div>
                 <hr class="my-1">

                 @include('partials.pagination', ['items' => $itemArray['item']])

             </div>
         </div>
     </div>
 </div>
 </div>

 <div class="modal fade" id="delete_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
     aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
             <input type="text" id="delete_id" name="delete_id" style="display: none;">
             <div class="modal-header">
                 <h4 class="modal-title" id="myLargeModalLabel">Delete</h4>
                 <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
             </div>
             <div class="modal-body">
                 <p> Are you sure you want to delete? </p>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Cancel</button>
                 <button type="button" class="btn btn-primary" onclick="delete_click()">Yes, Delete</button>
             </div>
         </div>
     </div>
 </div>

 @include('layouts.masterscript')
 <script>
     function set_delete_id($id) {
         $("#delete_id").val($id);
     }

     function delete_click() {
         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         id = $("#delete_id").val();
         var url = "{{ route('v_item.delete', ':id') }}";
         url = url.replace(":id", id);
         $.ajax({
             url: url,
             type: 'POST',
             beforeSend: function() {
                 $('#delete_model').modal('toggle');
             },
             success: function(data) {
                 if (data.error) {
                     window.alert('error==>' + data.error);
                 } else {
                     location.reload();
                 }
             },
             error: function(error) {
                 window.alert(error.responseText);
             },
             cache: false,
             contentType: false,
             processData: false
         })
     }

     function appSelection() {
         $('#create_item_action').submit();
     }

     const sortTable = (event, column, sortType) => {
         event.preventDefault();
         let url = new URL(window.location.href);
         url.searchParams.set('sort_by', column);
         url.searchParams.set('sort_order', sortType);
         window.location.href = url.toString();
     }
 </script>
 </body>

 </html>
