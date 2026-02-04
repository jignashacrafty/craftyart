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
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Records</span>
                                        <button type="button"
                                                class="meta-filter-btn {{ $filterType == 'all' ? 'active' : '' }}"
                                                data-filter="filter_type" data-value="all"> All
                                        </button>
                                    </div>
                                    <!-- Amount -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Amount</span>
                                        <button type="button"
                                                class="meta-filter-btn {{ $amountSort == 'desc' ? 'active' : '' }}"
                                                data-filter="amount_sort" data-value="desc">High
                                        </button>
                                        <button type="button"
                                                class="meta-filter-btn {{ $amountSort == 'asc' ? 'active' : '' }}"
                                                data-filter="amount_sort" data-value="asc">Low
                                        </button>
                                    </div>

                                    <!-- WhatsApp -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">WhatsApp</span>
                                        <button type="button"
                                                class="meta-filter-btn {{ $whatsappFilter == 'sent' ? 'active' : '' }}"
                                                data-filter="whatsapp_filter" data-value="sent">Sent
                                        </button>
                                        <button type="button"
                                                class="meta-filter-btn {{ $whatsappFilter == 'not_sent' ? 'active' : '' }}"
                                                data-filter="whatsapp_filter" data-value="not_sent">Not Sent
                                        </button>
                                    </div>

                                    <!-- Email -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Email</span>
                                        <button type="button"
                                                class="meta-filter-btn {{ $emailFilter == 'sent' ? 'active' : '' }}"
                                                data-filter="email_filter" data-value="sent">Sent
                                        </button>
                                        <button type="button"
                                                class="meta-filter-btn {{ $emailFilter == 'not_sent' ? 'active' : '' }}"
                                                data-filter="email_filter" data-value="not_sent">Not Sent
                                        </button>
                                    </div>

                                    <!-- Followup -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Followup</span>
                                        <button type="button"
                                                class="meta-filter-btn {{ $followupFilter == 'called' ? 'active' : '' }}"
                                                data-filter="followup_filter" data-value="called">Called
                                        </button>
                                        <button type="button"
                                                class="meta-filter-btn {{ $followupFilter == 'not_called' ? 'active' : '' }}"
                                                data-filter="followup_filter" data-value="not_called">Not Called
                                        </button>
                                    </div>

                                    <div class="search-container">
                                        <div class="search-box">
                                            <i class="fas fa-search search-icon"></i>
                                            <input type="text" class="search-input" id="globalSearch"
                                                   placeholder="Search records...">
                                            <button class="clear-search" id="clearSearch">×</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="meta-active-filters">
                                <strong style="color: #65676b; font-size: 13px;">Active filters:</strong>
                                @php
                                $filters = array_filter([
                                $filterType != 'all'
                                ? ['label' => 'Remove Duplicate', 'param' => 'filter_type']
                                : null,
                                $amountSort == 'desc'
                                ? ['label' => 'High Amount', 'param' => 'amount_sort']
                                : null,
                                $amountSort == 'asc'
                                ? ['label' => 'Low Amount', 'param' => 'amount_sort']
                                : null,
                                $whatsappFilter == 'sent'
                                ? ['label' => 'WhatsApp Sent', 'param' => 'whatsapp_filter']
                                : null,
                                $whatsappFilter == 'not_sent'
                                ? ['label' => 'WhatsApp Not Sent', 'param' => 'whatsapp_filter']
                                : null,
                                $emailFilter == 'sent'
                                ? ['label' => 'Email Sent', 'param' => 'email_filter']
                                : null,
                                $emailFilter == 'not_sent'
                                ? ['label' => 'Email Not Sent', 'param' => 'email_filter']
                                : null,
                                $followupFilter == 'called'
                                ? ['label' => 'Followup Called', 'param' => 'followup_filter']
                                : null,
                                $followupFilter == 'not_called'
                                ? ['label' => 'Followup Not Called', 'param' => 'followup_filter']
                                : null,
                                ]);
                                @endphp

                                @if (count($filters) > 0)
                                @foreach ($filters as $filter)
                                <span class="filter-pill">
                                            {{ $filter['label'] }}
                                            <span class="filter-pill-remove"
                                                  onclick="removeFilter('{{ $filter['param'] }}')">×</span>
                                        </span>
                                @endforeach
                                <a href="{{ route('recent_expire.index') }}" class="btn btn-sm btn-outline-danger"
                                   style="padding: 2px 8px; font-size: 11px; margin-left: 8px;">Clear all</a>
                                @else
                                <span style="color: #8a8d91; font-size: 12px;">No filters applied</span>
                                @endif
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
                                <th>name</th>
                                <th>email</th>
                                <th>Contact No</th>
                                <th>Plan</th>
                                <th>paid amount</th>
                                <th>expire date</th>
                                <th>Email Send</th>
                                <th>Whatsapp Send</th>
                                <th>
                                    <div>FollowUp Call</div>
                                </th>
                                <th>Transaction</th>
                            </tr>
                            </thead>
                            <tbody id="tableBody">
                            @foreach ($recentExpires as $recentExpire)
                            <tr class="searchable-row">
                                <td>{{ $recentExpire->id ?? '-' }}</td>
                                <td>{{ $recentExpire->name ?? ($recentExpire->userData->name ?? '-') }}</td>
                                <td>{{ $recentExpire->email ?? ($recentExpire->userData->email ?? '-') }}</td>
                                <td>{{ $recentExpire->contact_no ?? ($recentExpire->userData->number ?? '-') }}</td>
                                <td>
                                    @php
                                    $related = $recentExpire->related_plan;
                                    @endphp

                                    @if ($related)
                                    @if ($recentExpire->type == 0)
                                    {{ $related->package_name ?? '-' }}
                                    @elseif($recentExpire->type == 1)
                                    {{ $related->plan->name ?? ($related->name ?? '-') }}
                                    @elseif($recentExpire->type == 2)
                                    {{ $related->plan->name ?? ($related->subPlan->name ?? '-') }}
                                    @endif
                                    @else
                                    -
                                    @endif
                                </td>

                                <td>
                                    @php
                                    $symbol = match (strtoupper($recentExpire->currency_code ?? '')) {
                                    'INR' => '₹',
                                    'USD' => '$',
                                    default => '',
                                    };
                                    @endphp
                                    {{ $symbol }}{{ $recentExpire->paid_amount ?? '-' }}
                                </td>

                                <td>{{ $recentExpire->expired_at }}</td>
                                <td style="font-size: 15px;">
                                    @if ($recentExpire->email_template_count > 0)
                                    <span style="color: green; font-weight: bold;">✔
                                                    {{ $recentExpire->email_template_count }}</span>
                                    @else
                                    <span style="color: red; font-weight: bold;">✗</span>
                                    @endif
                                </td>
                                <td style="font-size: 15px;">
                                    @if ($recentExpire->whatsapp_template_count > 0)
                                    <span style="color: green; font-weight: bold;">✔
                                                    {{ $recentExpire->whatsapp_template_count }}</span>
                                    @else
                                    <span style="color: red; font-weight: bold;">✗</span>
                                    @endif
                                </td>
                                <td style="position: relative;">
                                    <input type="checkbox" class="followup-switch switch-btn me-3"
                                           data-id="{{ $recentExpire->id }}" data-size="small"
                                           @if ($recentExpire->followup_call == 1) checked @endif />
                                    @if (!empty($recentExpire->followup_note))
                                    <i class="fa-solid fa-circle-info info-icon"
                                       data-id="{{ $recentExpire->id }}"
                                       data-note="{{ $recentExpire->followup_note }}"></i>
                                    <div class="hover-note">{{ $recentExpire->followup_note }}</div>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-outline-primary open-transaction-modal"
                                            style="font-size: 12px;"
                                            data-user-id="{{ $recentExpire->user_id ?? $recentExpire->id }}"
                                            data-plan-id="{{ $recentExpire->plan_id ?? '' }}">
                                        <i class="fa-solid fa-money-bill-transfer"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $recentExpires])
            </div>
        </div>
    </div>
