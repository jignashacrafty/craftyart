@include('layouts.masterhead')

<style>
    .nav-tabs .nav-link {
        border: 3px solid transparent;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
    }
</style>

<div class="main-container">
    <div class="min-height-200px">
        <div class="card-box p-3">

            {{-- Tabs --}}
            <ul class="nav nav-tabs" id="automationConfigTabs" role="tablist" style="margin-bottom: 50px;">
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#emailConfig">Email Offer Purchase Automation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#emailCheckoutDropUser">Email Campaign for Checkout Drop
                        User</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#whatsappConfig">Whatsapp Offer Purchase Automation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#whatsappCheckoutDropUser">Whatsapp Campaign for
                        Checkout Drop User</a>
                </li>
            </ul>

            <div class="tab-content mt-3">
                <div class="tab-pane fade" id="emailConfig">
                    @include('automation_config.partials.email')
                </div>
                <div class="tab-pane fade" id="emailCheckoutDropUser">
                    @include('automation_config.partials.email_checkout_drop')
                </div>
                <div class="tab-pane fade" id="whatsappConfig">
                    @include('automation_config.partials.whatsapp')
                </div>
                <div class="tab-pane fade show active" id="whatsappCheckoutDropUser">
                    @include('automation_config.partials.whatsapp_checkout_drop')
                </div>
            </div>

        </div>
    </div>
</div>

{{-- pass server data into JS safely --}}
<script>
    let existingEmailFrequencies = @json($configs['email_checkout_drop_automation']['frequencies'] ?? []);
    let existingWhatsappFrequencies = @json($configs['whatsapp_checkout_drop_automation'] ?? []);
    let templateMap = @json($emailTemplates->pluck('name', 'id'));
    let whatsappMap = @json($whatsappTemplates->pluck('campaign_name', 'id'));
    let promoMap = @json($promoCodes->pluck('promo_code', 'id'));
</script>

@include('layouts.masterscript')

