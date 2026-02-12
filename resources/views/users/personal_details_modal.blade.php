<!-- Personal Details Modal -->
<div class="modal fade" id="personalDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title"><i class="fa fa-user-circle"></i> Personal Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="personalDetailsForm">
                <div class="modal-body" style="padding: 30px;">
                    <!-- Debug Info -->
                    <div id="debugInfo" style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 12px; display: none;">
                        <strong>Debug Info:</strong>
                        <div id="debugContent"></div>
                    </div>
                    
                    <div id="personalDetailsLoading" style="text-align: center; padding: 40px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p style="margin-top: 15px; color: #6c757d;">Loading personal details...</p>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="$('#debugInfo').toggle()">Toggle Debug</button>
                    </div>
                    
                    <div id="personalDetailsContent" style="display: none;">
                        <input type="hidden" id="user_uid" name="uid">
                        
                        <!-- Data Completeness Indicator -->
                        <div class="alert alert-light" id="completenessIndicator" style="border-radius: 10px; border-left: 4px solid #667eea; display: none;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong><i class="fa fa-chart-pie"></i> Profile Completeness:</strong>
                                    <span id="completenessText" style="margin-left: 10px;"></span>
                                </div>
                                <div>
                                    <span id="completenessPercentage" style="font-size: 24px; font-weight: 700; color: #667eea;"></span>
                                </div>
                            </div>
                            <div class="progress" style="height: 8px; margin-top: 10px; border-radius: 10px;">
                                <div id="completenessBar" class="progress-bar" role="progressbar" style="width: 0%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>
                            </div>
                        </div>
                        
                        <!-- Usage Type Badge -->
                        <div class="alert alert-info" id="usageTypeBadge" style="display: none; border-radius: 10px;">
                            <strong><i class="fa fa-tag"></i> Usage Type:</strong> <span id="usageTypeText"></span>
                        </div>
                        
                        <!-- Personal Information Section -->
                        <div class="card mb-3" style="border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 10px;">
                            <div class="card-header" style="background: #f8f9fa; border-radius: 10px 10px 0 0;">
                                <h6 class="mb-0"><i class="fa fa-user"></i> Personal Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" class="form-control" name="user_name" id="user_name" placeholder="Enter name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Language</label>
                                            <input type="text" class="form-control" name="language" id="language" placeholder="e.g., English, Hindi, Gujarati">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Bio</label>
                                    <textarea class="form-control" name="bio" id="bio" rows="3" placeholder="Tell us about yourself..."></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Location Section -->
                        <div class="card mb-3" style="border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 10px;">
                            <div class="card-header" style="background: #f8f9fa; border-radius: 10px 10px 0 0;">
                                <h6 class="mb-0"><i class="fa fa-map-marker"></i> Location</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Country</label>
                                            <input type="text" class="form-control" name="country" id="country" placeholder="e.g., India">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>State</label>
                                            <input type="text" class="form-control" name="state" id="state" placeholder="e.g., Gujarat">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>City</label>
                                            <input type="text" class="form-control" name="city" id="city" placeholder="e.g., Surat">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea class="form-control" name="address" id="address" rows="2" placeholder="Enter full address"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Professional Details Section -->
                        <div class="card mb-3" style="border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 10px;">
                            <div class="card-header" style="background: #f8f9fa; border-radius: 10px 10px 0 0;">
                                <h6 class="mb-0"><i class="fa fa-briefcase"></i> Professional Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Website</label>
                                            <input type="url" class="form-control" name="website" id="website" placeholder="https://example.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Role/Designation</label>
                                            <input type="text" class="form-control" name="role" id="role" placeholder="e.g., Designer, Developer, Business Owner">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Interest</label>
                                            <input type="text" class="form-control" name="interest" id="interest" placeholder="e.g., Graphic Design, Marketing">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Purpose</label>
                                            <input type="text" class="form-control" name="purpose" id="purpose" placeholder="e.g., Business, Personal Projects">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Usage</label>
                                            <select class="form-control" name="usage" id="usage">
                                                <option value="">Select Usage Type</option>
                                                <option value="personal">Personal Use</option>
                                                <option value="professional">Professional Use</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Reference</label>
                                            <input type="text" class="form-control" name="reference" id="reference" placeholder="How did you hear about us?">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 2px solid #e9ecef;">
                    <button type="button" id="closePersonalDetailsBtn" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal-content {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.form-control.has-value {
    background-color: #f0f9ff;
    border-left: 3px solid #28a745;
}

.form-control:not(.has-value):not(:focus) {
    background-color: #fff;
}

.card-header h6 {
    color: #495057;
    font-weight: 600;
}

.empty-field-hint {
    font-size: 11px;
    color: #6c757d;
    font-style: italic;
    margin-top: 3px;
}
</style>

<script>
function showPersonalDetails(uid) {
    console.log('=== PERSONAL DETAILS DEBUG START ===');
    console.log('Opening personal details for UID:', uid);
    console.log('jQuery available:', typeof $ !== 'undefined');
    console.log('Modal element exists:', $('#personalDetailsModal').length > 0);
    
    $('#personalDetailsModal').modal('show');
    $('#personalDetailsLoading').show();
    $('#personalDetailsContent').hide();
    $('#user_uid').val(uid);
    
    var url = '{{ url("/user/personal-details") }}/' + uid;
    console.log('Fetching from URL:', url);
    console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
    
    // Fetch personal details
    $.ajax({
        url: url,
        method: 'GET',
        timeout: 10000, // 10 second timeout
        beforeSend: function() {
            console.log('AJAX request starting...');
            $('#debugContent').html('Request started to: ' + url);
            $('#debugInfo').show();
        },
        success: function(response) {
            console.log('=== AJAX SUCCESS ===');
            console.log('Full Response:', response);
            console.log('Response Type:', typeof response);
            console.log('Success Flag:', response.success);
            
            $('#debugContent').html(
                'Status: SUCCESS<br>' +
                'User Found: ' + (response.user ? 'Yes' : 'No') + '<br>' +
                'Personal Details: ' + (response.personal_details ? 'Found' : 'Not Set') + '<br>' +
                'Brand Kit: ' + (response.brand_kit ? 'Found' : 'Not Set') + '<br>' +
                'Usage Type: ' + (response.usage_type || 'Not Set')
            );
            
            if (response.success) {
                console.log('User Data:', response.user);
                console.log('Personal Details:', response.personal_details);
                console.log('Brand Kit:', response.brand_kit);
                console.log('Usage Type:', response.usage_type);
                
                // Clear all fields first
                console.log('Clearing all fields...');
                $('#user_name').val('');
                $('#bio').val('');
                $('#country').val('');
                $('#state').val('');
                $('#city').val('');
                $('#address').val('');
                $('#interest').val('');
                $('#purpose').val('');
                $('#usage').val('');
                $('#reference').val('');
                $('#language').val('');
                $('#website').val('');
                $('#role').val('');
                
                // Populate user name from user data
                if (response.user && response.user.name) {
                    console.log('Setting user name:', response.user.name);
                    $('#user_name').val(response.user.name);
                }
                
                // Populate personal details ONLY if they exist and are not null/empty
                if (response.personal_details) {
                    console.log('Populating personal details...');
                    if (response.personal_details.bio) {
                        console.log('Setting bio');
                        $('#bio').val(response.personal_details.bio);
                    }
                    if (response.personal_details.country) {
                        console.log('Setting country');
                        $('#country').val(response.personal_details.country);
                    }
                    if (response.personal_details.state) {
                        console.log('Setting state');
                        $('#state').val(response.personal_details.state);
                    }
                    if (response.personal_details.city) {
                        console.log('Setting city');
                        $('#city').val(response.personal_details.city);
                    }
                    if (response.personal_details.address) {
                        console.log('Setting address');
                        $('#address').val(response.personal_details.address);
                    }
                    if (response.personal_details.interest) {
                        console.log('Setting interest');
                        $('#interest').val(response.personal_details.interest);
                    }
                    if (response.personal_details.purpose) {
                        console.log('Setting purpose');
                        $('#purpose').val(response.personal_details.purpose);
                    }
                    if (response.personal_details.usage) {
                        console.log('Setting usage');
                        $('#usage').val(response.personal_details.usage);
                    }
                    if (response.personal_details.reference) {
                        console.log('Setting reference');
                        $('#reference').val(response.personal_details.reference);
                    }
                    if (response.personal_details.language) {
                        console.log('Setting language');
                        $('#language').val(response.personal_details.language);
                    }
                } else {
                    console.log('No personal details found - fields will remain empty');
                }
                
                // Populate brand kit ONLY if it exists and fields are not null/empty
                if (response.brand_kit) {
                    console.log('Populating brand kit...');
                    if (response.brand_kit.website) {
                        console.log('Setting website');
                        $('#website').val(response.brand_kit.website);
                    }
                    if (response.brand_kit.role) {
                        console.log('Setting role');
                        $('#role').val(response.brand_kit.role);
                    }
                    // Populate usage from brand_kit if exists, otherwise from personal_details
                    if (response.brand_kit.usage) {
                        console.log('Setting usage from brand_kit:', response.brand_kit.usage);
                        $('#usage').val(response.brand_kit.usage);
                    } else if (response.personal_details && response.personal_details.usage) {
                        console.log('Setting usage from personal_details:', response.personal_details.usage);
                        $('#usage').val(response.personal_details.usage);
                    }
                } else {
                    console.log('No brand kit found - fields will remain empty');
                    // Still try to populate usage from personal_details if brand_kit doesn't exist
                    if (response.personal_details && response.personal_details.usage) {
                        console.log('Setting usage from personal_details (no brand_kit):', response.personal_details.usage);
                        $('#usage').val(response.personal_details.usage);
                    }
                }
                
                // Show usage type badge
                if (response.usage_type) {
                    console.log('Showing usage type badge:', response.usage_type);
                    $('#usageTypeBadge').show();
                    $('#usageTypeText').html('<span class="badge badge-' + 
                        (response.usage_type === 'professional' ? 'primary' : 'success') + 
                        '">' + response.usage_type.toUpperCase() + '</span>');
                } else {
                    console.log('No usage type - hiding badge');
                    $('#usageTypeBadge').hide();
                }
                
                console.log('Hiding loading, showing content...');
                $('#personalDetailsLoading').hide();
                $('#personalDetailsContent').show();
                
                // Highlight fields that have values
                console.log('Highlighting filled fields...');
                highlightFilledFields();
                
                console.log('=== PERSONAL DETAILS LOADED SUCCESSFULLY ===');
            } else {
                console.error('Error in response:', response.message);
                alert('Error loading personal details: ' + response.message);
                $('#personalDetailsModal').modal('hide');
            }
        },
        error: function(xhr, status, error) {
            console.error('=== AJAX ERROR ===');
            console.error('XHR Object:', xhr);
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response Text:', xhr.responseText);
            console.error('Status Code:', xhr.status);
            console.error('Ready State:', xhr.readyState);
            
            $('#debugContent').html(
                '<span style="color: red;">ERROR!</span><br>' +
                'Status Code: ' + xhr.status + '<br>' +
                'Status: ' + status + '<br>' +
                'Error: ' + error + '<br>' +
                'Response: ' + (xhr.responseText ? xhr.responseText.substring(0, 200) : 'No response')
            );
            $('#debugInfo').show();
            
            var errorMsg = 'Error loading personal details';
            
            if (xhr.status === 0) {
                errorMsg += ': Network error or request blocked. Check console for CORS/network issues.';
            } else if (xhr.status === 404) {
                errorMsg += ': Route not found (404). Check if route exists.';
            } else if (xhr.status === 500) {
                errorMsg += ': Server error (500). Check Laravel logs.';
            } else if (xhr.status === 401 || xhr.status === 403) {
                errorMsg += ': Authentication/Authorization error. You may need to login again.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg += ': ' + xhr.responseJSON.message;
            } else if (xhr.statusText) {
                errorMsg += ': ' + xhr.statusText;
            }
            
            console.error('Final Error Message:', errorMsg);
            alert(errorMsg);
            $('#personalDetailsModal').modal('hide');
        },
        complete: function() {
            console.log('=== AJAX REQUEST COMPLETE ===');
        }
    });
}

$('#personalDetailsForm').on('submit', function(e) {
    e.preventDefault();
    
    console.log('=== FORM SUBMISSION START ===');
    
    var uid = $('#user_uid').val();
    var formData = $(this).serialize();
    
    console.log('UID:', uid);
    console.log('Serialized Form Data:', formData);
    
    // Log individual field values
    console.log('Form Field Values:', {
        user_name: $('#user_name').val(),
        bio: $('#bio').val(),
        country: $('#country').val(),
        state: $('#state').val(),
        city: $('#city').val(),
        address: $('#address').val(),
        website: $('#website').val(),
        role: $('#role').val(),
        interest: $('#interest').val(),
        purpose: $('#purpose').val(),
        usage: $('#usage').val(),
        reference: $('#reference').val(),
        language: $('#language').val()
    });
    
    $.ajax({
        url: '{{ url("/user/personal-details") }}/' + uid,
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            console.log('Sending update request...');
        },
        success: function(response) {
            console.log('=== UPDATE SUCCESS ===');
            console.log('Response:', response);
            
            if (response.success) {
                alert('Personal details updated successfully!');
                $('#personalDetailsModal').modal('hide');
                
                // Optionally reload the page to see updated data
                // location.reload();
            } else {
                console.error('Update failed:', response.message);
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('=== UPDATE ERROR ===');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            
            var errorMsg = 'Error updating personal details';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg += ': ' + xhr.responseJSON.message;
            }
            alert(errorMsg);
        },
        complete: function() {
            console.log('=== FORM SUBMISSION COMPLETE ===');
        }
    });
});

