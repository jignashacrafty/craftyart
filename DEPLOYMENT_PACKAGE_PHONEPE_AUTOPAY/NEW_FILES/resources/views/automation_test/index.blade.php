@include('layouts.masterhead')

<style>
    .test-card {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        background: #fff;
    }

    .test-card.active {
        border-color: #007bff;
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.2);
    }

    .test-result {
        margin-top: 20px;
        padding: 15px;
        border-radius: 5px;
        display: none;
    }

    .test-result.success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    .test-result.error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .test-result.info {
        background: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
    }

    .btn-test {
        min-width: 150px;
    }

    .loading-spinner {
        display: none;
        margin-left: 10px;
    }

    .loading-spinner.active {
        display: inline-block;
    }

    .form-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }

    .quick-fill-btn {
        margin-top: 10px;
    }

    .template-info {
        background: #e7f3ff;
        padding: 10px;
        border-radius: 5px;
        margin-top: 10px;
        display: none;
    }

    .template-info.show {
        display: block;
    }

    .param-badge {
        display: inline-block;
        background: #007bff;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        margin: 2px;
        font-size: 12px;
    }
</style>

<div class="main-container">
    <div class="min-height-200px">
        <div class="page-header">
            <div class="row">
                <div class="col-md-12">
                    <div class="title">
                        <h4>🧪 Automation Testing Center</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Automation Test</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Quick Fill Section -->
        <div class="card-box mb-30">
            <div class="pd-20">
                <h5 class="text-primary mb-3">⚡ Quick Fill Test Data</h5>
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-info btn-sm quick-fill-btn" onclick="quickFillTestData()">
                            📱 Fill Test Data (9724085965)
                        </button>
                        <small class="text-muted ml-3">Automatically fills all forms with test data</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Tabs -->
        <ul class="nav nav-tabs" id="testTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#emailTest">📧 Email Test</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#whatsappTest">💬 WhatsApp Test</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#bothTest">🚀 Combined Test</a>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Email Test Tab -->
            <div class="tab-pane fade show active" id="emailTest">
                <div class="test-card">
                    <h5 class="mb-3">📧 Test Email Sending</h5>
                    <form id="emailTestForm">
                        @csrf
                        <div class="form-section">
                            <h6>Recipient Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email Address *</label>
                                        <input type="email" class="form-control" name="email" id="email_test" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Name *</label>
                                        <input type="text" class="form-control" name="name" id="name_email" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6>Email Configuration</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email Template *</label>
                                        <select class="form-control" name="template_id" id="email_template_test" required>
                                            <option value="">Select Template</option>
                                            @foreach($emailTemplates as $template)
                                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Subject *</label>
                                        <input type="text" class="form-control" name="subject" id="subject_test" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6>Optional Data</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Plan (Optional)</label>
                                        <select class="form-control" name="plan_id" id="plan_email">
                                            <option value="">No Plan</option>
                                            @foreach($plans as $plan)
                                                <option value="{{ $plan->id }}">{{ $plan->package_name }} - ₹{{ $plan->price }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Promo Code (Optional)</label>
                                        <select class="form-control" name="promo_code_id" id="promo_email">
                                            <option value="">No Promo</option>
                                            @foreach($promoCodes as $promo)
                                                <option value="{{ $promo->id }}">{{ $promo->promo_code }} ({{ $promo->disc }}% OFF)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-test">
                            📤 Send Test Email
                            <span class="spinner-border spinner-border-sm loading-spinner" role="status"></span>
                        </button>
                    </form>

                    <div class="test-result" id="emailResult"></div>
                </div>
            </div>

            <!-- WhatsApp Test Tab -->
            <div class="tab-pane fade" id="whatsappTest">
                <div class="test-card">
                    <h5 class="mb-3">💬 Test WhatsApp Sending</h5>
                    <form id="whatsappTestForm">
                        @csrf
                        <div class="form-section">
                            <h6>Recipient Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone Number *</label>
                                        <input type="text" class="form-control" name="phone" id="phone_test" 
                                               placeholder="919724085965" required>
                                        <small class="text-muted">Format: Country code + number (e.g., 919724085965)</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Name *</label>
                                        <input type="text" class="form-control" name="name" id="name_whatsapp" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6>WhatsApp Configuration</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>WhatsApp Template *</label>
                                        <select class="form-control" name="template_id" id="whatsapp_template_test" required>
                                            <option value="">Select Template</option>
                                            @foreach($whatsappTemplates as $template)
                                                <option value="{{ $template->id }}" 
                                                        data-params="{{ $template->template_params_count }}"
                                                        data-params-list="{{ json_encode($template->template_params) }}">
                                                    {{ $template->campaign_name }} ({{ $template->template_params_count }} params)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="template-info" id="whatsapp_template_info">
                                        <strong>Template Parameters:</strong>
                                        <div id="whatsapp_params_display"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6>Optional Data</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Plan (Optional)</label>
                                        <select class="form-control" name="plan_id" id="plan_whatsapp">
                                            <option value="">No Plan</option>
                                            @foreach($plans as $plan)
                                                <option value="{{ $plan->id }}">{{ $plan->package_name }} - ₹{{ $plan->price }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Promo Code (Optional)</label>
                                        <select class="form-control" name="promo_code_id" id="promo_whatsapp">
                                            <option value="">No Promo</option>
                                            @foreach($promoCodes as $promo)
                                                <option value="{{ $promo->id }}">{{ $promo->promo_code }} ({{ $promo->disc }}% OFF)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-test">
                            📤 Send Test WhatsApp
                            <span class="spinner-border spinner-border-sm loading-spinner" role="status"></span>
                        </button>
                    </form>

                    <div class="test-result" id="whatsappResult"></div>
                </div>
            </div>

            <!-- Combined Test Tab -->
            <div class="tab-pane fade" id="bothTest">
                <div class="test-card">
                    <h5 class="mb-3">🚀 Test Both Email & WhatsApp</h5>
                    <form id="bothTestForm">
                        @csrf
                        <div class="form-section">
                            <h6>Recipient Information</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Email Address *</label>
                                        <input type="email" class="form-control" name="email" id="email_both" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Phone Number *</label>
                                        <input type="text" class="form-control" name="phone" id="phone_both" 
                                               placeholder="919724085965" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Name *</label>
                                        <input type="text" class="form-control" name="name" id="name_both" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6>Email Configuration</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email Template *</label>
                                        <select class="form-control" name="email_template_id" id="email_template_both" required>
                                            <option value="">Select Template</option>
                                            @foreach($emailTemplates as $template)
                                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email Subject *</label>
                                        <input type="text" class="form-control" name="email_subject" id="subject_both" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6>WhatsApp Configuration</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>WhatsApp Template *</label>
                                        <select class="form-control" name="whatsapp_template_id" id="whatsapp_template_both" required>
                                            <option value="">Select Template</option>
                                            @foreach($whatsappTemplates as $template)
                                                <option value="{{ $template->id }}">
                                                    {{ $template->campaign_name }} ({{ $template->template_params_count }} params)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6>Optional Data</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Plan (Optional)</label>
                                        <select class="form-control" name="plan_id" id="plan_both">
                                            <option value="">No Plan</option>
                                            @foreach($plans as $plan)
                                                <option value="{{ $plan->id }}">{{ $plan->package_name }} - ₹{{ $plan->price }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Promo Code (Optional)</label>
                                        <select class="form-control" name="promo_code_id" id="promo_both">
                                            <option value="">No Promo</option>
                                            @foreach($promoCodes as $promo)
                                                <option value="{{ $promo->id }}">{{ $promo->promo_code }} ({{ $promo->disc }}% OFF)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-info btn-test">
                            🚀 Send Both Messages
                            <span class="spinner-border spinner-border-sm loading-spinner" role="status"></span>
                        </button>
                    </form>

                    <div class="test-result" id="bothResult"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')

<script>
$(document).ready(function() {
    // Show template parameters when WhatsApp template is selected
    $('#whatsapp_template_test').on('change', function() {
        const selected = $(this).find(':selected');
        const paramsList = selected.data('params-list');
        
        if (paramsList && paramsList.length > 0) {
            let html = '';
            paramsList.forEach(param => {
                html += `<span class="param-badge">${param}</span>`;
            });
            $('#whatsapp_params_display').html(html);
            $('#whatsapp_template_info').addClass('show');
        } else {
            $('#whatsapp_template_info').removeClass('show');
        }
    });

    // Email Test Form
    $('#emailTestForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        const spinner = btn.find('.loading-spinner');
        const resultDiv = $('#emailResult');

        btn.prop('disabled', true);
        spinner.addClass('active');
        resultDiv.hide();

        $.ajax({
            url: '{{ route("automation.test.email") }}',
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                showResult(resultDiv, 'success', response.message, response.details);
            },
            error: function(xhr) {
                const error = xhr.responseJSON || {message: 'Unknown error occurred'};
                showResult(resultDiv, 'error', error.message, error);
            },
            complete: function() {
                btn.prop('disabled', false);
                spinner.removeClass('active');
            }
        });
    });

    // WhatsApp Test Form
    $('#whatsappTestForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        const spinner = btn.find('.loading-spinner');
        const resultDiv = $('#whatsappResult');

        btn.prop('disabled', true);
        spinner.addClass('active');
        resultDiv.hide();

        $.ajax({
            url: '{{ route("automation.test.whatsapp") }}',
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                showResult(resultDiv, 'success', response.message, response.details);
            },
            error: function(xhr) {
                const error = xhr.responseJSON || {message: 'Unknown error occurred'};
                showResult(resultDiv, 'error', error.message, error);
            },
            complete: function() {
                btn.prop('disabled', false);
                spinner.removeClass('active');
            }
        });
    });

    // Both Test Form
    $('#bothTestForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        const spinner = btn.find('.loading-spinner');
        const resultDiv = $('#bothResult');

        btn.prop('disabled', true);
        spinner.addClass('active');
        resultDiv.hide();

        $.ajax({
            url: '{{ route("automation.test.both") }}',
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                let detailsHtml = '<div class="mt-2">';
                if (response.results.email) {
                    detailsHtml += `<div class="mb-2"><strong>📧 Email:</strong> ${response.results.email.message}</div>`;
                }
                if (response.results.whatsapp) {
                    detailsHtml += `<div><strong>💬 WhatsApp:</strong> ${response.results.whatsapp.message}</div>`;
                }
                detailsHtml += '</div>';
                
                showResult(resultDiv, response.success ? 'success' : 'error', response.message, detailsHtml);
            },
            error: function(xhr) {
                const error = xhr.responseJSON || {message: 'Unknown error occurred'};
                showResult(resultDiv, 'error', error.message, error);
            },
            complete: function() {
                btn.prop('disabled', false);
                spinner.removeClass('active');
            }
        });
    });

    function showResult(div, type, message, details) {
        let html = `<strong>${message}</strong>`;
        
        if (details) {
            if (typeof details === 'string') {
                html += details;
            } else {
                html += '<div class="mt-2"><pre style="background:#f5f5f5;padding:10px;border-radius:5px;max-height:300px;overflow:auto;">' + 
                        JSON.stringify(details, null, 2) + '</pre></div>';
            }
        }
        
        div.removeClass('success error info').addClass(type).html(html).fadeIn();
    }
});