<script>
    $(document).ready(function() {

        // ---------------- EMAIL CONFIG ----------------
        $('#emailConfigForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let tplId = form.find('#email_template_id').val();
            let subject = form.find('#subject').val().trim();

            if (!tplId) {
                alert("Please select a template before saving.");
                return;
            }
            if (!subject) {
                alert("Please Enter Email Subject");
                return;
            }

            let data = {
                subject: subject,
                template: tplId
            };
            setConfigData(form.find('#config_name').val(), data);
        });

        // ---------------- FREQUENCIES (Email Checkout Drop) ----------------
        let emailFrequencies = [];

        if (Array.isArray(existingEmailFrequencies) && existingEmailFrequencies.length > 0) {
            emailFrequencies = existingEmailFrequencies.map(f => ({
                days: parseInt(f.days, 10) || 0,
                subject: f.subject || '',
                template: f.template ?? '',
                promo_code: f.promo_code ?? ''
            }));
            renderEmailFrequencyList();
        }

        $("#addFrequencyBtn").on("click", function() {
            $("#editIndexEmail").val("");
            $("#freq_days").val("");
            $("#freq_subject").val("");
            $("#freq_template").val("");
            $("#freq_promo_code").val("");
            $("#saveFrequencyBtn").text("Add");
            $("#emailFrequencyModal").modal("show");
        });

        $("#saveFrequencyBtn").on("click", function() {
            let days = $("#freq_days").val().trim();
            let subject = $("#freq_subject").val().trim();
            let template = $("#freq_template").val();
            let promo_code = $("#freq_promo_code").val();

            if (!days || !subject || !template || !promo_code) {
                alert("Please fill all fields");
                return;
            }

            let obj = {
                days: parseInt(days, 10),
                subject,
                template,
                promo_code
            };
            let index = $("#editIndexEmail").val();

            if (index === "") {
                emailFrequencies.push(obj);
            } else {
                emailFrequencies[index] = obj;
            }

            renderEmailFrequencyList();
            $("#emailFrequencyModal").modal("hide");
        });

        function renderEmailFrequencyList() {
            if (emailFrequencies.length === 0) {
                $("#frequencyList").html("<p>No frequencies added yet.</p>");
                return;
            }

            let html =
                `<table class="table table-bordered">
                <thead><tr><th>Days</th><th>Subject</th><th>Template</th><th>Promo Code</th><th>Actions</th></tr></thead><tbody>`;

            emailFrequencies.forEach((f, i) => {
                html += `<tr>
                    <td>${f.days}</td>
                    <td>${f.subject}</td>
                    <td>${templateMap[f.template] ?? f.template}</td>
                    <td>${promoMap[f.promo_code] ?? f.promo_code}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-warning mr-1" onclick="editEmailFrequency(${i})">Edit</button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteEmailFrequency(${i})">Delete</button>
                    </td>
                </tr>`;
            });

            html += "</tbody></table>";
            $("#frequencyList").html(html);
        }

        window.editEmailFrequency = function(i) {
            let f = emailFrequencies[i];
            $("#editIndexEmail").val(i);
            $("#freq_days").val(f.days);
            $("#freq_subject").val(f.subject);
            $("#freq_template").val(f.template);
            $("#freq_promo_code").val(f.promo_code);
            $("#saveFrequencyBtn").text("Update");
            $("#emailFrequencyModal").modal("show");
        };

        window.deleteEmailFrequency = function(i) {
            if (confirm("Delete this frequency?")) {
                emailFrequencies.splice(i, 1);
                renderEmailFrequencyList();
            }
        };

        $("#emailCheckoutDropForm").on("submit", function(e) {
            e.preventDefault();
            let form = $(this);
            let subject = form.find('#subject').val().trim();
            let template = form.find('#email_template_id').val();
            let promo_code = form.find('#main_promo_code').val();

            if (!template || !subject || !promo_code) {
                alert("Please fill main fields properly");
                return;
            }
            if (emailFrequencies.length === 0) {
                alert("Please add at least one frequency");
                return;
            }

            let data = {
                subject,
                template,
                promo_code,
                frequencies: emailFrequencies
            };
            setConfigData(form.find('#config_name').val(), data);
        });

        // ---------------- WHATSAPP CHECKOUT DROP ----------------
        let whatsappFrequencies = [];

        // Load existing frequencies
        if (Array.isArray(existingWhatsappFrequencies) && existingWhatsappFrequencies.length > 0) {
            whatsappFrequencies = existingWhatsappFrequencies.map(f => ({
                days: parseInt(f.days, 10) || 0,
                drop_from_offer: {
                    template: f.drop_from_offer?.template ?? ''
                },
                drop_from_subscription: {
                    template: f.drop_from_subscription?.template ?? '',
                    promocode: f.drop_from_subscription?.promocode ?? ''
                },
                drop_from_templates: {
                    template: f.drop_from_templates?.template ?? '',
                    promocode: f.drop_from_templates?.promocode ?? ''
                }
            }));
            renderWhatsappFrequencyList();
        }

        // Add button click
        $("#addWhatsappFrequencyBtn").on("click", function() {
            $("#editIndexWhatsapp").val("");
            $("#freq_days_wp").val("");
            $("#freq_drop_offer_template").val("");
            $("#freq_drop_subscription_template").val("");
            $("#freq_drop_subscription_promo_code").val("");
            $("#freq_drop_templates_template").val("");
            $("#freq_drop_templates_promo_code").val("");
            $("#saveWhatsappFrequencyBtn").text("Add");
            $("#whatsappFrequencyModal").modal("show");
        });

        // Save frequency
        $("#saveWhatsappFrequencyBtn").on("click", function() {
            let days = $("#freq_days_wp").val().trim();
            if (!days) {
                alert("Please enter frequency days");
                return;
            }

            let obj = {
                days: parseInt(days, 10),
                drop_from_offer: {
                    template: $("#freq_drop_offer_template").val()
                },
                drop_from_subscription: {
                    template: $("#freq_drop_subscription_template").val(),
                    promocode: $("#freq_drop_subscription_promo_code").val()
                },
                drop_from_templates: {
                    template: $("#freq_drop_templates_template").val(),
                    promocode: $("#freq_drop_templates_promo_code").val()
                }
            };

            let index = $("#editIndexWhatsapp").val();
            if (index === "") whatsappFrequencies.push(obj);
            else whatsappFrequencies[index] = obj;

            renderWhatsappFrequencyList();
            $("#whatsappFrequencyModal").modal("hide");
        });

        // Render frequency list
        function renderWhatsappFrequencyList() {
            if (whatsappFrequencies.length === 0) {
                $("#whatsappFrequencyList").html("<p>No campaigns added yet.</p>");
                return;
            }

            let html = `<table class="table table-bordered">
            <thead><tr>
                <th>Days</th>
                <th>Drop From Offer</th>
                <th>Drop From Subscription</th>
                <th>Drop From Templates</th>
                <th>Actions</th>
            </tr></thead><tbody>`;

            whatsappFrequencies.forEach((f, i) => {
                html += `<tr>
                <td>${f.days}</td>
                <td>${whatsappMap[f.drop_from_offer.template] ?? '-'}</td>
                <td>${whatsappMap[f.drop_from_subscription.template] ?? '-'} (${promoMap[f.drop_from_subscription.promocode] ?? '-'})</td>
                <td>${whatsappMap[f.drop_from_templates.template] ?? '-'} (${promoMap[f.drop_from_templates.promocode] ?? '-'})</td>
                <td>
                    <button type="button" class="btn btn-sm btn-warning mr-1" onclick="editWhatsappFrequency(${i})">Edit</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteWhatsappFrequency(${i})">Delete</button>
                </td>
            </tr>`;
            });

            html += "</tbody></table>";
            $("#whatsappFrequencyList").html(html);
        }

        // Edit frequency
        window.editWhatsappFrequency = function(i) {
            let f = whatsappFrequencies[i];
            $("#editIndexWhatsapp").val(i);
            $("#freq_days_wp").val(f.days);
            $("#freq_drop_offer_template").val(f.drop_from_offer.template);
            $("#freq_drop_subscription_template").val(f.drop_from_subscription.template);
            $("#freq_drop_subscription_promo_code").val(f.drop_from_subscription.promocode);
            $("#freq_drop_templates_template").val(f.drop_from_templates.template);
            $("#freq_drop_templates_promo_code").val(f.drop_from_templates.promocode);
            $("#saveWhatsappFrequencyBtn").text("Update");
            $("#whatsappFrequencyModal").modal("show");
        };

        // Delete frequency
        window.deleteWhatsappFrequency = function(i) {
            if (confirm("Delete this campaign?")) {
                whatsappFrequencies.splice(i, 1);
                renderWhatsappFrequencyList();
            }
        };

        // Submit form
        $("#whatsappCheckoutDropForm").on("submit", function(e) {
            e.preventDefault();
            if (whatsappFrequencies.length === 0) {
                alert("Please add at least one campaign");
                return;
            }

            let data = whatsappFrequencies;

            $.ajax({
                url: "{{ route('automation_config.store') }}",
                type: "POST",
                data: {
                    _token: $('input[name="_token"]').val(),
                    name: "whatsapp_checkout_drop_automation",
                    data: data
                },
                success: function(response) {
                    alert(response.message || "Automation config saved successfully!");
                    location.reload();
                },
                error: function(xhr) {
                    alert("Error while saving config");
                }
            });
        });

        // ---------------- AJAX Save Function ----------------
        function setConfigData(name, data) {
            $.ajax({
                url: "{{ route('automation_config.store') }}",
                type: "POST",
                data: {
                    _token: $('input[name="_token"]').val(),
                    name: name,
                    data: data
                },
                success: function(response) {
                    console.log("Saved:", response);
                    alert(response.message || "Automation config saved successfully!");
                    location.reload();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert("Error while saving config");
                }
            });
        }

        // ---------------- Template Preview ----------------
        $(document).on('change', '.email_template_id, .whatsapp_template_id', function() {
            let btn = $(this).closest('.d-flex').find('.preview_template_btn');
            btn.prop('disabled', $(this).val() === '');
        });

        $(document).on('click', '.preview_template_btn', function() {
            let tplId = $(this).closest('.d-flex').find('select').val();
            if (!tplId) tplId = $(this).closest('form').find('select').val();
            if (tplId) {
                let url = "{{ url('email-template/preview') }}/" + tplId;
                window.open(url, '_blank');
            } else {
                alert('Please select a template first.');
            }
        });

    });
</script>

