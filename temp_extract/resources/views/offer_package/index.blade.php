@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@include('layouts.masterhead')
<div class="main-container designer-access-container">
    <div class="min-height-200px">
        <div class="card-box">
            <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                <div class="row justify-content-between">
                    <div class="col-md-3">
                        @if ($roleManager::onlyDesignerAccess(Auth::user()->user_type))
                            <button type="button" class="btn btn-primary m-1 item-form-input"
                                id="addNewbackgroundItemBtn">
                                + Add Offer package
                            </button>
                        @endif
                    </div>
                </div>

                <div class="scroll-wrapper table-responsive tableFixHead"
                    style="max-height: calc(110vh - 220px) !important">
                    <table id="temp_table" style="table-layout: fixed; width: 100%;"
                        class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Plan</th>
                                <th>Duration</th>
                                <th>bonus Code</th>
                                <th>Status</th>
                                <th class="datatable-nosort" style="width:150px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($OfferPackage as $row)
                                <tr id="row_{{ $row->id }}">
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->plan->name ?? '-' }}</td>
                                    <td>{{ $row->duration->name ?? '-' }}</td>
                                    <td>
                                        <span
                                            class="badge badge-info">{{ $row->BonusPackage->bonus_code ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @if ($row->status)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="dropdown-item edit-bonus" data-id="{{ $row->id }}"><i
                                                class="dw dw-edit2"></i> Edit</button>
                                        <button class="dropdown-item delete-bonus" data-id="{{ $row->id }}"><i
                                                class="dw dw-delete-3"></i> Delete</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <hr class="my-1">
        </div>
    </div>
</div>

<div class="modal fade" id="bounce_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 676px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Add Offer packeg</h5>
                <button type="button" class="close" data-bs-dismiss="modal">×</button>
            </div>
            <div class="modal-body">
                <form id="bounce_form" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="bounce_id">

                    <div class="form-group mb-3">
                        <label>Select Plan</label>
                        <select name="plan_id" id="plan_id" class="form-control" required>
                            <option value="" selected>-- Select Plan --</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->string_id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Select Sub Plan / Duration</label>
                        <select name="sub_plan_id" id="sub_plan_id" class="form-control" required>
                            <option value="">-- Select Sub Plan & Duration --</option>
                        </select>
                    </div>

                    <input type="hidden" name="duration_id" id="duration_id">

                    <div class="form-group mb-3">
                        <label>Select bonus package</label>
                        <select name="bounce_code_id" id="bounce_code_id" class="form-control" required>
                            <option value="">-- Select bonus package --</option>
                            @foreach ($BouncePackages as $code)
                                <option value="{{ $code->id }}">{{ $code->bonus_code }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0" selected>Inactive</option>
                        </select>
                    </div>

                    <div class="align-content-end">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@include('layouts.masterscript')

<script>
    $(document).on("change", "#plan_id", function() {
        let planId = $(this).val();
        $("#sub_plan_id").html('<option value="">-- Select Sub Plan & Duration --</option>');
        if (planId) {
            $.ajax({
                url: "{{ route('offer-package.getDurations', '') }}/" + planId,
                type: "GET",
                success: function(res) {
                    $.each(res, function(index, item) {
                        $("#sub_plan_id").append(
                            `<option value="${item.sub_plan_id}" data-duration="${item.duration_id}">
                                ${item.duration_name}
                            </option>`
                        );
                    });
                }
            });
        }
    });

    // 🔹 Sync duration_id hidden field when sub_plan changes
    $(document).on("change", "#sub_plan_id", function() {
        let durationId = $(this).find(":selected").data("duration");
        $("#duration_id").val(durationId);
    });

    // 🔹 Open Modal (Add New)
    $(document).on("click", "#addNewbackgroundItemBtn", function() {
        $("#bounce_form")[0].reset();
        $("#bounce_id").val("");
        $("#sub_plan_id").html('<option value="">-- Select Sub Plan & Duration --</option>');
        $("#modal_title").text("Add Offer packeg");
        $("#bounce_modal").modal("show");
    });
    $(document).on("click", ".edit-bonus", function() {
        let id = $(this).data("id");
        $.get("{{ url('offer-package') }}/" + id + "/edit", function(res) {
            $("#bounce_id").val(res.id);
            $("#plan_id").val(res.plan_id).trigger("change");

            // Wait for durations to load, then set sub_plan and duration
            setTimeout(() => {
                $("#sub_plan_id").val(res.sub_plan_id).trigger("change");
                $("#duration_id").val(res.duration_id);
            }, 500);

            $("#bounce_code_id").val(res.bounce_code_id);
            $("#status").val(res.status);
            $("#modal_title").text("Edit Offer packeg");
            $("#bounce_modal").modal("show");
        });
    });

    // 🔹 Save Form (Ajax)
    $("#bounce_form").on("submit", function(e) {
        e.preventDefault();
        let formData = $(this).serialize();
        $.post("{{ route('offer-package.store') }}", formData, function(res) {
            if (res.status) {
                $("#bounce_modal").modal("hide");
                window.location.reload();
            } else {
                alert(res.message);
            }
        });
    });

    // 🔹 Delete
    $(document).on("click", ".delete-bonus", function() {
        if (!confirm("Are you sure?")) return;
        let id = $(this).data("id");
        $.ajax({
            url: "{{ url('offer-package') }}/" + id,
            type: "DELETE",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res.status) {
                    $("#row_" + id).remove();
                }
            }
        });
    });
</script>
