<!-- Add Gateway Modal -->
<div class="modal fade" id="addGatewayModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Add New Payment Gateway</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
                                       placeholder="e.g., Cashfree, Easebuzz">
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

                    <!-- Payment Types Selection -->
                    <div class="payment-types-selector-box">
                        <h6><i class="fas fa-tags mr-2"></i>Select Payment Types *</h6>
                        <small class="text-muted d-block mb-3">Choose which payment types this gateway will handle</small>
                        
                        <div class="payment-type-checkbox-item" data-type="caricature">
                            <input type="checkbox" name="payment_types[]" value="caricature" id="add-type-caricature">
                            <label for="add-type-caricature">
                                <i class="fas fa-user-tie mr-2"></i>Caricature
                            </label>
                        </div>
                        
                        <div class="payment-type-checkbox-item" data-type="template">
                            <input type="checkbox" name="payment_types[]" value="template" id="add-type-template">
                            <label for="add-type-template">
                                <i class="fas fa-file-alt mr-2"></i>Template Purchase
                            </label>
                        </div>
                        
                        <div class="payment-type-checkbox-item" data-type="video">
                            <input type="checkbox" name="payment_types[]" value="video" id="add-type-video">
                            <label for="add-type-video">
                                <i class="fas fa-video mr-2"></i>Video Purchase
                            </label>
                        </div>
                        
                        <div class="payment-type-checkbox-item" data-type="ai_credit">
                            <input type="checkbox" name="payment_types[]" value="ai_credit" id="add-type-ai-credit">
                            <label for="add-type-ai-credit">
                                <i class="fas fa-robot mr-2"></i>AI Credit
                            </label>
                        </div>
                        
                        <div class="payment-type-checkbox-item" data-type="subscription">
                            <input type="checkbox" name="payment_types[]" value="subscription" id="add-type-subscription">
                            <label for="add-type-subscription">
                                <i class="fas fa-crown mr-2"></i>Subscription
                            </label>
                        </div>
                    </div>


                    <!-- Credentials Configuration -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="fas fa-key mr-2"></i>Credentials Configuration *</h6>
                            <button type="button" class="btn btn-sm btn-success" id="addNewCredentialField">
                                <i class="fas fa-plus"></i> Add Field
                            </button>
                        </div>

                        <div id="addCredentialsContainer">
                            <div class="credential-row-box">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Gateway</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Gateway Modal -->
<div class="modal fade" id="editGatewayModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Gateway Configuration</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editGatewayForm">
                @csrf
                <input type="hidden" name="gateway_id" id="editGatewayId">
                <div class="modal-body" id="editModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Gateway</button>
                </div>
            </form>
        </div>
    </div>
</div>
