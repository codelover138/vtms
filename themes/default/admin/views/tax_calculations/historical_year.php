<?php defined('BASEPATH') OR exit('No direct script access allowed');
$attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'historical-year-form');
echo admin_form_open('tax_calculations/save_historical_year', $attrib); ?>
<?= form_hidden('customer_id', $customer->id); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-history"></i> <?= lang('add_historical_year'); ?> -
            <?= $customer->name . ($customer->last_name ? ' ' . $customer->last_name : '') . ' (' . $customer->company . ')'; ?>
        </h2>
    </div>
    <div class="box-content">
        <p class="introtext"><?= lang('add_historical_year_intro'); ?></p>

        <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="year"><?= lang('year'); ?> </label>
                    <select name="year" id="year" class="form-control select" required>
                        <?php
                        $current_year = (int)date('Y');
                        for ($y = $current_year; $y >= $current_year - 15; $y--):
                            $exists = in_array((string)$y, $existing_year_list);
                        ?>
                        <option value="<?= $y ?>"><?= $y ?><?= $exists ? ' (' . lang('already_has_data') . ')' : '' ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><?= lang('coefficient_used'); ?></label>
                    <input type="number" name="coefficient_used" class="form-control" step="0.01"
                        value="<?= $customer->coefficient_of_profitability ? $customer->coefficient_of_profitability : '78' ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><?= lang('tax_rate_used'); ?></label>
                    <input type="number" name="tax_rate_used" class="form-control" step="0.01"
                        value="<?= $customer->tax_rate ? $customer->tax_rate : '5' ?>">
                </div>
            </div>
        </div>

        <!-- Tax calculation -->
        <div class="row">
            <div class="col-lg-12">
                <h4 style="border-bottom: 2px solid #ddd; padding-bottom: 8px; margin-top: 20px;">
                    <?= lang('tax_calculation'); ?></h4>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('total_sales'); ?></label>
                    <input type="number" name="total_sales" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('taxable_income'); ?></label>
                    <input type="number" name="taxable_income" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('tax_due'); ?></label>
                    <input type="number" name="tax_due" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('advance_payments_made'); ?></label>
                    <input type="number" name="advance_payments_made" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('balance_payment'); ?></label>
                    <input type="number" name="balance_payment" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('next_year_advance_base'); ?></label>
                    <input type="number" name="next_year_advance_base" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('previous_year_inps'); ?></label>
                    <input type="number" name="previous_year_inps" class="form-control" step="0.01" value="0">
                </div>
            </div>
        </div>

        <!-- Tax payments -->
        <div class="row">
            <div class="col-lg-12">
                <h5 style="margin-top: 15px;"><?= lang('tax_payments'); ?></h5>
                <table class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th><?= lang('payment_type'); ?></th>
                            <th><?= lang('amount'); ?></th>
                            <th><?= lang('paid_amount'); ?></th>
                            <th><?= lang('paid_date'); ?></th>
                            <th><?= lang('status'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= lang('balance'); ?></td>
                            <td><input type="number" name="tax_balance_amount" class="form-control input-sm" step="0.01"
                                    value="0"></td>
                            <td><input type="number" name="tax_balance_paid_amount" class="form-control input-sm"
                                    step="0.01" value="0"></td>
                            <td><input type="date" name="tax_balance_paid_date" class="form-control input-sm"></td>
                            <td>
                                <select name="tax_balance_status" class="form-control input-sm">
                                    <option value="pending"><?= lang('pending'); ?></option>
                                    <option value="paid"><?= lang('paid'); ?></option>
                                    <option value="overdue"><?= lang('overdue'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?= lang('first_advance'); ?></td>
                            <td><input type="number" name="tax_first_advance_amount" class="form-control input-sm"
                                    step="0.01" value="0"></td>
                            <td><input type="number" name="tax_first_advance_paid_amount" class="form-control input-sm"
                                    step="0.01" value="0"></td>
                            <td><input type="date" name="tax_first_advance_paid_date" class="form-control input-sm">
                            </td>
                            <td>
                                <select name="tax_first_advance_status" class="form-control input-sm">
                                    <option value="pending"><?= lang('pending'); ?></option>
                                    <option value="paid"><?= lang('paid'); ?></option>
                                    <option value="overdue"><?= lang('overdue'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?= lang('second_advance'); ?></td>
                            <td><input type="number" name="tax_second_advance_amount" class="form-control input-sm"
                                    step="0.01" value="0"></td>
                            <td><input type="number" name="tax_second_advance_paid_amount" class="form-control input-sm"
                                    step="0.01" value="0"></td>
                            <td><input type="date" name="tax_second_advance_paid_date" class="form-control input-sm">
                            </td>
                            <td>
                                <select name="tax_second_advance_status" class="form-control input-sm">
                                    <option value="pending"><?= lang('pending'); ?></option>
                                    <option value="paid"><?= lang('paid'); ?></option>
                                    <option value="overdue"><?= lang('overdue'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- INPS -->
        <div class="row">
            <div class="col-lg-12">
                <h4 style="border-bottom: 2px solid #ddd; padding-bottom: 8px; margin-top: 25px;">INPS</h4>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('inps_taxable_income'); ?></label>
                    <input type="number" name="inps_taxable_income" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('inps_amount'); ?></label>
                    <input type="number" name="inps_amount" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label><?= lang('discount_percentage'); ?></label>
                    <input type="number" name="inps_discount_percentage" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label><?= lang('discount_amount'); ?></label>
                    <input type="number" name="inps_discount_amount" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label><?= lang('inps_amount_after_discount'); ?></label>
                    <input type="number" name="inps_amount_after_discount" class="form-control" step="0.01" value="0">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h5 style="margin-top: 10px;"><?= lang('inps_payments'); ?> (<?= $gestione_separata ? '3' : '4'; ?>
                    <?= lang('installments'); ?>)</h5>
                <table class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= lang('amount'); ?></th>
                            <th><?= lang('paid_amount'); ?></th>
                            <th><?= lang('paid_date'); ?></th>
                            <th><?= lang('status'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($n = 1; $n <= ($gestione_separata ? 3 : 4); $n++): ?>
                        <tr>
                            <td><?= $n ?></td>
                            <td><input type="number" name="inps_installment_<?= $n ?>_amount"
                                    class="form-control input-sm" step="0.01" value="0"></td>
                            <td><input type="number" name="inps_installment_<?= $n ?>_paid_amount"
                                    class="form-control input-sm" step="0.01" value="0"></td>
                            <td><input type="date" name="inps_installment_<?= $n ?>_paid_date"
                                    class="form-control input-sm"></td>
                            <td>
                                <select name="inps_installment_<?= $n ?>_status" class="form-control input-sm">
                                    <option value="PENDING"><?= lang('pending'); ?></option>
                                    <option value="PAID"><?= lang('paid'); ?></option>
                                    <option value="OVERDUE"><?= lang('overdue'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($is_artigiani): ?>
        <!-- INAIL -->
        <div class="row">
            <div class="col-lg-12">
                <h4 style="border-bottom: 2px solid #ddd; padding-bottom: 8px; margin-top: 25px;">INAIL</h4>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><?= lang('inail_taxable_income'); ?></label>
                    <input type="number" name="inail_taxable_income" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><?= lang('inail_final_amount'); ?></label>
                    <input type="number" name="inail_final_amount" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><?= lang('inail_payment'); ?> <?= lang('amount'); ?></label>
                    <input type="number" name="inail_payment_amount" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><?= lang('paid_amount'); ?></label>
                    <input type="number" name="inail_payment_paid_amount" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><?= lang('paid_date'); ?></label>
                    <input type="date" name="inail_payment_paid_date" class="form-control">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><?= lang('status'); ?></label>
                    <select name="inail_payment_status" class="form-control">
                        <option value="pending"><?= lang('pending'); ?></option>
                        <option value="paid"><?= lang('paid'); ?></option>
                        <option value="overdue"><?= lang('overdue'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($is_commercianti_artigiani): ?>
        <!-- Diritto Annuale -->
        <div class="row">
            <div class="col-lg-12">
                <h4 style="border-bottom: 2px solid #ddd; padding-bottom: 8px; margin-top: 25px;">
                    <?= lang('diritto_annuale_payments'); ?></h4>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('amount'); ?></label>
                    <input type="number" name="diritto_annuale_amount" class="form-control" step="0.01"
                        value="<?= $customer->diritto_annuale_amount ? $customer->diritto_annuale_amount : '0' ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('paid_amount'); ?></label>
                    <input type="number" name="diritto_annuale_paid_amount" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('paid_date'); ?></label>
                    <input type="date" name="diritto_annuale_paid_date" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('status'); ?></label>
                    <select name="diritto_annuale_status" class="form-control">
                        <option value="pending"><?= lang('pending'); ?></option>
                        <option value="paid"><?= lang('paid'); ?></option>
                        <option value="overdue"><?= lang('overdue'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fattura tra privati (optional) -->
        <div class="row">
            <div class="col-lg-12">
                <h4 style="border-bottom: 2px solid #ddd; padding-bottom: 8px; margin-top: 25px;">
                    <?= lang('fattura_tra_privati_calculation'); ?> (<?= lang('optional'); ?>)</h4>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('total_invoices'); ?></label>
                    <input type="number" name="fattura_total_invoices" class="form-control" min="0" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('total_sales_amount'); ?></label>
                    <input type="number" name="fattura_total_sales_amount" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('total_payment_amount'); ?></label>
                    <input type="number" name="fattura_total_payment_amount" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('paid_amount'); ?></label>
                    <input type="number" name="fattura_paid_amount" class="form-control" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('paid_date'); ?></label>
                    <input type="date" name="fattura_paid_date" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('status'); ?></label>
                    <select name="fattura_status" class="form-control">
                        <option value="pending"><?= lang('pending'); ?></option>
                        <option value="paid"><?= lang('paid'); ?></option>
                        <option value="overdue"><?= lang('overdue'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-top: 25px;">
            <a href="<?= admin_url('tax_calculations/settings?customer_id=' . $customer->id) ?>"
                class="btn btn-default">
                <i class="fa fa-arrow-left"></i> <?= lang('back') ?>
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> <?= lang('save_historical_year') ?>
            </button>
            <a href="<?= admin_url('tax_calculations/view?customer_id=' . $customer->id) ?>" class="btn btn-info">
                <i class="fa fa-eye"></i> <?= lang('view_tax_calculation') ?>
            </a>
        </div>
    </div>
</div>
<?= form_close(); ?>