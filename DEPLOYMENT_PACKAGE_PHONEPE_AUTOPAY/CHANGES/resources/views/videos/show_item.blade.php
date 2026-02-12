 @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
 
 <style>
     .video-items-container {
         background: #f8f9fa;
         min-height: 100vh;
         padding: 20px;
     }
     
     .items-card {
         background: white;
         border-radius: 8px;
         box-shadow: 0 2px 4px rgba(0,0,0,0.1);
         overflow: hidden;
     }
     
     .items-header {
         padding: 20px;
         border-bottom: 1px solid #e9ecef;
         background: white;
     }
     
     .items-table-wrapper {
         overflow-x: auto;
     }
     
     .items-table {
         width: 100%;
         margin: 0;
         border-collapse: separate;
         border-spacing: 0;
     }
     
     .items-table thead th {
         background: #f8f9fa;
         color: #495057;
         font-weight: 600;
         font-size: 13px;
         text-transform: uppercase;
         padding: 15px 12px;
         border-bottom: 2px solid #dee2e6;
         white-space: nowrap;
         position: sticky;
         top: 0;
         z-index: 10;
     }
     
     .items-table tbody td {
         padding: 12px;
         border-bottom: 1px solid #e9ecef;
         vertical-align: middle;
         font-size: 14px;
         color: #495057;
     }
     
     .items-table tbody tr:hover {
         background-color: #f8f9fa;
     }
     
     .video-thumb-cell {
         width: 120px;
     }
     
     .video-thumb-img {
         width: 100px;
         height: 60px;
         object-fit: cover;
         border-radius: 4px;
         border: 1px solid #dee2e6;
     }
     
     .status-badge {
         display: inline-block;
         padding: 4px 12px;
         border-radius: 12px;
         font-size: 12px;
         font-weight: 500;
     }
     
     .status-live {
         background: #d4edda;
         color: #155724;
     }
     
     .status-not-live {
         background: #f8d7da;
         color: #721c24;
     }
     
     .premium-badge {
         background: #fff3cd;
         color: #856404;
     }
     
     .free-badge {
         background: #d1ecf1;
         color: #0c5460;
     }
     
     .action-dropdown .dropdown-toggle {
         background: transparent;
         border: none;
         padding: 5px 10px;
         color: #6c757d;
         cursor: pointer;
     }
     
     .action-dropdown .dropdown-toggle::after {
         display: none !important;
     }
     
     .action-dropdown .dropdown-toggle:hover {
         color: #495057;
         background: #f8f9fa;
         border-radius: 4px;
     }
     
     .filter-section {
         display: flex;
         gap: 10px;
         align-items: center;
         flex-wrap: wrap;
     }
     
     .filter-input {
         min-width: 200px;
     }
     
     .btn-add-new {
         background: #007bff;
         color: white;
         border: none;
         padding: 10px 20px;
         border-radius: 6px;
         font-weight: 500;
         transition: all 0.3s;
     }
     
     .btn-add-new:hover {
         background: #0056b3;
         transform: translateY(-1px);
         box-shadow: 0 4px 8px rgba(0,123,255,0.3);
     }
     
     .pagination-wrapper {
         padding: 20px;
         background: white;
         border-top: 1px solid #e9ecef;
     }
     
     .id-cell {
         font-family: 'Courier New', monospace;
         font-size: 12px;
         color: #6c757d;
     }
     
     .string-id {
         display: block;
         color: #007bff;
         font-size: 11px;
         margin-top: 2px;
     }
 </style>
 
 <div class="main-container">
     <div class="video-items-container">
         <div class="items-card">
             <div class="items-header">
                 <div class="row align-items-center mb-3">
                     <div class="col-md-6">
                         <h4 class="mb-0" style="font-weight: 600; color: #212529;">Video Templates</h4>
                         <p class="text-muted mb-0" style="font-size: 14px;">Manage your video template items</p>
                     </div>
                     <div class="col-md-6 text-right">
                         <button class="btn btn-add-new" onclick="appSelection()">
                             <i class="fa fa-plus mr-2"></i>Add New Item
                         </button>
                     </div>
                 </div>
                 
                 <div class="row">
                     <div class="col-12">
                         <form method="GET" action="{{ route('show_v_item') }}" id="filter-form">
                             <div class="filter-section">
                                 <input type="text" name="query" class="form-control filter-input"
                                     placeholder="Search by ID, name, or category..." value="{{ request('query') }}" 
                                     style="flex: 1; max-width: 400px;">
                                 
                                 <select name="per_page" class="form-control" style="width: 130px;">
                                     <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 per page</option>
                                     <option value="20" {{ request('per_page') == '20' ? 'selected' : '' }}>20 per page</option>
                                     <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 per page</option>
                                     <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 per page</option>
                                 </select>
                                 
                                 <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                                     <i class="fa fa-search mr-1"></i>Search
                                 </button>
                                 
                                 @if(request('query') || request('per_page'))
                                     <a href="{{ route('show_v_item') }}" class="btn btn-secondary" style="white-space: nowrap;">
                                         <i class="fa fa-times mr-1"></i>Clear
                                     </a>
                                 @endif
                             </div>
                         </form>
                     </div>
                 </div>
             </div>

             <form id="create_item_action" action="create_v_item" method="POST" enctype="multipart/form-data"
                 style="display: none;">
                 <input type="text" id="passingAppId" name="passingAppId">
                 @csrf
             </form>

             <div class="items-table-wrapper">
                 <table class="items-table">
                     <thead>
                         <tr>
                             <th style="width: 100px;">ID</th>
                             <th style="width: 120px;">User</th>
                             <th style="width: 100px;">Template ID</th>
                             <th style="width: 200px;">Category</th>
                             <th style="width: 250px;">Video Name</th>
                             <th class="video-thumb-cell">Thumbnail</th>
                             @if ($roleManager::isAdmin(Auth::user()->user_type))
                                 <th style="width: 80px;">Views</th>
                                 <th style="width: 100px;">Purchases</th>
                             @endif
                             <th style="width: 100px;">Premium</th>
                             <th style="width: 100px;">Status</th>
                             <th style="width: 80px; text-align: center;">Actions</th>
                         </tr>
                     </thead>
                     <tbody>
                         @forelse ($itemArray['item'] as $item)
                             <tr>
                                 <td class="id-cell">
                                     #{{ $item->id }}
                                     <span class="string-id">{{ $item->string_id }}</span>
                                 </td>
                                 <td>{{ $roleManager::getEmployeeName($item->emp_id) }}</td>
                                 <td class="id-cell">{{ $item->relation_id }}</td>
                                 <td>{{ $helperController::getVCatName($item->category_id) }}</td>
                                 <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                                     title="{{ $item->video_name }}">
                                     {{ $item->video_name }}
                                 </td>
                                 <td class="video-thumb-cell">
                                     <img src="{{ config('filesystems.storage_url') }}{{ $item->video_thumb }}"
                                         class="video-thumb-img" alt="Thumbnail" />
                                 </td>
                                 @if ($roleManager::isAdmin(Auth::user()->user_type))
                                     <td>{{ number_format($item->views) }}</td>
                                     <td>{{ number_format($helperController::getVPurchaseTemplateCount($item->string_id)) }}</td>
                                 @endif
                                 <td>
                                     @if ($item->is_premium == '1')
                                         <span class="status-badge premium-badge">Premium</span>
                                     @else
                                         <span class="status-badge free-badge">Free</span>
                                     @endif
                                 </td>
                                 <td>
                                     @if ($item->status == '1')
                                         <span class="status-badge status-live">Live</span>
                                     @else
                                         <span class="status-badge status-not-live">Not Live</span>
                                     @endif
                                 </td>
                                 <td style="text-align: center;">
                                     <div class="dropdown action-dropdown">
                                         <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                             <i class="dw dw-more"></i>
                                         </a>
                                         <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                             <a class="dropdown-item" href="edit_v_item/{{ $item->id }}">
                                                 <i class="dw dw-edit2"></i> Edit
                                             </a>
                                             <a class="dropdown-item" href="edit_seo_v_item/{{ $item->id }}">
                                                 <i class="dw dw-file"></i> Edit SEO Data
                                             </a>
                                             @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                 <a class="dropdown-item" href="#"
                                                     onclick="set_delete_id('{{ $item->id }}')"
                                                     data-backdrop="static" data-toggle="modal"
                                                     data-target="#delete_model">
                                                     <i class="dw dw-delete-3"></i> Delete
                                                 </a>
                                             @endif
                                         </div>
                                     </div>
                                 </td>
                             </tr>
                         @empty
                             <tr>
                                 <td colspan="11" style="text-align: center; padding: 40px; color: #6c757d;">
                                     <i class="fa fa-inbox" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 10px;"></i>
                                     <p class="mb-0">No video items found</p>
                                 </td>
                             </tr>
                         @endforelse
                     </tbody>
                 </table>
             </div>

             <div class="pagination-wrapper">
                 @include('partials.pagination', ['items' => $itemArray['item']])
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
                 <h4 class="modal-title" id="myLargeModalLabel">Delete Video Item</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
             </div>
             <div class="modal-body">
                 <p>Are you sure you want to delete this video item? This action cannot be undone.</p>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                 <button type="button" class="btn btn-danger" onclick="delete_click()">Delete</button>
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
                 $('#delete_model').modal('hide');
             },
             success: function(data) {
                 if (data.error) {
                     alert('Error: ' + data.error);
                 } else {
                     location.reload();
                 }
             },
             error: function(error) {
                 alert('Error: ' + error.responseText);
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
