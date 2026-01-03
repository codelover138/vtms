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
                            $ct_options = array();
                            foreach ($customer_types as $ct) {
                                $ct_options[$ct] = $ct;
                            }
                            echo form_dropdown('customer_type', $ct_options, 
                                ($customer->customer_type ? $customer->customer_type : ''), 
                                'class="form-control select" id="customer_type" required="required"');
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
                                    'class="form-control tip" id="coefficient_of_profitability" required="required" type="number" step="0.01" min="0" max="100"'); ?>
                                <span class="input-group-addon">%</span>
                            </div>
                            <small class="help-block"><?= lang('coefficient_help_text'); ?> (e.g., 78% for services, 40% for wholesale)</small>
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
                            <small class="help-block"><?= lang('tax_rate_help_text'); ?> (5% for first 5 years, 15% standard)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('business_start_date', 'business_start_date'); ?>
                            <?php echo form_input('business_start_date', 
                                ($customer->business_start_date ?$this->sma->hrsd($customer->business_start_date)  : ''),
                                'class="form-control input-tip date" id="business_start_date"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('inps_discount_eligible', 'inps_discount_eligible'); ?>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="inps_discount_eligible" value="1" 
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

