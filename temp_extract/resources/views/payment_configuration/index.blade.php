@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container seo-all-container">
    <div class="pd-ltr-10 xs-pd-20-10">
        <div class="min-height-200px">

            {{-- Common Add Gateway Modal --}}
            <div class="modal fade" id="addGatewayModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Payment Gateway</h5>
                            <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="addGatewayForm">
                            @csrf
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Gateway Name *</label>
                                            <input type="text" name="gateway" class="form-control" required
                                                   placeholder="e.g., PayPal, Paytm">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Payment Scope *</label>
                                            <select name="scope" class="form-control" required>
                                                <option value="">Select Scope</option>
                                                <option value="NATIONAL">National</option>
                                                <option value="INTERNATIONAL">International</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Credentials Configuration *</h6>
                                        <button type="button" class="btn btn-sm btn-success" id="addNewCredentialField">
                                            <i class="fas fa-plus"></i> Add Field
                                        </button>
                                    </div>

                                    <div id="addCredentialsContainer">
                                        <div class="credential-field-row mb-3">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <div class="form-group mb-2">
                                                        <label class="small">Field Key *</label>
                                                        <input type="text"
                                                               class="form-control credential-key"
                                                               name="credential_keys[]"
                                                               placeholder="e.g., api_key, secret_key"
                                                               required>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="form-group mb-2">
                                                        <label class="small">Field Value *</label>
                                                        <input type="text"
                                                               class="form-control credential-value"
                                                               name="credential_values[]"
                                                               placeholder="Enter value"
                                                               required>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-sm btn-danger remove-add-credential-field"
                                                            style="margin-bottom: 8px;" disabled>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add Gateway</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Edit Gateway Modal --}}
            <div class="modal fade" id="editGatewayModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Gateway Configuration</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="editGatewayForm">
                            @csrf
                            @method('POST')
                            <input type="hidden" name="gateway_id" id="editGatewayId">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Gateway Name *</label>
                                            <input type="text" name="gateway" id="editGatewayName"
                                                   class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Payment Scope *</label>
                                            <select name="scope" id="editGatewayScope" class="form-control" required>
                                                <option value="NATIONAL">National</option>
                                                <option value="INTERNATIONAL">International</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Credentials Configuration *</h6>
                                        <button type="button" class="btn btn-sm btn-success" id="addEditCredentialField">
                                            <i class="fas fa-plus"></i> Add Field
                                        </button>
                                    </div>

                                    <div id="editCredentialsContainer">
                                        <!-- Dynamic fields will be added here -->
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Update Gateway</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Common Add Button --}}
            <div class="bg-transparent mb-2">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addGatewayModal">
                    <i class="fas fa-plus mr-1"></i> Add New Payment Gateway
                </button>
            </div>

            {{-- National Payment Section --}}
            <div class="card mb-3">
                <div class="card-header py-2">
                    <h6 class="mb-0">National Payment Options (India)</h6>
                    <small class="text-muted">Select one gateway for national payments</small>
                </div>
                <div class="card-body p-3">
                    <form id="nationalPaymentForm">
                        @csrf
                        <input type="hidden" name="scope" value="NATIONAL">
                        <div class="row">
                            @foreach($nationalGateways as $gateway)
                            @php
                            $isActive = $gateway->is_active;
                            $credentials = $gateway->credentials;
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div class="card gateway-card h-100 {{ $isActive ? 'border-success' : 'border-light' }}">
                                    <div class="px-3 py-2 w-100 d-flex justify-content-between align-items-center">
                                        {{-- Left side: Radio button, Gateway name, and Status badge --}}
                                        <div class="d-flex align-items-center">
                                            {{-- Radio button --}}
                                            <!--                                            <div class="form-check m-0 mr-3">-->
                                            <input type="radio"
                                                   id="national-{{ $gateway->id }}"
                                                   name="gateway"
                                                   value="{{ $gateway->gateway }}"
                                                   class="national-gateway-radio"
                                                   {{ $isActive ? 'checked' : '' }}
                                            data-id="{{ $gateway->id }}">
                                            <!--                                            </div>-->

                                            {{-- Gateway name --}}
                                            <label class="form-check-label font-weight-bold m-0" for="national-{{ $gateway->id }}">
                                                {{ ucfirst(str_replace('_', ' ', $gateway->gateway)) }}
                                            </label>

                                            {{-- Status badge - Show only ONE badge --}}
                                            @if($isActive)
                                            <span class="badge badge-success badge-pill ml-2 status-badge" id="badge-{{ $gateway->id }}">
            <i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Active
        </span>
                                            @else
                                            <span class="badge badge-secondary badge-pill ml-2 status-badge" id="badge-{{ $gateway->id }}">
            <i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Inactive
        </span>
                                            @endif
                                        </div>

                                        {{-- Right side: Edit and Delete buttons --}}
                                        <div class="d-flex align-items-center">
                                            <button type="button" class="btn btn-sm btn-outline-info edit-gateway-btn mr-2"
                                                    data-id="{{ $gateway->id }}"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-gateway-btn"
                                                    data-id="{{ $gateway->id }}"
                                                    data-name="{{ $gateway->gateway }}"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="gateway-fields"
                                             id="national-fields-{{ $gateway->id }}"
                                             style="{{ !$isActive ? 'opacity: 0.6;' : '' }}">
                                            @if(count($credentials) > 0)
                                            @foreach($credentials as $fieldKey => $fieldValue)
                                            <div class="form-group mb-2">
                                                <label for="national-{{ $gateway->id }}-{{ $fieldKey }}"
                                                       class="form-label small mb-1">
                                                    {{ ucfirst(str_replace('_', ' ', $fieldKey)) }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="text"
                                                       class="form-control form-control-sm gateway-input"
                                                       id="national-{{ $gateway->id }}-{{ $fieldKey }}"
                                                       name="credentials[{{ $fieldKey }}]"
                                                       value="{{ $fieldValue }}"
                                                       placeholder="Enter {{ str_replace('_', ' ', $fieldKey) }}"
                                                       {{ !$isActive ? 'disabled' : '' }}
                                                required>
                                            </div>
                                            @endforeach
                                            @else
                                            <div class="text-center py-3 text-muted">
                                                <i class="fas fa-key fa-2x mb-2"></i>
                                                <p class="mb-1 small">No credentials configured</p>
                                                <small class="d-block">Select and save to add credentials</small>
                                                <small class="d-block">or click edit button to add fields</small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            @if(count($nationalGateways) === 0)
                            <div class="col-12">
                                <div class="alert alert-warning text-center py-3">
                                    <i class="fas fa-exclamation-circle fa-lg mb-2"></i>
                                    <h6 class="alert-heading mb-1">No National Gateways</h6>
                                    <p class="mb-0 small">Click "Add New Payment Gateway" button above to add national payment gateways.</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        @if(count($nationalGateways) > 0)
                        <div class="row mt-3">
                            <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-primary btn-sm" id="saveNationalConfig">
                                    <i class="fas fa-save mr-1"></i> Save National Configuration
                                </button>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- International Payment Section --}}
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="mb-0">International Payment Options</h6>
                    <small class="text-muted">Select one gateway for international payments</small>
                </div>
                <div class="card-body p-3">
                    <form id="internationalPaymentForm">
                        @csrf
                        <input type="hidden" name="scope" value="INTERNATIONAL">
                        <div class="row">
                            @foreach($internationalGateways as $gateway)
                            @php
                            $isActive = $gateway->is_active;
                            $credentials = $gateway->credentials;
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div class="card gateway-card h-100 {{ $isActive ? 'border-success' : 'border-light' }}">
                                    <div class="px-3 py-2 w-100 d-flex justify-content-between align-items-center">
                                        {{-- Left side: Radio button, Gateway name, and Status badge --}}
                                        <div class="d-flex align-items-center">
                                            {{-- Radio button --}}
                                            <!--                                            <div class="form-check m-0 mr-3">-->
                                            <input type="radio"
                                                   id="international-{{ $gateway->id }}"
                                                   name="gateway"
                                                   value="{{ $gateway->gateway }}"
                                                   class="international-gateway-radio"
                                                   {{ $isActive ? 'checked' : '' }}
                                            data-id="{{ $gateway->id }}">
                                            <!--                                            </div>-->

                                            {{-- Gateway name --}}
                                            <label class="form-check-label font-weight-bold m-0" for="national-{{ $gateway->id }}">
                                                {{ ucfirst(str_replace('_', ' ', $gateway->gateway)) }}
                                            </label>

                                            {{-- Status badge - Show only ONE badge --}}
                                            @if($isActive)
                                            <span class="badge badge-success badge-pill ml-2 status-badge" id="badge-{{ $gateway->id }}">
            <i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Active
        </span>
                                            @else
                                            <span class="badge badge-secondary badge-pill ml-2 status-badge" id="badge-{{ $gateway->id }}">
            <i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Inactive
        </span>
                                            @endif
                                        </div>

                                        {{-- Right side: Edit and Delete buttons --}}
                                        <div class="d-flex align-items-center">
                                            <button type="button" class="btn btn-sm btn-outline-info edit-gateway-btn mr-2"
                                                    data-id="{{ $gateway->id }}"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-gateway-btn"
                                                    data-id="{{ $gateway->id }}"
                                                    data-name="{{ $gateway->gateway }}"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="gateway-fields"
                                             id="international-fields-{{ $gateway->id }}"
                                             style="{{ !$isActive ? 'opacity: 0.6;' : '' }}">
                                            @if(count($credentials) > 0)
                                            @foreach($credentials as $fieldKey => $fieldValue)
                                            <div class="form-group mb-2">
                                                <label for="international-{{ $gateway->id }}-{{ $fieldKey }}"
                                                       class="form-label small mb-1">
                                                    {{ ucfirst(str_replace('_', ' ', $fieldKey)) }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="text"
                                                       class="form-control form-control-sm gateway-input"
                                                       id="international-{{ $gateway->id }}-{{ $fieldKey }}"
                                                       name="credentials[{{ $fieldKey }}]"
                                                       value="{{ $fieldValue }}"
                                                       placeholder="Enter {{ str_replace('_', ' ', $fieldKey) }}"
                                                       {{ !$isActive ? 'disabled' : '' }}
                                                required>
                                            </div>
                                            @endforeach
                                            @else
                                            <div class="text-center py-3 text-muted">
                                                <i class="fas fa-key fa-2x mb-2"></i>
                                                <p class="mb-1 small">No credentials configured</p>
                                                <small class="d-block">Select and save to add credentials</small>
                                                <small class="d-block">or click edit button to add fields</small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            @if(count($internationalGateways) === 0)
                            <div class="col-12">
                                <div class="alert alert-warning text-center py-3">
                                    <i class="fas fa-exclamation-circle fa-lg mb-2"></i>
                                    <h6 class="alert-heading mb-1">No International Gateways</h6>
                                    <p class="mb-0 small">Click "Add New Payment Gateway" button above to add international payment gateways.</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        @if(count($internationalGateways) > 0)
                        <div class="row mt-3">
                            <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-primary btn-sm" id="saveInternationalConfig">
                                    <i class="fas fa-save mr-1"></i> Save International Configuration
                                </button>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')

<style>

    .national-gateway-radio {
        width: 16px;
        height: 16px;
    }

    .card-header {
        background-color: #f8f9fa !important;
        padding: 8px 12px !important;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }

    .form-check-input {
        width: 16px;
        height: 16px;
        margin-top: 0;
        cursor: pointer;
    }

    .form-check-label {
        font-size: 0.95rem;
        color: #343a40;
        font-weight: 600;
        cursor: pointer;
        margin-right: 0 !important;
    }

    .status-badge {
        font-size: 0.7rem;
        padding: 3px 8px;
        font-weight: 500;
        margin-left: 8px !important;
    }

    .badge-success {
        background-color: #28a745;
    }

    .badge-secondary {
        background-color: #6c757d;
    }

    .btn-sm {
        padding: 4px 8px;
        font-size: 0.8rem;
        line-height: 1.2;
    }

    .btn-outline-info {
        color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-outline-info:hover {
        background-color: #17a2b8;
        color: white;
    }

    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }

    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }

    /* Ensure proper spacing between elements in card header */
    .d-flex.align-items-center {
        gap: 8px;
    }

    /* Make sure the badge doesn't wrap */
    .status-badge {
        white-space: nowrap;
    }
    .gateway-card {
        transition: all 0.2s ease;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        overflow: hidden;
        font-size: 0.9rem;
    }

    .gateway-card.border-success {
        border-color: #28a745 !important;
        box-shadow: 0 0 0 1px rgba(40, 167, 69, 0.1);
    }

    .gateway-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .card-header {
        background-color: #f8f9fa !important;
        padding: 8px 12px !important;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }

    .form-check-input {
        width: 16px;
        height: 16px;
        margin-top: 0;
        cursor: pointer;
    }

    .form-check-label {
        font-size: 0.95rem;
        color: #343a40;
        font-weight: 600;
        cursor: pointer;
    }

    .badge-success {
        background-color: #28a745;
        font-size: 0.7rem;
        padding: 3px 8px;
        font-weight: 500;
    }

    .badge-secondary {
        background-color: #6c757d;
        font-size: 0.7rem;
        padding: 3px 8px;
        font-weight: 500;
    }

    .btn-sm {
        padding: 4px 8px;
        font-size: 0.8rem;
        line-height: 1.2;
    }

    .btn-outline-info {
        color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-outline-info:hover {
        background-color: #17a2b8;
        color: white;
    }

    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }

    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }

    .credential-field-row {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 8px;
        margin-bottom: 8px;
        background: #f8f9fa;
    }

    .card-body {
        padding: 12px !important;
    }

    .form-group {
        margin-bottom: 8px;
    }

    .form-label {
        font-size: 0.8rem;
        font-weight: 500;
    }

    .form-control-sm {
        height: calc(1.5em + 0.5rem + 2px);
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }

    .card-header h6 {
        font-size: 0.9rem;
        font-weight: 600;
    }

    .card-header small {
        font-size: 0.75rem;
        color: #6c757d;
    }

    .alert {
        padding: 8px 12px;
        margin-bottom: 0;
    }

    .alert-heading {
        font-size: 0.9rem;
        margin-bottom: 4px;
    }

    .fa-2x {
        font-size: 1.5em;
    }

    .fa-lg {
        font-size: 1.1em;
    }

    .text-center.py-3 {
        padding-top: 12px !important;
        padding-bottom: 12px !important;
    }

    .mb-3 {
        margin-bottom: 12px !important;
    }

    .mt-3 {
        margin-top: 12px !important;
    }

    .mb-4 {
        margin-bottom: 16px !important;
    }

    .mb-2 {
        margin-bottom: 8px !important;
    }

    .mr-1 {
        margin-right: 4px !important;
    }

    .mr-2 {
        margin-right: 8px !important;
    }

    .mr-3 {
        margin-right: 12px !important;
    }

    .ml-2 {
        margin-left: 8px !important;
    }

    .py-2 {
        padding-top: 8px !important;
        padding-bottom: 8px !important;
    }

    .px-3 {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    .py-3 {
        padding-top: 12px !important;
        padding-bottom: 12px !important;
    }

    .p-3 {
        padding: 12px !important;
    }

    .p-4 {
        padding: 16px !important;
    }

    .modal-body {
        padding: 16px;
    }

    .modal-footer {
        padding: 12px 16px;
    }

    .d-flex.align-items-center {
        gap: 8px;
    }
</style>

<script>
    // Keep the same JavaScript code as before, no changes needed
    $(document).ready(function() {
        let addFieldCounter = 1;
        let editFieldCounter = 0;

        // Add new credential field in add modal
        $('#addNewCredentialField').on('click', function() {
            const fieldHtml = `
                <div class="credential-field-row mb-3">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label class="small">Field Key *</label>
                                <input type="text"
                                       class="form-control credential-key"
                                       name="credential_keys[]"
                                       placeholder="e.g., api_key, secret_key"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label class="small">Field Value *</label>
                                <input type="text"
                                       class="form-control credential-value"
                                       name="credential_values[]"
                                       placeholder="Enter value"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-danger remove-add-credential-field"
                                    style="margin-bottom: 8px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
            $('#addCredentialsContainer').append(fieldHtml);

            // Enable remove button for first field if there are more than 1 fields
            if ($('#addCredentialsContainer .credential-field-row').length > 1) {
                $('#addCredentialsContainer .credential-field-row:first .remove-add-credential-field').prop('disabled', false);
            }
        });

        // Remove credential field in add modal
        $(document).on('click', '.remove-add-credential-field', function() {
            if ($('#addCredentialsContainer .credential-field-row').length > 1) {
                $(this).closest('.credential-field-row').remove();

                // Disable remove button for first field if only 1 field remains
                if ($('#addCredentialsContainer .credential-field-row').length === 1) {
                    $('#addCredentialsContainer .credential-field-row:first .remove-add-credential-field').prop('disabled', true);
                }
            }
        });

        // Add new gateway with credentials
        $('#addGatewayForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const saveBtn = $(this).find('button[type="submit"]');
            const originalText = saveBtn.html();

            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

            // Note: The form already has credential_keys[] and credential_values[] fields
            // so we don't need to process them separately

            $.ajax({
                url: '{{ route("payment.config.add-gateway") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    saveBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        showNotification('success', response.message);
                        $('#addGatewayModal').modal('hide');
                        $('#addGatewayForm')[0].reset();
                        // Reset to one field
                        $('#addCredentialsContainer').html(`
                    <div class="credential-field-row mb-3">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group mb-2">
                                    <label class="small">Field Key *</label>
                                    <input type="text"
                                           class="form-control credential-key"
                                           name="credential_keys[]"
                                           placeholder="e.g., api_key, secret_key"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group mb-2">
                                    <label class="small">Field Value *</label>
                                    <input type="text"
                                           class="form-control credential-value"
                                           name="credential_values[]"
                                           placeholder="Enter value"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-danger remove-add-credential-field"
                                        style="margin-bottom: 8px;" disabled>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    saveBtn.prop('disabled', false).html(originalText);
                    let message = 'Error adding gateway';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        message = Object.values(errors).flat().join('<br>');
                    }
                    showNotification('error', message);
                }
            });
        });

        // Edit gateway - Open modal with data
        $(document).on('click', '.edit-gateway-btn', function(e) {
            e.stopPropagation();
            const gatewayId = $(this).data('id');

            // Show loading
            $('#editGatewayModal').modal('show');
            $('#editGatewayModal .modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');

            // Load gateway data using $.get
            $.get(`payment_configuration/${gatewayId}/get`, function(response) {
                if (response.success) {
                    const gateway = response.gateway;
                    populateEditModal(gateway);
                } else {
                    $('#editGatewayModal').modal('hide');
                    showNotification('error', response.message || 'Failed to load gateway data');
                }
            }).fail(function() {
                $('#editGatewayModal').modal('hide');
                showNotification('error', 'Failed to load gateway data');
            });
        });

        // Populate edit modal with gateway data
        function populateEditModal(gateway) {
            const credentials = gateway.credentials || {};

            $('#editGatewayModal .modal-body').html(`
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Gateway Name *</label>
                            <input type="text" name="gateway" id="editGatewayName"
                                   class="form-control" value="${gateway.gateway}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Payment Scope *</label>
                            <select name="scope" id="editGatewayScope" class="form-control" required>
                                <option value="NATIONAL" ${gateway.payment_scope === 'NATIONAL' ? 'selected' : ''}>National</option>
                                <option value="INTERNATIONAL" ${gateway.payment_scope === 'INTERNATIONAL' ? 'selected' : ''}>International</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Credentials Configuration *</h6>
                        <button type="button" class="btn btn-sm btn-success" id="addEditCredentialField">
                            <i class="fas fa-plus"></i> Add Field
                        </button>
                    </div>

                    <div id="editCredentialsContainer">
                        <!-- Dynamic fields will be added here -->
                    </div>
                </div>
            `);

            // Clear and populate credentials container
            $('#editCredentialsContainer').empty();
            editFieldCounter = 0;

            if (Object.keys(credentials).length > 0) {
                Object.entries(credentials).forEach(([key, value]) => {
                    addEditCredentialField(key, value);
                });
            } else {
                // Add one empty field if no credentials
                addEditCredentialField('', '');
            }

            // Attach event handlers
            $('#addEditCredentialField').off('click').on('click', function() {
                addEditCredentialField('', '');
            });

            // Attach submit handler
            $('#editGatewayForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                updateGateway(gateway.id);
            });
        }

        // Add credential field in edit modal
        function addEditCredentialField(key = '', value = '') {
            const fieldId = editFieldCounter++;
            const fieldHtml = `
                    <div class="credential-field-row mb-3" id="edit-credential-field-${fieldId}">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group mb-2">
                                    <label class="small">Field Key *</label>
                                    <input type="text"
                                           class="form-control edit-credential-key"
                                           name="credential_keys[]"
                                           value="${key}"
                                           placeholder="e.g., api_key, secret_key"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group mb-2">
                                    <label class="small">Field Value *</label>
                                    <input type="text"
                                           class="form-control edit-credential-value"
                                           name="credential_values[]"
                                           value="${value}"
                                           placeholder="Enter value"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-danger remove-edit-credential-field"
                                        data-field-id="${fieldId}" style="margin-bottom: 8px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;

            $('#editCredentialsContainer').append(fieldHtml);

            // Attach remove handler
            $(`#edit-credential-field-${fieldId} .remove-edit-credential-field`).off('click').on('click', function() {
                if ($('#editCredentialsContainer .credential-field-row').length > 1) {
                    $(this).closest('.credential-field-row').remove();
                } else {
                    showNotification('warning', 'At least one credential field is required');
                }
            });
        }

        // Update gateway
        function updateGateway(gatewayId) {
            const gatewayName = $('#editGatewayName').val();
            const scope = $('#editGatewayScope').val();

            // Collect credentials
            const credentialKeys = [];
            const credentialValues = [];
            let isValid = true;

            $('#editCredentialsContainer .credential-field-row').each(function() {
                const key = $(this).find('input[name="credential_keys[]"]').val().trim();
                const value = $(this).find('input[name="credential_values[]"]').val().trim();

                if (!key || !value) {
                    isValid = false;
                    $(this).find('input').addClass('is-invalid');
                } else {
                    $(this).find('input').removeClass('is-invalid');
                    credentialKeys.push(key);
                    credentialValues.push(value);
                }
            });

            if (!isValid) {
                showNotification('error', 'Please fill all credential fields');
                return;
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('gateway', gatewayName);
            formData.append('scope', scope);

            // Add credential keys and values as arrays
            credentialKeys.forEach(key => {
                formData.append('credential_keys[]', key);
            });

            credentialValues.forEach(value => {
                formData.append('credential_values[]', value);
            });

            const saveBtn = $('#editGatewayForm button[type="submit"]');
            const originalText = saveBtn.html();

            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

            // Update using named route
            $.ajax({
                url: `payment_configuration/${gatewayId}/update`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    saveBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        showNotification('success', response.message);
                        $('#editGatewayModal').modal('hide');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    saveBtn.prop('disabled', false).html(originalText);
                    let message = 'Error updating gateway';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        message = Object.values(errors).flat().join('<br>');
                    }
                    showNotification('error', message);
                }
            });
        }

        // Delete gateway
        $(document).on('click', '.delete-gateway-btn', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            const name = $(this).data('name');

            if (confirm(`Are you sure you want to delete "${name}" gateway?`)) {
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                // Delete using named route
                $.ajax({
                    url: `payment_configuration/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('success', response.message);
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            showNotification('error', response.message);
                            $('.delete-gateway-btn').prop('disabled', false).html('<i class="fas fa-trash"></i>');
                        }
                    },
                    error: function(xhr) {
                        let message = 'Error deleting gateway';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        showNotification('error', message);
                        $('.delete-gateway-btn').prop('disabled', false).html('<i class="fas fa-trash"></i>');
                    }
                });
            }
        });

        // Radio button change handlers
        $('.national-gateway-radio').on('change', function() {
            handleGatewaySelection('national', $(this).data('id'));
        });

        $('.international-gateway-radio').on('change', function() {
            handleGatewaySelection('international', $(this).data('id'));
        });

        function handleGatewaySelection(scope, gatewayId) {
            // First, reset all badges in this scope
            $(`[id^="${scope}-fields-"]`).each(function() {
                const currentId = $(this).attr('id').replace(`${scope}-fields-`, '');
                const card = $(this).closest('.gateway-card');
                const badge = card.find('.status-badge');

                if (currentId == gatewayId) {
                    // For selected gateway
                    card.toggleClass('border-success', true)
                        .toggleClass('border-light', false);

                    $(this).find('.gateway-input').prop('disabled', false);
                    $(this).css('opacity', '1');

                    // Update badge to active
                    badge.removeClass('badge-secondary')
                        .addClass('badge-success')
                        .html('<i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Active');

                } else {
                    // For unselected gateways
                    card.toggleClass('border-success', false)
                        .toggleClass('border-light', true);

                    $(this).find('.gateway-input').prop('disabled', true);
                    $(this).css('opacity', '0.6');

                    // Update badge to inactive
                    badge.removeClass('badge-success')
                        .addClass('badge-secondary')
                        .html('<i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Inactive');
                }
            });
        }

        // Save configuration
        $('#saveNationalConfig').on('click', function() {
            saveConfiguration('national');
        });

        $('#saveInternationalConfig').on('click', function() {
            saveConfiguration('international');
        });

        function saveConfiguration(scope) {
            const formId = scope === 'national' ? '#nationalPaymentForm' : '#internationalPaymentForm';
            const form = $(formId);
            const selectedGateway = form.find('input[name="gateway"]:checked').val();
            const selectedGatewayId = form.find('input[name="gateway"]:checked').data('id');

            if (!selectedGateway) {
                showNotification('error', `Please select a ${scope} payment gateway`);
                return;
            }

            const selectedFields = $(`#${scope}-fields-${selectedGatewayId} .gateway-input`);
            let isValid = true;
            const credentials = {};

            selectedFields.each(function() {
                const name = $(this).attr('name');
                const value = $(this).val().trim();
                const fieldName = name.replace('credentials[', '').replace(']', '');

                if (!value) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                    credentials[fieldName] = value;
                }
            });

            if (!isValid) {
                showNotification('error', 'Please fill all required fields for the selected gateway');
                return;
            }

            const formData = new FormData();
            formData.append('_token', form.find('input[name="_token"]').val());
            formData.append('scope', form.find('input[name="scope"]').val());
            formData.append('gateway', selectedGateway);
            formData.append('credentials', JSON.stringify(credentials));

            const saveBtn = scope === 'national' ? $('#saveNationalConfig') : $('#saveInternationalConfig');
            const originalText = saveBtn.html();
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: '{{ route("payment.config.store") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    saveBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        showNotification('success', response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    saveBtn.prop('disabled', false).html(originalText);
                    let message = 'Error saving configuration';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        message = Object.values(errors).flat().join('<br>');
                    }
                    showNotification('error', message);
                }
            });
        }

        // Input validation
        $(document).on('input', '.gateway-input', function() {
            if ($(this).val().trim()) {
                $(this).removeClass('is-invalid');
            }
        });

        // Notification function
        function showNotification(type, message) {
            const alertClass = type === 'success' ? 'alert-success' :
                type === 'error' ? 'alert-danger' : 'alert-warning';
            const icon = type === 'success' ? 'fa-check-circle' :
                type === 'error' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle';

            $('.notification-alert').remove();

            const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show notification-alert" role="alert"
                 style="position: fixed; top: 15px; right: 15px; z-index: 9999; min-width: 250px; max-width: 350px; font-size: 0.85rem;">
                <div class="d-flex align-items-center">
                    <i class="fas ${icon} mr-2"></i>
                    <div class="flex-grow-1">${message}</div>
                    <button type="button" class="close ml-2" data-dismiss="alert" style="font-size: 1rem;">
                        <span>&times;</span>
                    </button>
                </div>
            </div>`;

            $('body').append(alertHtml);

            setTimeout(() => {
                $('.notification-alert').alert('close');
            }, 4000);
        }

        // Initialize radio button states on page load
        $('.national-gateway-radio:checked').trigger('change');
        $('.international-gateway-radio:checked').trigger('change');

        // Close modal on cancel button
        $('.modal .btn-secondary, .modal .close').on('click', function() {
            $(this).closest('.modal').modal('hide');
        });

        // Reset add form when modal is hidden
        $('#addGatewayModal').on('hidden.bs.modal', function () {
            $('#addGatewayForm')[0].reset();
            $('#addCredentialsContainer').html(`
                <div class="credential-field-row mb-3">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label class="small">Field Key *</label>
                                <input type="text"
                                       class="form-control credential-key"
                                       name="credential_keys[]"
                                       placeholder="e.g., api_key, secret_key"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label class="small">Field Value *</label>
                                <input type="text"
                                       class="form-control credential-value"
                                       name="credential_values[]"
                                       placeholder="Enter value"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-danger remove-add-credential-field"
                                    style="margin-bottom: 8px;" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `);
        });
    });
</script>