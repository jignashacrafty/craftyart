@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container seo-all-container">
    <div class="pd-ltr-10 xs-pd-20-10">
        <div class="min-height-200px">
            {{-- National Payment Section --}}
            <div class="card mb-3">
                <div class="card-header py-2">
                    <h6 class="mb-0">
                         National Payment Options (India)
                    </h6>
                    <small class="text-muted">Select one gateway for national payments</small>
                </div>
                <div class="card-body p-3">
                    <form id="nationalPaymentForm">
                        @csrf
                        <input type="hidden" name="scope" value="national">
                        <div class="row">
                            @foreach($nationalGateways as $gateway)
                            @php
                            $config = $configurations[$gateway->value] ?? null;
                            $isActive = $config && $config->is_active;
                            @endphp
                            <div class="col-md-6 mb-2">
                                <div class="card gateway-card h-100 {{ $isActive ? 'border-success' : 'border-light' }}">
                                    <div class="card-header py-1">
                                        <div class="custom-control custom-radio d-flex align-items-center">
                                            <input type="radio"
                                                   id="national-{{ $gateway->value }}"
                                                   name="gateway"
                                                   value="{{ $gateway->value }}"
                                                   class="custom-control-input national-gateway-radio"
                                                   {{ $isActive ? 'checked' : '' }}
                                            data-gateway="{{ $gateway->value }}">
                                            <label class="custom-control-label mb-0 ml-2" for="national-{{ $gateway->value }}">
                                                <strong class="big">{{ ucfirst($gateway->value) }}</strong>
                                            </label>
                                            @if($isActive)
                                            <span class="badge badge-success badge-pill ml-auto" style="font-size: 10px; padding: 2px 6px;">
                                                <i class="fas fa-check-circle"></i> Active
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="gateway-fields"
                                             id="national-fields-{{ $gateway->value }}"
                                             style="{{ !$isActive ? 'opacity: 0.6;' : '' }}">
                                            @php
                                            $fields = $gateway->fields();
                                            $credentials = $config->credentials ?? [];
                                            @endphp
                                            @foreach($fields as $field => $label)
                                            <div class="form-group mb-1">
                                                <label for="national-{{ $gateway->value }}-{{ $field }}" class="small mb-0">
                                                    {{ $label }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="text"
                                                       class="form-control form-control-sm gateway-input"
                                                       id="national-{{ $gateway->value }}-{{ $field }}"
                                                       name="credentials[{{ $gateway->value }}][{{ $field }}]"
                                                       value="{{ $credentials[$field] ?? '' }}"
                                                       placeholder="Enter {{ strtolower($label) }}"
                                                       {{ !$isActive ? 'disabled' : '' }}
                                                required>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-primary btn-sm" id="saveNationalConfig">
                                    <i class="fas fa-save mr-1"></i> Save National Configuration
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- International Payment Section --}}
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="mb-0">
                        International Payment Options
                    </h6>
                    <small class="text-muted">Select one gateway for international payments</small>
                </div>
                <div class="card-body p-3">
                    <form id="internationalPaymentForm">
                        @csrf
                        <input type="hidden" name="scope" value="international">
                        <div class="row">
                            @foreach($internationalGateways as $gateway)
                            @php
                            $config = $configurations[$gateway->value] ?? null;
                            $isActive = $config && $config->is_active;
                            @endphp
                            <div class="col-md-6 mb-2">
                                <div class="card gateway-card h-100 {{ $isActive ? 'border-success' : 'border-light' }}">
                                    <div class="card-header py-1">
                                        <div class="custom-control custom-radio d-flex align-items-center">
                                            <input type="radio"
                                                   id="international-{{ $gateway->value }}"
                                                   name="gateway"
                                                   value="{{ $gateway->value }}"
                                                   class="custom-control-input international-gateway-radio"
                                                   {{ $isActive ? 'checked' : '' }}
                                            data-gateway="{{ $gateway->value }}">
                                            <label class="custom-control-label mb-0 ml-2" for="international-{{ $gateway->value }}">
                                                <strong class="small">{{ ucfirst($gateway->value) }}</strong>
                                            </label>
                                            @if($isActive)
                                            <span class="badge badge-success badge-pill ml-auto" style="font-size: 10px; padding: 2px 6px;">
                                                <i class="fas fa-check-circle"></i> Active
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="gateway-fields"
                                             id="international-fields-{{ $gateway->value }}"
                                             style="{{ !$isActive ? 'opacity: 0.6;' : '' }}">
                                            @php
                                            $fields = $gateway->fields();
                                            $credentials = $config->credentials ?? [];
                                            @endphp
                                            @foreach($fields as $field => $label)
                                            <div class="form-group mb-1">
                                                <label for="international-{{ $gateway->value }}-{{ $field }}" class="small mb-0">
                                                    {{ $label }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="text"
                                                       class="form-control form-control-sm gateway-input"
                                                       id="international-{{ $gateway->value }}-{{ $field }}"
                                                       name="credentials[{{ $gateway->value }}][{{ $field }}]"
                                                       value="{{ $credentials[$field] ?? '' }}"
                                                       placeholder="Enter {{ strtolower($label) }}"
                                                       {{ !$isActive ? 'disabled' : '' }}
                                                required>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-primary btn-sm" id="saveInternationalConfig">
                                    <i class="fas fa-save mr-1"></i> Save International Configuration
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<style>
    .gateway-card {
        transition: all 0.2s ease;
        font-size: 0.85rem;
    }
    .gateway-card.border-success {
        border-color: #28a745 !important;
        box-shadow: 0 0 0 1px rgba(40, 167, 69, 0.05);
    }
    .gateway-card .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 6px 10px;
    }
    .gateway-card .card-body {
        padding: 8px;
    }
    .gateway-card:hover {
        box-shadow: 0 1px 5px rgba(0,0,0,0.05);
    }
    .custom-control-label {
        font-size: 0.85rem;
    }
    .form-group {
        margin-bottom: 8px;
    }
    label {
        font-size: 0.8rem;
        font-weight: 500;
    }
    .form-control-sm {
        height: calc(1.5em + 0.5rem + 2px);
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    .badge-pill {
        font-size: 0.7rem;
        padding: 2px 6px;
    }
    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.8rem;
    }
    .card-header h6 {
        font-size: 0.95rem;
    }
    .card-header small {
        font-size: 0.75rem;
    }