// Function to highlight fields that have values
function highlightFilledFields() {
    $('#personalDetailsForm input, #personalDetailsForm textarea, #personalDetailsForm select').each(function() {
        var $field = $(this);
        var value = $field.val();
        
        if (value && value.trim() !== '') {
            $field.addClass('has-value');
        } else {
            $field.removeClass('has-value');
        }
    });
}

// Update highlighting when user types
$('#personalDetailsForm').on('input change', 'input, textarea, select', function() {
    var $field = $(this);
    var value = $field.val();
    
    if (value && value.trim() !== '') {
        $field.addClass('has-value');
    } else {
        $field.removeClass('has-value');
    }
});

// Explicit close button handlers
$(document).ready(function() {
    console.log('Personal Details Modal - Close handlers initialized');
    
    // Close button in header (X button)
    $('#personalDetailsModal .close').on('click', function(e) {
        e.preventDefault();
        console.log('Close button (X) clicked');
        $('#personalDetailsModal').modal('hide');
    });
    
    // Close button in footer
    $('#closePersonalDetailsBtn').on('click', function(e) {
        e.preventDefault();
        console.log('Close button (footer) clicked');
        $('#personalDetailsModal').modal('hide');
    });
    
    // Backup: data-dismiss attribute handler
    $('[data-dismiss="modal"]').on('click', function() {
        console.log('Data-dismiss clicked');
        var modalId = $(this).closest('.modal').attr('id');
        if (modalId) {
            $('#' + modalId).modal('hide');
        }
    });
    
    // Click outside modal to close
    $('#personalDetailsModal').on('click', function(e) {
        if ($(e.target).hasClass('modal')) {
            console.log('Clicked outside modal');
            $('#personalDetailsModal').modal('hide');
        }
    });
    
    // ESC key to close
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#personalDetailsModal').hasClass('show')) {
            console.log('ESC key pressed');
            $('#personalDetailsModal').modal('hide');
        }
    });
    
    // Log when modal is hidden
    $('#personalDetailsModal').on('hidden.bs.modal', function() {
        console.log('Modal hidden successfully');
    });
});
</script>
