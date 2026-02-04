   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
   @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
   @inject('helperController', 'App\Http\Controllers\HelperController')
   @include('layouts.masterhead')
   <div class="main-container">
       <div class="">
           <div class="min-height-200px">
               <div class="card-box">
                   <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                       <div class="row justify-content-between">
                           <div class="col-md-3 m-1">
                               <a class="btn btn-primary" href="create_v_cat" role="button"> Add New Category </a>
                           </div>

                           <div class="col-md-7">
                               @include('partials.filter_form ', [
                                   'action' => route('show_v_cat'),
                               ])
                           </div>
                       </div>

                       <div class="scroll-wrapper table-responsive tableFixHead"
                           style="max-height: calc(110vh - 220px) !important">
                           <table id="temp_table" style="table-layout: fixed; width: 100%;"
                               class="table table-striped table-bordered mb-0">


                               <thead>
                                   <tr>
                                       <th class="datatable-nosort">Category Id</th>
                                       <th class="datatable-nosort">Category Name</th>
                                       <th class="datatable-nosort">Category Thumb</th>
                                       <th class="datatable-nosort">Sequence Number</th>
                                       <th class="datatable-nosort">Status</th>
                                       <th class="datatable-nosort">User</th>
                                       <th class="datatable-nosort">Action</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   @foreach ($catArray as $cat)
                                       <tr>
                                           <td class="table-plus">{{ $cat->id }}</td>

                                           <td>{{ $cat->category_name }}</td>

                                           <td><img src="{{ config('filesystems.storage_url') }}{{ $cat->category_thumb }}"
                                                   width="100" /></td>

                                           <td>{{ $cat->sequence_number }}</td>

                                           @if ($cat->status == '1')
                                               <td>LIVE</td>
                                           @else
                                               <td>NOT LIVE</td>
                                           @endif
                                           <td>{{ $roleManager::getUploaderName($cat->emp_id) }}</td>
                                           <td>
                                               <div class="d-flex">
                                                   <a class="dropdown-item" href="edit_v_cat/{{ $cat->id }}"><i
                                                           class="dw dw-edit2"></i> Edit</a>
                                                   @if ($roleManager::isAdminOrDesignerManager(Auth::user()->user_type))
                                                       <a class="dropdown-item"
                                                           href="delete_v_cat/{{ $cat->id }}"><i
                                                               class="dw dw-delete-3"></i> Delete</a>
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

                   @include('partials.pagination', ['items' => $catArray])

               </div>
           </div>
       </div>
   </div>
   </div>
   @include('layouts.masterscript')
   <script>
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