// Quick fill function
function quickFillTestData() {
    // Email test
    $('#email_test').val('test@craftyartapp.com');
    $('#name_email').val('Test User');
    $('#subject_test').val('Test Email from Automation System');
    
    // WhatsApp test
    $('#phone_test').val('919724085965');
    $('#name_whatsapp').val('Test User');
    
    // Both test
    $('#email_both').val('test@craftyartapp.com');
    $('#phone_both').val('919724085965');
    $('#name_both').val('Test User');
    $('#subject_both').val('Test Email from Automation System');
    
    // Select first available options
    if ($('#email_template_test option').length > 1) {
        $('#email_template_test').val($('#email_template_test option:eq(1)').val());
        $('#email_template_both').val($('#email_template_both option:eq(1)').val());
    }
    
    if ($('#whatsapp_template_test option').length > 1) {
        $('#whatsapp_template_test').val($('#whatsapp_template_test option:eq(1)').val()).trigger('change');
        $('#whatsapp_template_both').val($('#whatsapp_template_both option:eq(1)').val());
    }
    
    if ($('#plan_email option').length > 1) {
        $('#plan_email').val($('#plan_email option:eq(1)').val());
        $('#plan_whatsapp').val($('#plan_whatsapp option:eq(1)').val());
        $('#plan_both').val($('#plan_both option:eq(1)').val());
    }
    
    alert('✅ Test data filled! You can now test the automation.');
}
</script>

