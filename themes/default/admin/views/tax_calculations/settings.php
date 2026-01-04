<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'tax-settings-form');
echo admin_form_open_multipart("tax_calculations/settings?customer_id=" . $customer->id, $attrib); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-cog"></i><?= lang('tax_settings'); ?> -
            <?= $customer->name . ' ' . ($customer->last_name ? $customer->last_name : '') . ' (' . $customer->company . ')'; ?>
        </h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('configure_tax_settings_for_customer'); ?></p>

                <?php if ($error) { ?>
                <div class="alert alert-danger">
                    <?= $error; ?>
                </div>
                <?php } ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('customer_type', 'customer_type'); ?> <b>*</b>
                            <?php
                            if (isset($has_existing_calculations) && $has_existing_calculations) {
                                // If calculations exist, show as read-only text with hidden field
                                echo form_hidden('customer_type', $customer->customer_type ? $customer->customer_type : '');
                                echo '<div class="form-control" style="background-color: #f5f5f5; cursor: not-allowed;">';
                                echo htmlspecialchars($customer->customer_type ? $customer->customer_type : '');
                                echo '</div>';
                                echo '<small class="help-block text-danger">';
                                echo '<i class="fa fa-exclamation-triangle"></i> ';
                                echo lang('customer_type_locked_existing_calculations');
                                echo '</small>';
                            } else {
                                // If no calculations, show normal dropdown
                                $ct_options = array();
                                foreach ($customer_types as $ct) {
                                    $ct_options[$ct] = $ct;
                                }
                                echo form_dropdown('customer_type', $ct_options, 
                                    ($customer->customer_type ? $customer->customer_type : ''), 
                                    'class="form-control select" id="customer_type" required="required"');
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('tax_regime', 'tax_regime'); ?> <b>*</b>
                            <?php
                            echo form_dropdown('tax_regime', $tax_regimes, 
                                ($customer->tax_regime ? $customer->tax_regime : 'regime_forfettario'), 
                                'class="form-control select" id="tax_regime" required="required"');
                            ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('coefficient_of_profitability', 'coefficient_of_profitability'); ?> <b>*</b>
                            <div class="input-group">
                                <?php echo form_input('coefficient_of_profitability', 
                                    ($customer->coefficient_of_profitability ? $customer->coefficient_of_profitability : '78.00'), 
                                    'class="form-control tip" id="coefficient_of_profitability" required="required" type="number" step="0.01" min="0" max="100" autocomplete="off"'); ?>
                                <span class="input-group-addon">%</span>
                            </div>
                            <small class="help-block"><?= lang('coefficient_help_text'); ?> (e.g., 78% for services, 40%
                                for wholesale)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('tax_rate', 'tax_rate'); ?> <b>*</b>
                            <div class="input-group">
                                <?php echo form_input('tax_rate', 
                                    ($customer->tax_rate ? $customer->tax_rate : '5.00'), 
                                    'class="form-control tip" id="tax_rate" required="required" type="number" step="0.01" min="0" max="100"'); ?>
                                <span class="input-group-addon">%</span>
                            </div>
                            <small class="help-block"><?= lang('tax_rate_help_text'); ?> (5% for first 5 years, 15%
                                standard)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('business_start_date', 'business_start_date'); ?>
                            <?php echo form_input('business_start_date', 
                                ($customer->business_start_date ? $this->sma->hrsd($customer->business_start_date)  : ''),
                                'class="form-control input-tip date" id="business_start_date"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" id="inps_discount_group">
                            <?= lang('inps_discount_eligible', 'inps_discount_eligible'); ?>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="inps_discount_eligible" value="1"
                                        id="inps_discount_eligible"
                                        <?= ($customer->inps_discount_eligible ? 'checked' : '') ?>>
                                    <?= lang('eligible_for_35_percent_inps_discount'); ?>
                                    <small>(<?= lang('for_commercianti_artigiani_only'); ?>)</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('annual_revenue_limit', 'annual_revenue_limit'); ?>
                            <div class="input-group">
                                <span class="input-group-addon">€</span>
                                <?php echo form_input('annual_revenue_limit', 
                                    ($customer->annual_revenue_limit ? $customer->annual_revenue_limit : '85000.00'), 
                                    'class="form-control tip" id="annual_revenue_limit" type="number" step="0.01" min="0"'); ?>
                            </div>
                            <small class="help-block"><?= lang('annual_revenue_limit_help_text'); ?></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('employee_cost_limit', 'employee_cost_limit'); ?>
                            <div class="input-group">
                                <span class="input-group-addon">€</span>
                                <?php echo form_input('employee_cost_limit', 
                                    ($customer->employee_cost_limit ? $customer->employee_cost_limit : '20000.00'), 
                                    'class="form-control tip" id="employee_cost_limit" type="number" step="0.01" min="0"'); ?>
                            </div>
                            <small class="help-block"><?= lang('employee_cost_limit_help_text'); ?></small>
                        </div>
                    </div>
                </div>

                <!-- INAIL Settings (Only for Artigiani) -->
                <div class="row" id="inail_settings_group" style="display: none;">
                    <div class="col-lg-12">
                        <h4 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 20px;">
                            <?= lang('inail_settings'); ?> <small>(<?= lang('for_artigiani_only'); ?>)</small>
                        </h4>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('inail_ateco_code', 'inail_ateco_code'); ?>
                            <?php echo form_input('inail_ateco_code', 
                                ($customer->inail_ateco_code ? $customer->inail_ateco_code : ''), 
                                'class="form-control tip" id="inail_ateco_code" placeholder="e.g., 43.32.10"'); ?>
                            <small class="help-block"><?= lang('inail_ateco_code_help'); ?></small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('inail_rate', 'inail_rate'); ?>
                            <div class="input-group">
                                <?php echo form_input('inail_rate', 
                                    ($customer->inail_rate ? $customer->inail_rate : ''), 
                                    'class="form-control tip" id="inail_rate" type="number" step="0.01" min="0" max="100" placeholder="e.g., 4.2"'); ?>
                                <span class="input-group-addon">%</span>
                            </div>
                            <small class="help-block"><?= lang('inail_rate_help'); ?></small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('inail_minimum_payment', 'inail_minimum_payment'); ?>
                            <div class="input-group">
                                <span class="input-group-addon">€</span>
                                <?php echo form_input('inail_minimum_payment', 
                                    ($customer->inail_minimum_payment ? $customer->inail_minimum_payment : '800.00'), 
                                    'class="form-control tip" id="inail_minimum_payment" type="number" step="0.01" min="0"'); ?>
                            </div>
                            <small class="help-block"><?= lang('inail_minimum_payment_help'); ?></small>
                        </div>
                    </div>
                </div>

                <!-- Diritto Annuale Settings (Only for Artigiani and Commercianti) -->
                <div class="row" id="diritto_annuale_settings_group" style="display: none;">
                    <div class="col-lg-12">
                        <h4 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 20px;">
                            <?= lang('diritto_annuale_settings'); ?>
                            <small>(<?= lang('for_artigiani_commercianti_only'); ?>)</small>
                        </h4>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('diritto_annuale_amount', 'diritto_annuale_amount'); ?>
                            <div class="input-group">
                                <span class="input-group-addon">€</span>
                                <?php echo form_input('diritto_annuale_amount', 
                                    ($customer->diritto_annuale_amount ? $customer->diritto_annuale_amount : ''), 
                                    'class="form-control tip" id="diritto_annuale_amount" type="number" step="0.01" min="0"'); ?>
                            </div>
                            <small class="help-block"><?= lang('diritto_annuale_amount_help'); ?></small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <a href="<?= admin_url('tax_calculations/view?customer_id=' . $customer->id) ?>"
                        class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> <?= lang('back') ?>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> <?= lang('save_settings') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= form_close(); ?>

<script type="text/javascript">
$(document).ready(function() {
    // Helper function to get customer type value (handles both select and hidden field)
    function getCustomerType() {
        var customerTypeSelect = $('#customer_type');
        if (customerTypeSelect.length && customerTypeSelect.is('select')) {
            return customerTypeSelect.val();
        } else {
            // If it's read-only, get value from hidden field
            var hiddenField = $('input[name="customer_type"][type="hidden"]');
            if (hiddenField.length) {
                return hiddenField.val();
            }
        }
        return '';
    }

    // Function to toggle INPS discount eligible checkbox based on customer type
    function toggleINPSDiscount() {
        var customerType = getCustomerType();
        var inpsDiscountGroup = $('#inps_discount_group');
        var inpsDiscountCheckbox = $('#inps_discount_eligible');

        // Only show/enable for Commercianti and Artigiani
        if (customerType === 'Commercianti' || customerType === 'Artigiani') {
            inpsDiscountGroup.show();
            inpsDiscountCheckbox.prop('disabled', false);
        } else {
            // Hide and uncheck for other customer types
            inpsDiscountGroup.hide();
            inpsDiscountCheckbox.prop('checked', false);
            inpsDiscountCheckbox.prop('disabled', true);
        }
    }

    // Function to toggle INAIL settings based on customer type
    function toggleINAILSettings() {
        var customerType = getCustomerType();
        var inailSettingsGroup = $('#inail_settings_group');
        var inailAtecoCode = $('#inail_ateco_code');
        var inailRate = $('#inail_rate');
        var inailMinimumPayment = $('#inail_minimum_payment');

        // Only show/enable for Artigiani
        if (customerType === 'Artigiani' || customerType === 'artigiani') {
            inailSettingsGroup.show();
            inailAtecoCode.prop('disabled', false);
            inailRate.prop('disabled', false);
            inailMinimumPayment.prop('disabled', false);
        } else {
            // Hide and clear for other customer types
            inailSettingsGroup.hide();
            inailAtecoCode.prop('disabled', true);
            inailRate.prop('disabled', true);
            inailMinimumPayment.prop('disabled', true);
        }
    }

    // Function to toggle Diritto Annuale settings based on customer type
    function toggleDirittoAnnualeSettings() {
        var customerType = getCustomerType();
        var dirittoAnnualeSettingsGroup = $('#diritto_annuale_settings_group');
        var dirittoAnnualeAmount = $('#diritto_annuale_amount');

        // Only show/enable for Artigiani and Commercianti
        if (customerType === 'Artigiani' || customerType === 'artigiani' ||
            customerType === 'Commercianti' || customerType === 'commercianti') {
            dirittoAnnualeSettingsGroup.show();
            dirittoAnnualeAmount.prop('disabled', false);
        } else {
            // Hide and clear for other customer types
            dirittoAnnualeSettingsGroup.hide();
            dirittoAnnualeAmount.prop('disabled', true);
        }
    }

    // Initial check on page load - wait a bit for select2 to initialize if used
    setTimeout(function() {
        toggleINPSDiscount();
        toggleINAILSettings();
        toggleDirittoAnnualeSettings();
    }, 100);

    // Update when customer type changes (only if not disabled and is a select element)
    $(document).on('change', '#customer_type', function() {
        if ($(this).is('select') && !$(this).prop('disabled')) {
            toggleINPSDiscount();
            toggleINAILSettings();
            toggleDirittoAnnualeSettings();
        }
    });

    // Also listen for select2 change event if select2 is used
    if ($.fn.select2) {
        $(document).on('select2:select', '#customer_type', function() {
            if ($(this).is('select') && !$(this).prop('disabled')) {
                toggleINPSDiscount();
                toggleINAILSettings();
                toggleDirittoAnnualeSettings();
            }
        });
    }
});
</script>