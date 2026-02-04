@include('layouts.masterhead')
@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
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

                                    <!-- Followup Label Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Followup label</span>
                                        @foreach ($followupLabels as $key => $label)
                                        <button type="button"
                                                class="meta-filter-btn {{ $followupLabelFilter == $key ? 'active' : '' }}"
                                                data-filter="followup_label_filter" data-value="{{ $key }}">
                                            {{ $label }}
                                        </button>
                                        @endforeach
                                    </div>

                                    <div class="search-container">
                                        <form method="GET" action="{{ route('recent_expire.index') }}"
                                              id="searchForm">
                                            <div class="search-box">
                                                <i class="fas fa-search search-icon"></i>
                                                <input type="text" class="search-input" name="search"
                                                       id="globalSearch" value="{{ request('search') }}"
                                                       placeholder="Search all records...">

                                                <!-- Preserve all existing parameters -->
                                                @foreach (request()->all() as $key => $value)
                                                @if ($key !== 'search' && $key !== 'page')
                                                @if (is_array($value))
                                                @foreach ($value as $arrayValue)
                                                <input type="hidden" name="{{ $key }}[]"
                                                       value="{{ $arrayValue }}">
                                                @endforeach
                                                @else
                                                <input type="hidden" name="{{ $key }}"
                                                       value="{{ $value }}">
                                                @endif
                                                @endif
                                                @endforeach

                                                <button type="submit" style="display:none;"></button>
                                                <button type="button" class="clear-search" id="clearSearch">×</button>
                                            </div>
                                            <input type="hidden" name="followup_label_filter"
                                                   value="{{ $followupLabelFilter }}">
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
                                <th>name</th>
                                <th>email</th>
                                <th>Contact No</th>
                                <th>Plan</th>
                                <th>paid amount</th>
                                <th>expire date</th>
                                <th>Email Send</th>
                                <th>Whatsapp Send</th>
                                <th>FollowUp Call</th>
                                <th>Follow By</th>
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

                                    @if ($recentExpire->type == 0)
                                    {{ $related->package_name ?? '-' }}
                                    @elseif($recentExpire->type == 1)
                                    {{ $related->plan->name ?? ($related->name ?? '-') }}
                                    @elseif($recentExpire->type == 2)
                                    {{ $related->plan->name ?? ($related->subPlan->name ?? '-') }}
                                    @endif
                                </td>

                                <td>{{ $recentExpire->amount_with_symbol }}</td>

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
                                    @if (!empty($recentExpire->followup_note) || !empty($recentExpire->followup_label))
                                    <i class="fa-solid fa-circle-info info-icon"
                                       data-id="{{ $recentExpire->id }}"
                                       data-note="{{ $recentExpire->followup_note }}"
                                       data-label="{{ $recentExpire->followup_label }}"></i>

                                    <div class="hover-note">
                                        <strong class="">Label :</strong>
                                        @if (!empty($recentExpire->followup_label))
                                        <span>
                                                            {{ $followupLabels[$recentExpire->followup_label] ?? $recentExpire->followup_label }}</span><br>
                                        @endif
                                        <br>
                                        <strong>Text : </strong>
                                        {{ $recentExpire->followup_note }}
                                    </div>
                                    @endif
                                </td>
                                <td style="word-break: keep-all">{{ $roleManager::getUploaderName($recentExpire->emp_id) }}</td>
                                <td>
                                    <button class="btn btn-outline-primary open-transaction-modal"
                                            style="font-size: 12px;"
                                            data-email="{{ $recentExpire->email ?? ($recentExpire->userData->email ?? '-') }}"
                                            data-contact="{{ $recentExpire->contact_no ?? ($recentExpire->userData->number ?? '-') }}">
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
                    <div class="form-group">
                        <label for="followup_label">Followup Type</label>
                        <select name="followup_label" class="form-control" id="followup_label" required>
                            <option value="">Select label</option>
                            @foreach ($followupLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="followup_note">Note</label>
                        <textarea name="followup_note" class="form-control" id="followup_note" rows="4" placeholder="Enter note"></textarea>
                    </div>
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
                    <div class="form-group">
                        <h6>Email</h6>
                        <input type="email" class="form-control" name="email" required />
                    </div>

                    <div class="form-group">
                        <h6>Contact No</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" name="contact" required="">
                        </div>
                    </div>

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

        // Clear search functionality
        $('#clearSearch').on('click', function() {
            $('#globalSearch').val('');

            // Remove search parameter and submit
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('search');
            currentUrl.searchParams.delete('page'); // Go to first page after clear

            window.location.href = currentUrl.toString();
        });

        // Enter key press to submit form
        $('#globalSearch').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();

                // Remove page parameter to start from first page when searching
                const form = $('#searchForm');
                const pageInput = form.find('input[name="page"]');
                if (pageInput.length) {
                    pageInput.remove();
                }

                form.submit();
            }
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
                $("#followup_label").val('');

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
            let label = $(this).data("label");


            currentOrderId = id;
            $("#followup_order_id").val(id);
            $("#followup_note").val(note || '');
            $("#followup_label").val(label || '');
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

            // Remove page parameter when changing filters
            currentUrl.searchParams.delete('page');

            if (filterName === 'filter_type' && filterValue === 'all') {
                const paramsToRemove = [
                    'filter_type', 'status_filter', 'amount_sort',
                    'whatsapp_filter', 'email_filter', 'followup_filter', 'followup_label_filter'
                ];

                paramsToRemove.forEach(param => {
                    currentUrl.searchParams.delete(param);
                });
            }

            window.location.href = currentUrl.toString();
        });

        // Transaction Modal
        $(".open-transaction-modal").on("click", function() {
            let email = $(this).data('email');
            let contact = $(this).data('contact');

            $('#add_transaction_model').modal('show');
            $('#add_transaction_form [name="email"]').val(email);
            $('#add_transaction_form [name="contact"]').val(contact);
        });

        // AJAX submit for transaction
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