</style>
<script>
    $(document).ready(function() {
        $('.national-gateway-radio').on('change', function() {
            const selectedGateway = $(this).val();
            $('[id^="national-fields-"]').each(function() {
                const gatewayName = $(this).attr('id').replace('national-fields-', '');
                const isSelected = gatewayName === selectedGateway;
                $(this).closest('.gateway-card')
                    .toggleClass('border-success', isSelected)
                    .toggleClass('border-light', !isSelected);
                const badgeSpan = $(this).closest('.gateway-card').find('.badge');
                if (isSelected) {
                    badgeSpan.removeClass('d-none');
                } else {
                    badgeSpan.addClass('d-none');
                }
                $(this).find('.gateway-input').prop('disabled', !isSelected);
                $(this).css('opacity', isSelected ? '1' : '0.6');
            });
        });

        $('.international-gateway-radio').on('change', function() {
            const selectedGateway = $(this).val();
            $('[id^="international-fields-"]').each(function() {
                const gatewayName = $(this).attr('id').replace('international-fields-', '');
                const isSelected = gatewayName === selectedGateway;
                $(this).closest('.gateway-card')
                    .toggleClass('border-success', isSelected)
                    .toggleClass('border-light', !isSelected);
                const badgeSpan = $(this).closest('.gateway-card').find('.badge');
                if (isSelected) {
                    badgeSpan.removeClass('d-none');
                } else {
                    badgeSpan.addClass('d-none');
                }
                $(this).find('.gateway-input').prop('disabled', !isSelected);
                $(this).css('opacity', isSelected ? '1' : '0.6');
            });
        });

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

            if (!selectedGateway) {
                showNotification('error', `Please select a ${scope} payment gateway`);
                return;
            }

            let isValid = true;
            const selectedFields = $(`#${scope}-fields-${selectedGateway} .gateway-input`);
            selectedFields.each(function() {
                if (!$(this).val().trim()) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                showNotification('error', 'Please fill all required fields for the selected gateway');
                return;
            }

            const credentials = {};
            selectedFields.each(function() {
                const name = $(this).attr('name');
                const match = name.match(/credentials\[([^\]]+)\]\[([^\]]+)\]/);
                if (match) {
                    const fieldName = match[2];
                    credentials[fieldName] = $(this).val().trim();
                }
            });

            const formData = new FormData();
            formData.append('_token', form.find('input[name="_token"]').val());
            formData.append('scope', scope);
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
                        const otherCards = $(`#${scope}PaymentForm .gateway-card`);
                        otherCards.removeClass('border-success').addClass('border-light');
                        otherCards.find('.badge').addClass('d-none');
                        otherCards.find('.gateway-input').prop('disabled', true);
                        otherCards.find('.gateway-fields').css('opacity', '0.6');
                        const selectedCard = $(`#${scope}PaymentForm input[value="${selectedGateway}"]`).closest('.gateway-card');
                        selectedCard.addClass('border-success').removeClass('border-light');
                        selectedCard.find('.badge').removeClass('d-none');
                        selectedCard.find('.gateway-input').prop('disabled', false);
                        selectedCard.find('.gateway-fields').css('opacity', '1');
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

        function showNotification(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            $('.notification-alert').remove();
            const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show notification-alert" role="alert"
                 style="position: fixed; top: 15px; right: 15px; z-index: 9999; min-width: 250px; font-size: 0.85rem;">
                <i class="fas ${icon} mr-1"></i> ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>`;
            $('body').append(alertHtml);
            setTimeout(() => {
                $('.notification-alert').alert('close');
            }, 4000);
        }

        $('.gateway-input').on('input', function() {
            if ($(this).val().trim()) {
                $(this).removeClass('is-invalid');
            }
        });
    });
</script>