</div>

<!-- Modal for note -->
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

        // Global Search Functionality
        $('#globalSearch').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            const $clearBtn = $('#clearSearch');

            if (searchTerm.length > 0) {
                $clearBtn.addClass('show');
            } else {
                $clearBtn.removeClass('show');
            }

            $('.searchable-row').each(function() {
                const rowText = $(this).text().toLowerCase();
                if (rowText.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Clear Search
        $('#clearSearch').on('click', function() {
            $('#globalSearch').val('').focus();
            $(this).removeClass('show');
            $('.searchable-row').show();
        });

        // Checkbox change
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
                        url: "{{ route('recent_expire.followupUpdate') }}",
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

        // Info icon click → open modal with existing note
        $(".info-icon").on("dblclick", function() {
            let id = $(this).data("id");
            let note = $(this).data("note");

            currentOrderId = id;
            $("#followup_order_id").val(id);
            $("#followup_note").val(note || '');
            $("#followupModal").modal("show");
        });

        // Modal submit
        $("#followupForm").on("submit", function(e) {
            e.preventDefault();
            let formData = $(this).serialize();

            $.ajax({
                url: "{{ route('recent_expire.followupUpdate') }}",
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

        // Toggle filter functionality
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
                    'filter_type', 'status_filter', 'amount_sort',
                    'whatsapp_filter', 'email_filter', 'followup_filter'
                ];

                paramsToRemove.forEach(param => {
                    currentUrl.searchParams.delete(param);
                });
            }

            window.location.href = currentUrl.toString();
        });


        $(document).on('click', '.open-transaction-modal', function() {
            let userId = $(this).data('user-id');
            let planId = $(this).data('plan-id');

            $('#add_transaction_model').modal('show');
            $('#add_transaction_form [name="user_id"]').val(userId);
            $('#add_transaction_form [name="plan_id"]').val(planId);
        });

        // AJAX submit
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
                beforeSend: function() {
                    // Optional loader
                    console.log("Submitting transaction...");
                },
                success: function(data) {
                    console.log(data);
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
    });
</script>
</body>

</html>