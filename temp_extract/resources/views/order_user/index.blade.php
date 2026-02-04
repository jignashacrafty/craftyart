@include('layouts.masterhead')

<div class="main-container">
    <div class="pd-ltr-20-10">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">
                    <div class="header-row">
                        <div class="filters-section">
                            <div class="meta-filter-container">
                                <div class="meta-filter-row">
                                    <!-- Records Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Records</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $filterType == 'all' ? 'active' : '' }}"
                                            data-filter="filter_type" data-value="all">All</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $filterType == 'remove_duplicate' ? 'active' : '' }}"
                                            data-filter="filter_type" data-value="remove_duplicate">No Dupes</button>
                                    </div>

                                    <!-- Status Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Status</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $statusFilter == 'pending' ? 'active' : '' }}"
                                            data-filter="status_filter" data-value="pending">Pending</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $statusFilter == 'failed' ? 'active' : '' }}"
                                            data-filter="status_filter" data-value="failed">Failed</button>
                                    </div>

                                    <!-- Amount Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Amount</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $amountSort == 'desc' ? 'active' : '' }}"
                                            data-filter="amount_sort" data-value="desc">High</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $amountSort == 'asc' ? 'active' : '' }}"
                                            data-filter="amount_sort" data-value="asc">Low</button>
                                    </div>

                                    <!-- WhatsApp Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">WhatsApp</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $whatsappFilter == 'sent' ? 'active' : '' }}"
                                            data-filter="whatsapp_filter" data-value="sent">Sent</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $whatsappFilter == 'not_sent' ? 'active' : '' }}"
                                            data-filter="whatsapp_filter" data-value="not_sent">Not Sent</button>
                                    </div>

                                    <!-- Email Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Email</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $emailFilter == 'sent' ? 'active' : '' }}"
                                            data-filter="email_filter" data-value="sent">Sent</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $emailFilter == 'not_sent' ? 'active' : '' }}"
                                            data-filter="email_filter" data-value="not_sent">Not Sent</button>
                                    </div>

                                    <!-- Followup Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Followup</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $followupFilter == 'called' ? 'active' : '' }}"
                                            data-filter="followup_filter" data-value="called">Called</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $followupFilter == 'not_called' ? 'active' : '' }}"
                                            data-filter="followup_filter" data-value="not_called">Not Called</button>
                                    </div>

                                    <!-- Type Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Type</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $typeFilter == 'old_sub' ? 'active' : '' }}"
                                            data-filter="type_filter" data-value="old_sub">Old Sub</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $typeFilter == 'new_sub' ? 'active' : '' }}"
                                            data-filter="type_filter" data-value="new_sub">New Sub</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $typeFilter == 'template' ? 'active' : '' }}"
                                            data-filter="type_filter" data-value="template">Template</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $typeFilter == 'video' ? 'active' : '' }}"
                                            data-filter="type_filter" data-value="video">Video</button>
                                    </div>


                                    <!-- Search Box -->
                                    <div class="search-container">
                                        <form method="GET" action="{{ route('order_user.index') }}" id="searchForm">
                                            <div class="search-box">
                                                <i class="fas fa-search search-icon"></i>
                                                <input type="text" class="search-input" id="globalSearch"
                                                    name="search" value="{{ $searchTerm ?? '' }}"
                                                    placeholder="Search all records...">
                                                @if (isset($searchTerm) && !empty($searchTerm))
                                                    <button type="button" class="clear-search"
                                                        id="clearSearch">×</button>
                                                @endif
                                            </div>
                                            <!-- Hidden fields to preserve filters -->
                                            <input type="hidden" name="filter_type" value="{{ $filterType }}">
                                            <input type="hidden" name="status_filter" value="{{ $statusFilter }}">
                                            <input type="hidden" name="type_filter" value="{{ $typeFilter }}">
                                            <input type="hidden" name="amount_sort" value="{{ $amountSort }}">
                                            <input type="hidden" name="whatsapp_filter"
                                                value="{{ $whatsappFilter }}">
                                            <input type="hidden" name="email_filter" value="{{ $emailFilter }}">
                                            <input type="hidden" name="followup_filter"
                                                value="{{ $followupFilter }}">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 200px) !important;">
                        <table id="temp_table" class="table table-striped table-bordered mb-0"
                            style="table-layout: fixed; width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Contact No</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Link</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Email Send</th>
                                    <th>Whatsapp Send</th>
                                    <th>FollowUp Call</th>
                                    <th>Transaction</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @if ($OrderUsers->count() > 0)
                                    @foreach ($OrderUsers as $OrderUser)
                                        <tr class="searchable-row">
                                            <td>{{ $OrderUser->id ?? '-' }}</td>
                                            <td>{{ $OrderUser->name ?? ($OrderUser->user->name ?? '-') }}</td>
                                            <td>{{ $OrderUser->email ?? ($OrderUser->user->email ?? '-') }}</td>
                                            <td>
                                                {{ !empty($OrderUser->contact_no) ? $OrderUser->contact_no : (!empty($OrderUser->user->number) ? ($OrderUser->user->country_code ?? '') . ' ' . $OrderUser->user->number : '-') }}
                                            </td>
                                            <td>
                                                @php
                                                    $symbol = match (strtoupper($OrderUser->currency ?? '')) {
                                                        'INR' => '₹',
                                                        'USD' => '$',
                                                        default => '',
                                                    };
                                                @endphp
                                                {{ $symbol }}{{ $OrderUser->amount ?? '-' }}
                                            </td>
                                            <td>
                                                @php
                                                    $typeName = match ($OrderUser->type ?? '') {
                                                        'old_sub' => 'Old Subscription',
                                                        'new_sub' => 'New Subscription',
                                                        'template' => 'Template',
                                                        'video' => 'Video',
                                                        default => '-',
                                                    };
                                                @endphp
                                                {{ $typeName }}
                                            </td>
                                            <td>
                                                @if (in_array($OrderUser->type, ['template', 'video']) && !empty($OrderUser->plan_id))
                                                    @php
                                                        $templateData = json_decode($OrderUser->plan_id, true);
                                                    @endphp
                                                    @foreach ($templateData as $item)
                                                        @php
                                                            $design = $allDesigns[$item['id']] ?? null;
                                                        @endphp
                                                        @if ($design)
                                                            <a href="{{ \App\Http\Controllers\HelperController::getFrontendPageUrl(0, $design->id_name) }}"
                                                                target="_blank">
                                                                {{ $design->string_id }}
                                                            </a><br>
                                                        @else
                                                            {{ $item['id'] }}<br>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    {{ $OrderUser->plan_id ?? '-' }}
                                                @endif
                                            </td>
                                            <td>
                                                @if (isset($OrderUser->status))
                                                    @if ($OrderUser->status == 'pending')
                                                        <span
                                                            class="badge badge-warning">{{ $OrderUser->status }}</span>
                                                    @elseif($OrderUser->status == 'failed')
                                                        <span
                                                            class="badge badge-danger">{{ $OrderUser->status }}</span>
                                                    @else
                                                        {{ $OrderUser->status }}
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $OrderUser->created_at }}</td>
                                            <td style="font-size: 15px;">
                                                @if ($OrderUser->email_template_count > 0)
                                                    <span style="color: green; font-weight: bold;">✔
                                                        {{ $OrderUser->email_template_count }}</span>
                                                @else
                                                    <span style="color: red; font-weight: bold;">✗</span>
                                                @endif
                                            </td>
                                            <td style="font-size: 15px;">
                                                @if ($OrderUser->whatsapp_template_count > 0)
                                                    <span style="color: green; font-weight: bold;">✔
                                                        {{ $OrderUser->whatsapp_template_count }}</span>
                                                @else
                                                    <span style="color: red; font-weight: bold;">✗</span>
                                                @endif
                                            </td>
                                            <td style="position: relative;">
                                                <input type="checkbox" class="followup-switch switch-btn me-3"
                                                    data-id="{{ $OrderUser->id }}" data-size="small"
                                                    @if ($OrderUser->followup_call == 1) checked @endif />
                                                @if (!empty($OrderUser->followup_note))
                                                    <i class="fa-solid fa-circle-info info-icon"
                                                        data-id="{{ $OrderUser->id }}"
                                                        data-note="{{ $OrderUser->followup_note }}"></i>
                                                    <div class="hover-note">{{ $OrderUser->followup_note }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-outline-primary open-transaction-modal"
                                                    style="font-size: 12px;"
                                                    data-user-id="{{ $OrderUser->user_id ?? $OrderUser->id }}"
                                                    data-plan-id="{{ $OrderUser->plan_id ?? '' }}">
                                                    <i class="fa-solid fa-money-bill-transfer"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="13" class="text-center">No records found</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $OrderUsers])
            </div>
        </div>
    </div>
