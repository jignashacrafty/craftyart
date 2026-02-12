@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')

<style>
    /* Reviews Page Styles - Updated v2.2 - Full Width */
    
    .main-container .pd-ltr-20 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    
    .reviews-header-card {
        background: linear-gradient(90deg, #1abc9c 0%, #667eea 100%);
        border-radius: 0;
        padding: 25px 30px;
        margin-bottom: 0;
        box-shadow: none;
    }
    
    .reviews-header-card h2 {
        color: white;
        font-size: 26px;
        font-weight: 600;
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .reviews-breadcrumb {
        color: rgba(255, 255, 255, 0.9);
        font-size: 14px;
        margin: 0;
    }
    
    .reviews-breadcrumb a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
    }
    
    .reviews-breadcrumb a:hover {
        color: white;
    }
    
    .reviews-main-card {
        background: white;
        border-radius: 0;
        box-shadow: none;
        overflow: hidden;
    }
    
    .reviews-toolbar-section {
        padding: 20px 25px;
        background: white;
        border-bottom: 2px solid #f0f2f5;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .toolbar-left {
        flex: 1;
        display: flex;
        justify-content: flex-start;
    }
    
    .toolbar-right {
        display: flex;
        justify-content: flex-end;
    }
    
    /* Filter Form Styling */
    .filter-access select.form-control,
    .filter-access input.form-control {
        border: 2px solid #e0e6ed;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13px;
        color: #2c3e50;
        background: white;
        transition: all 0.3s ease;
        height: 42px;
    }
    
    .filter-access select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%231abc9c' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 35px;
    }
    
    .filter-access select.form-control option {
        padding: 10px;
        color: #2c3e50;
    }
    
    .filter-access select.form-control:focus,
    .filter-access input.form-control:focus {
        border-color: #1abc9c;
        box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.15);
        outline: none;
    }
    
    .filter-access .btn-success {
        background: linear-gradient(90deg, #1abc9c 0%, #16a085 100%);
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        font-size: 13px;
        height: 42px;
        transition: all 0.3s ease;
        color: white;
    }
    
    .filter-access .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 188, 156, 0.3);
    }
    
    .btn-add-new-review {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white !important;
        padding: 11px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        text-decoration: none;
    }
    
    .btn-add-new-review:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(102, 126, 234, 0.4);
        color: white !important;
    }
    
    .reviews-table-container {
        overflow-x: auto;
    }
    
    .reviews-data-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }
    
    .reviews-data-table thead {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }
    
    .reviews-data-table thead th {
        color: white !important;
        font-weight: 600 !important;
        font-size: 11px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        padding: 16px 12px !important;
        border: none !important;
        white-space: nowrap !important;
        text-align: left !important;
    }
    
    .reviews-data-table thead th:first-child {
        text-align: center !important;
    }
    
    .reviews-data-table thead th:nth-child(6),
    .reviews-data-table thead th:nth-child(7),
    .reviews-data-table thead th:nth-child(8),
    .reviews-data-table thead th:nth-child(9) {
        text-align: center !important;
    }
    
    .reviews-data-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f0f2f5;
    }
    
    .reviews-data-table tbody tr:hover {
        background: #f8f9ff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    
    .reviews-data-table tbody td {
        padding: 16px 12px !important;
        vertical-align: middle !important;
        font-size: 14px !important;
        color: #2c3e50 !important;
        text-align: left !important;
    }
    
    .reviews-data-table tbody td:first-child {
        text-align: center !important;
    }
    
    .reviews-data-table tbody td:nth-child(5),
    .reviews-data-table tbody td:nth-child(7),
    .reviews-data-table tbody td:nth-child(8),
    .reviews-data-table tbody td:nth-child(9) {
        text-align: center !important;
    }
    
    .badge-review-no {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 16px;
        font-weight: 600;
        font-size: 12px;
        display: inline-block;
    }
    
    .badge-service-id {
        background: #e3f2fd;
        color: #1976d2;
        padding: 5px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 11px;
        display: inline-block;
        border: 1px solid #bbdefb;
    }
    
    .user-info-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .user-avatar-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #667eea;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.25);
        display: block;
        margin: 0 auto;
    }
    
    .user-avatar-img[src=""],
    .user-avatar-img:not([src]) {
        display: none;
    }
    
    .user-name-text {
        font-weight: 600;
        color: #2c3e50;
        font-size: 14px;
    }
    
    .user-email-text {
        font-size: 13px;
        color: #7f8c8d;
    }
    
    .badge-service-name {
        background: linear-gradient(90deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .rating-stars-wrapper {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .rating-stars-wrapper .fa-star.checked {
        color: #ffc107;
        font-size: 14px;
    }
    
    .rating-stars-wrapper .fa-star {
        color: #e0e0e0;
        font-size: 14px;
    }
    
    .review-text-content {
        max-width: 250px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        line-clamp: 2;
        line-height: 1.4;
        color: #555;
        font-size: 13px;
        cursor: pointer;
        position: relative;
        padding-right: 25px;
    }
    
    .review-text-content:hover {
        color: #667eea;
    }
    
    .review-comment-icon {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        color: #667eea;
        font-size: 14px;
        cursor: pointer;
    }
    
    .review-comment-icon:hover {
        color: #764ba2;
    }
    
    .review-created-date {
        color: #7f8c8d;
        font-size: 12px;
        font-weight: 500;
    }
    
    .action-buttons-group {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    
    .btn-icon-action {
        width: 34px;
        height: 34px;
        border-radius: 6px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        cursor: pointer;
        font-size: 14px;
    }
    
    .btn-icon-action.btn-edit {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .btn-icon-action.btn-edit:hover:not(:disabled) {
        background: #1976d2;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(25, 118, 210, 0.3);
    }
    
    .btn-icon-action.btn-remove {
        background: #ffebee;
        color: #d32f2f;
    }
    
    .btn-icon-action.btn-remove:hover:not(:disabled) {
        background: #d32f2f;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(211, 47, 47, 0.3);
    }
    
    .btn-icon-action:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    
    .switch-new {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
    }
    
    .switch-new input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e0e6ed;
        transition: .3s;
        border-radius: 24px;
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    
    .switch-new input:checked + .slider {
        background: linear-gradient(90deg, #1abc9c 0%, #16a085 100%) !important;
    }
    
    .switch-new input:checked + .slider:before {
        transform: translateX(24px);
    }
    
    .reviews-pagination-section {
        padding: 18px 25px;
        background: white;
        border-top: 2px solid #f0f2f5;
    }
    
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }
    
    .modal-header {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 22px 28px;
        border: none;
    }
    
    .modal-header .modal-title {
        font-size: 20px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .modal-header .close {
        color: white;
        opacity: 1;
        text-shadow: none;
        font-size: 26px;
        font-weight: 300;
    }
    
    .modal-header .close:hover {
        color: white;
        opacity: 0.8;
    }
    
    .modal-body {
        padding: 28px;
    }
    
    .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 13px;
    }
    
    .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }
    
    .modal-footer {
        padding: 18px 28px;
        border-top: 2px solid #f0f2f5;
        background: #f8f9fa;
        border-radius: 0 0 12px 12px;
    }
    
    .modal-footer .btn-primary {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 10px 26px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .modal-footer .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(102, 126, 234, 0.4);
    }
    
    @media (max-width: 768px) {
        .reviews-toolbar-section {
            flex-direction: column;
            align-items: stretch;
        }
        
        .toolbar-left,
        .toolbar-right {
            width: 100%;
        }
        
        .toolbar-left {
            justify-content: flex-start;
        }
        
        .toolbar-right {
            justify-content: center;
            margin-top: 10px;
        }
        
        .reviews-data-table {
            font-size: 12px;
        }
        
        .reviews-data-table thead th,
        .reviews-data-table tbody td {
            padding: 10px 8px;
        }
    }
</style>

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="reviews-header-card">
                <h2>
                    <i class="icon-copy fa fa-star" aria-hidden="true"></i>
                    Reviews
                </h2>
                <div class="reviews-breadcrumb">
                    Dashboard &gt; Reviews
                </div>
            </div>

            <div class="reviews-main-card">
                    <div class="reviews-toolbar-section">
                        <div class="toolbar-left">
                            @include('partials.filter_form', [
                                'action' => route('reviews.index'),
                            ])
                        </div>
                        
                        <div class="toolbar-right">
                            @if (!$roleManager::isSeoManager(Auth::user()->user_type) || $roleManager::isDesignerManager(Auth::user()->user_type))
                                <a href="#" class="btn-add-new-review" data-toggle="modal" data-target="#add_preview" id="openAddModal">
                                    <i class="icon-copy fa fa-plus" aria-hidden="true"></i>
                                    Add Review
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="reviews-table-container">
                        <table class="reviews-data-table">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">Index</th>
                                    <th style="width: 110px;">User ID</th>
                                    <th style="width: 220px;">Name</th>
                                    <th style="width: 180px;">Email</th>
                                    <th style="width: 160px;">Profile Image</th>
                                    <th style="width: 280px;">Message</th>
                                    <th style="width: 110px;">Rating</th>
                                    <th style="width: 110px;">Is Approve</th>
                                    <th style="width: 140px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reviews as $index => $reviewRow)
                                    <tr>
                                        <td>
                                            <span class="badge-review-no">{{ $reviewRow->id }}</span>
                                        </td>
                                        <td>
                                            <span class="badge-service-id">{{ $reviewRow->user_id ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="user-name-text">
                                                @if($reviewRow->name)
                                                    {{ $reviewRow->name }}
                                                @elseif($reviewRow->user_id && $reviewRow->user)
                                                    {{ $reviewRow->user->name }}
                                                @else
                                                    Deleted User
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <span class="user-email-text">
                                                @if($reviewRow->email)
                                                    {{ $reviewRow->email }}
                                                @elseif($reviewRow->user_id && $reviewRow->user)
                                                    {{ $reviewRow->user->email }}
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            @php
                                                $photoUri = $reviewRow->photo_uri;
                                                if (!$photoUri && $reviewRow->user_id && $reviewRow->user) {
                                                    $photoUri = $reviewRow->user->photo_uri;
                                                }
                                                $displayName = $reviewRow->name ?? ($reviewRow->user->name ?? 'User');
                                            @endphp
                                            <img src="{{ $contentManager::getStorageLink($photoUri ?? '') }}" 
                                                 alt="User" 
                                                 class="user-avatar-img"
                                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($displayName) }}&background=667eea&color=fff&size=40'">
                                        </td>
                                        <td>
                                            <div class="review-text-content" onclick="showFullMessage('{{ addslashes($reviewRow->feedback) }}')">
                                                {{ $reviewRow->feedback }}
                                                @if(strlen($reviewRow->feedback) > 100)
                                                    <i class="icon-copy fa fa-comment review-comment-icon" aria-hidden="true"></i>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="rating-stars-wrapper">
                                                {!! \App\Http\Controllers\HelperController::generateStars($reviewRow->rate) !!}
                                            </div>
                                        </td>
                                        <td>
                                            <label class="switch-new" title="Toggle Approval">
                                                <input type="checkbox" 
                                                       {{ $reviewRow->is_approve == '1' ? 'checked' : '' }}
                                                       onclick="onReviewStatus('{{ $reviewRow->id }}')">
                                                <span class="slider"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="action-buttons-group">
                                                @php
                                                    $isSeoManager = $roleManager::isSeoManager(Auth::user()->user_type) || $roleManager::isDesignerManager(Auth::user()->user_type);
                                                    $isAdminOrSeoManager = $roleManager::isAdmin(Auth::user()->user_type) || $roleManager::isSeoManager(Auth::user()->user_type);
                                                @endphp
                                                
                                                <button class="btn-icon-action btn-edit edit-review-btn" 
                                                        data-id="{{ $reviewRow->id }}"
                                                        title="Edit Review"
                                                        @if ($reviewRow->user_id != null || $isSeoManager) disabled @endif>
                                                    <i class="icon-copy fa fa-pencil" aria-hidden="true"></i>
                                                </button>
                                                
                                                <button class="btn-icon-action btn-remove delete-review-btn" 
                                                        data-id="{{ $reviewRow->id }}"
                                                        title="Delete Review"
                                                        @if (!$isAdminOrSeoManager || $reviewRow->user_id != null) disabled @endif>
                                                    <i class="icon-copy fa fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="reviews-pagination-section">
                        @include('partials.pagination', ['items' => $reviews])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_preview" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="addForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="review_id">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="icon-copy fa fa-star" aria-hidden="true"></i>
                        Add / Edit Review
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" id="closeButton" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label for="name">
                                    <i class="icon-copy fa fa-user" aria-hidden="true"></i>
                                    Name
                                </label>
                                <input class="form-control" placeholder="Enter customer name" id="name" name="name" type="text" required>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label for="email">
                                    <i class="icon-copy fa fa-envelope" aria-hidden="true"></i>
                                    Email
                                </label>
                                <input class="form-control" placeholder="Enter email address" id="email" name="email" type="email" required>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                <label>
                                    <i class="icon-copy fa fa-image" aria-hidden="true"></i>
                                    Profile Picture
                                </label>
                                <input type="file" class="form-control-file form-control dynamic-file height-auto"
                                    data-imgstore-id="photo_uri" data-nameset="true" id="profile_pic_input"
                                    data-accept=".jpg, .jpeg, .webp, .svg, .png">
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                <label>
                                    <i class="icon-copy fa fa-star" aria-hidden="true"></i>
                                    Rating (1-5)
                                </label>
                                <input class="form-control" placeholder="Enter rating (1-5)" name="rate" id="rate"
                                    type="number" min="1" max="5" required>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                <label>
                                    <i class="icon-copy fa fa-comment" aria-hidden="true"></i>
                                    Feedback
                                </label>
                                <textarea class="form-control" name="feedback" id="feedback" rows="5" 
                                          placeholder="Enter customer feedback..." required></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div id="result" class="mr-auto"></div>
                    <button type="submit" class="btn btn-primary" id="btnSubmitForm">
                        <i class="icon-copy fa fa-check" aria-hidden="true"></i>
                        Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const STORAGE_URL = "{{ env('STORAGE_URL') }}";
    const storageUrl = "{{ config('filesystems.storage_url') }}";
</script>

@include('layouts.masterscript')

<script>
    let reviews = @json($reviews);
    
    $(document).ready(function() {
        $('#openAddModal').click(function() {
            resetValue();
        });

        $('#closeButton').click(function() {
            resetValue();
        });

        function resetValue() {
            $('#addForm')[0].reset();
            $('#profile_pic_input').removeAttr('data-value');
            $('#photo_uri').attr('src', '');
            $('#review_id').val('');
            $('#result').html('');
        }
        
        $('#addForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('reviews.store') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        $('#add_preview').modal('hide');
                        Swal.fire({
                            title: 'Success!',
                            text: 'Review saved successfully!',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: res.message,
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors || xhr.responseJSON || {};
                    let msg = errors.error || 'Something went wrong.';
                    Swal.fire({
                        title: 'Error!',
                        text: msg,
                        icon: 'error'
                    });
                }
            });
        });
    });

    $(document).on('click', '.edit-review-btn', function() {
        const data = $(this).data();
        const reviewId = reviews.data.find(p => p.id === data.id);
        $('#review_id').val(reviewId.id);
        $('#name').val(reviewId.name);
        $('#email').val(reviewId.email);
        $('#rate').val(reviewId.rate);
        $('#feedback').val(reviewId.feedback);
        const imageUrl = getStorageLink(reviewId.photo_uri)
        $('#profile_pic_input').attr('data-value', imageUrl);
        $('#result').html('');

        $('#add_preview').modal('show');
        dynamicFileCmp();
    });

    const onReviewStatus = (id) => {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
        var formData = new FormData();
        formData.append('id', id);
        $.ajax({
            url: "{{ route('review.status') }}",
            type: 'POST',
            data: formData,
            success: function(data) {
                if (data.error) {
                    Swal.fire({
                        title: 'Error!',
                        text: data.error,
                        icon: 'error'
                    });
                } else {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Review status updated successfully!',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while updating status',
                    icon: 'error'
                });
            },
            cache: false,
            contentType: false,
            processData: false
        })
    }
    
    function showFullMessage(message) {
        Swal.fire({
            title: '<i class="fa fa-comment" style="color: #667eea;"></i> Full Review Message',
            html: '<div style="text-align: left; padding: 15px; line-height: 1.6; color: #2c3e50; font-size: 14px; max-height: 400px; overflow-y: auto;">' + message + '</div>',
            width: '600px',
            confirmButtonColor: '#667eea',
            confirmButtonText: 'Close',
            customClass: {
                popup: 'review-message-popup'
            }
        });
    }

    $(document).ready(function() {
        $('.delete-review-btn').click(function() {
            var reviewId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ route('reviews.destroy', ':id') }}`.replace(':id', reviewId),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'Review has been deleted successfully.',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while deleting the review',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
</body>
</html>