</div>

<!-- Followup Modal -->
<div class="modal fade" id="followupModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="followupForm">
            @csrf
            <input type="hidden" name="id" id="followup_order_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Follow Up Note</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <textarea name="followup_note" class="form-control" id="followup_note" rows="4" placeholder="Enter note"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Transaction Modal -->
<div class="modal fade" id="add_transaction_model" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Transaction</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="post" id="add_transaction_form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="user_id" />
                    <input type="hidden" name="plan_id" />

                    <div class="form-group">
                        <h6>Method</h6>
                        <input type="text" class="form-control" name="method" required />
                    </div>

                    <div class="form-group">
                        <h6>Plan ID</h6>
                        <div class="input-group custom">
                            <select class="selectpicker form-control" data-style="btn-outline-primary"
                                name="plan_id">
                                @foreach ($datas['packageArray'] as $package)
                                    <option value="{{ $package->id }}">{{ $package->package_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Transaction ID</h6>
                        <input type="text" class="form-control" name="transaction_id" required />
                    </div>

                    <div class="form-group">
                        <h6>Currency</h6>
                        <select class="form-control" name="currency_code">
                            <option value="INR">INR</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Price Amount</h6>
                        <input type="number" class="form-control" name="price_amount" required />
                    </div>

                    <div class="form-group">
                        <h6>Paid Amount</h6>
                        <input type="number" class="form-control" name="paid_amount" required />
                    </div>

                    <div class="form-group">
                        <h6>From Wallet</h6>
                        <select class="form-control" name="fromWallet">
                            <option value="0">False</option>
                            <option value="1">True</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>From Where</h6>
                        <select class="form-control" name="fromWhere">
                            <option value="Mobile">Mobile</option>
                            <option value="Web">Web</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Coins</h6>
                        <input type="number" class="form-control" name="coins" value="0" required />
                    </div>

                    <div class="row mt-3">
                        <div class="col-sm-12">
                            <button class="btn btn-primary btn-block" type="submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

@include('layouts.masterscript')

<script>
    function removeFilter(paramName) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.delete(paramName);
        window.location.href = currentUrl.toString();
    }

    $(document).ready(function() {
        let currentOrderId = null;

        // Search on Enter key press only
        $('#globalSearch').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                $('#searchForm').submit();
            }
        });

        // Clear search
        $('#clearSearch').on('click', function() {
            $('#globalSearch').val('');
            $('#searchForm').submit();
        });

        // Show/hide clear button based on search input
        $('#globalSearch').on('input', function() {
            const $clearBtn = $('#clearSearch');
            if ($(this).val().length > 0) {
                $clearBtn.show();
            } else {
                $clearBtn.hide();
            }
        });

        // Initialize Event Handlers
        function initializeEventHandlers() {
            // Followup Switch
            $(".followup-switch").on("change", function() {
                let id = $(this).data("id");
                let isChecked = $(this).is(":checked");
                let $checkbox = $(this);

                if (isChecked) {
                    currentOrderId = id;
                    $("#followup_order_id").val(id);
                    $("#followup_note").val('');
                    $("#followupModal").modal("show");
                } else {
                    if (confirm("Are you sure you want to uncheck this follow-up?")) {
                        $.ajax({
                            url: "{{ route('order_user.followupUpdate') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                id: id,
                                followup_call: 0
                            },
                            success: function(res) {
                                window.location.reload();
                            },
                            error: function(err) {
                                alert("Error: " + err.responseText);
                                $checkbox.prop("checked", true);
                            }
                        });
                    } else {
                        $checkbox.prop("checked", true);
                    }
                }
            });

            // Info Icon Double Click
            $(".info-icon").on("dblclick", function() {
                let id = $(this).data("id");
                let note = $(this).data("note");

                currentOrderId = id;
                $("#followup_order_id").val(id);
                $("#followup_note").val(note || '');
                $("#followupModal").modal("show");
            });

            // Transaction Modal
            $(".open-transaction-modal").on("click", function() {
                let userId = $(this).data('user-id');
                let planId = $(this).data('plan-id');

                $('#add_transaction_model').modal('show');
                $('#add_transaction_form [name="user_id"]').val(userId);
                $('#add_transaction_form [name="plan_id"]').val(planId);
            });
        }

        // Filter Toggle
        $('.meta-filter-btn').on('click', function() {
            const filterName = $(this).data('filter');
            const filterValue = $(this).data('value');
            const currentUrl = new URL(window.location.href);

            const currentValue = currentUrl.searchParams.get(filterName);

            if (currentValue === filterValue) {
                currentUrl.searchParams.delete(filterName);
            } else {
                currentUrl.searchParams.set(filterName, filterValue);
            }

            if (filterName === 'filter_type' && filterValue === 'all') {
                const paramsToRemove = [
                    'filter_type', 'status_filter', 'type_filter', 'amount_sort',
                    'whatsapp_filter', 'email_filter', 'followup_filter'
                ];
                paramsToRemove.forEach(param => currentUrl.searchParams.delete(param));
            }

            window.location.href = currentUrl.toString();
        });

        // Transaction Form Submission
        $('#add_transaction_form').on('submit', function(event) {
            event.preventDefault();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var formData = new FormData(this);

            $.ajax({
                url: "{{ route('manage_subscription.submit') }}",
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                    if (data.status) {
                        alert(data.success || 'Subscription added successfully!');
                        location.reload();
                    } else {
                        alert(data.success || data.error || 'Something went wrong!');
                    }
                },
                error: function(error) {
                    console.error(error);
                    alert('AJAX Error: ' + error.responseText);
                }
            });
        });

        // Followup Form Submission
        $("#followupForm").on("submit", function(e) {
            e.preventDefault();
            let formData = $(this).serialize();

            $.ajax({
                url: "{{ route('order_user.followupUpdate') }}",
                type: "POST",
                data: formData,
                success: function(res) {
                    window.location.reload();
                    $("#followupModal").modal("hide");
                },
                error: function(err) {
                    alert("Error: " + err.responseText);
                }
            });
        });

        // Initial Setup
        initializeEventHandlers();
    });
</script>
</body>

</html>
