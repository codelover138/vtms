<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {
    $('#year-selector').change(function() {
        var year = $(this).val();
        var customer_id = <?= $customer->id ?>;
        window.location.href = '<?= admin_url('tax_calculations/view?customer_id=' . $customer->id . '&year=') ?>' + year;
    });

    $('.update-payment').click(function(e) {
        e.preventDefault();
        var payment_id = $(this).data('payment-id');
        var payment_type = $(this).data('payment-type');
        var amount = parseFloat($(this).data('amount'));
        
        bootbox.prompt({
            title: "<?= lang('enter_paid_amount') ?>",
            inputType: 'number',
            value: amount,
            callback: function(result) {
                if (result !== null && result !== '') {
                    var paid_date = prompt("<?= lang('enter_paid_date') ?> (YYYY-MM-DD):", "<?= date('Y-m-d') ?>");
                    if (paid_date) {
                        $.ajax({
                            type: 'POST',
                            url: '<?= admin_url('tax_calculations/updatePayment') ?>',
                            data: {
                                payment_id: payment_id,
                                payment_type: payment_type,
                                paid_amount: result,
                                paid_date: paid_date,
                                status: 'paid'
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (data.error == 0) {
                                    location.reload();
                                } else {
                                    bootbox.alert(data.msg);
                                }
                            }
                        });
                    }
                }
            }
        });
    });
});
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-calculator"></i><?= lang('tax_calculations'); ?> - 
            <?= $customer->name . ' ' . ($customer->last_name ? $customer->last_name : '') . ' (' . $customer->company . ')'; ?>
        </h2>
        <div class="box-icon">
            <div class="form-group" style="margin: 10px;">
                <label for="year-selector"><?= lang('year'); ?>: </label>
                <select id="year-selector" class="form-control" style="display:inline-block; width:100px;">
                    <?php 
                    $current_year = date('Y');
                    for ($y = $current_year; $y >= $current_year - 10; $y--) {
                        $selected = ($y == $year) ? 'selected' : '';
                        echo '<option value="' . $y . '" ' . $selected . '>' . $y . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="col-lg-12" style="margin-bottom: 20px;">
                    <a href="<?= admin_url('tax_calculations/settings?customer_id=' . $customer->id) ?>" 
                       class="btn btn-warning">
                        <i class="fa fa-cog"></i> <?= lang('tax_settings') ?>
                    </a>
                    <a href="<?= admin_url('tax_calculations') ?>" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> <?= lang('back_to_list') ?>
                    </a>
                </div>

                <?php if ($tax_calculation): ?>
                <!-- Tax Calculation Section -->
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                <?= lang('tax_calculation') ?> - <?= lang('year') ?>: <?= $year ?>
                                <small class="text-muted">(<?= lang('calculated_in_year') ?> <?= $year + 1 ?>)</small>
                            </h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <td><strong><?= lang('total_sales'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($tax_calculation->total_sales); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('previous_year_inps'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($tax_calculation->previous_year_inps); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('coefficient_of_profitability'); ?></strong></td>
                                            <td><?= number_format($tax_calculation->coefficient_used, 2); ?>%</td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('taxable_income'); ?></strong></td>
                                            <td><strong><?= $this->sma->formatMoney($tax_calculation->taxable_income); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('tax_rate'); ?></strong></td>
                                            <td><?= number_format($tax_calculation->tax_rate_used, 2); ?>%</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr class="success">
                                            <td><strong><?= lang('tax_due'); ?></strong></td>
                                            <td><strong><?= $this->sma->formatMoney($tax_calculation->tax_due); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('advance_payments_made'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($tax_calculation->advance_payments_made); ?></td>
                                        </tr>
                                        <tr class="info">
                                            <td><strong><?= lang('balance_payment'); ?></strong></td>
                                            <td><strong><?= $this->sma->formatMoney($tax_calculation->balance_payment); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('next_year_advance_base'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($tax_calculation->next_year_advance_base); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tax Payments Section -->
                <?php if (!empty($tax_payments)): ?>
                <div class="col-lg-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                <?= lang('tax_payments') ?> - <?= lang('tax_year') ?>: <?= $year ?>
                                <small class="text-muted">(<?= lang('payments_due_in') ?> <?= $year + 1 ?>-<?= $year + 2 ?>)</small>
                            </h3>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= lang('payment_type'); ?></th>
                                            <th><?= lang('for_tax_year'); ?></th>
                                            <th><?= lang('due_date'); ?></th>
                                            <th><?= lang('amount'); ?></th>
                                            <th><?= lang('paid_amount'); ?></th>
                                            <th><?= lang('paid_date'); ?></th>
                                            <th><?= lang('status'); ?></th>
                                            <th><?= lang('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tax_payments as $payment): ?>
                                        <tr>
                                            <td><?= lang($payment->payment_type); ?></td>
                                            <td><strong><?= $payment->payment_year ?></strong></td>
                                            <td><?= $this->sma->hrld($payment->due_date); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->amount); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->paid_amount); ?></td>
                                            <td><?= $payment->paid_date ? $this->sma->hrld($payment->paid_date) : '-'; ?></td>
                                            <td>
                                                <?php
                                                if ($payment->status == 'paid') {
                                                    echo '<span class="label label-success">' . lang('paid') . '</span>';
                                                } elseif ($payment->status == 'overdue') {
                                                    echo '<span class="label label-danger">' . lang('overdue') . '</span>';
                                                } else {
                                                    echo '<span class="label label-warning">' . lang('pending') . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($payment->status != 'paid'): ?>
                                                <a href="#" class="btn btn-xs btn-success update-payment" 
                                                   data-payment-id="<?= $payment->id ?>" 
                                                   data-payment-type="tax"
                                                   data-amount="<?= $payment->amount ?>">
                                                    <i class="fa fa-check"></i> <?= lang('mark_paid') ?>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- INPS Calculation Section -->
                <?php if ($inps_calculation): ?>
                <div class="col-lg-12">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= lang('inps_calculation') ?> - <?= lang('year') ?>: <?= $year ?></h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <td><strong><?= lang('taxable_income'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($inps_calculation->taxable_income); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('inps_rate'); ?></strong></td>
                                            <td><?= number_format($inps_calculation->inps_rate, 2); ?>%</td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('inps_amount'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($inps_calculation->inps_amount); ?></td>
                                        </tr>
                                        <?php if ($inps_calculation->discount_percentage > 0): ?>
                                        <tr>
                                            <td><strong><?= lang('discount_percentage'); ?></strong></td>
                                            <td><?= number_format($inps_calculation->discount_percentage, 2); ?>%</td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('discount_amount'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($inps_calculation->discount_amount); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr class="success">
                                            <td><strong><?= lang('inps_amount_after_discount'); ?></strong></td>
                                            <td><strong><?= $this->sma->formatMoney($inps_calculation->inps_amount_after_discount); ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- INPS Payments Section -->
                <?php if (!empty($inps_payments)): ?>
                <div class="col-lg-12">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= lang('inps_payments') ?> - <?= lang('year') ?>: <?= $year ?></h3>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= lang('installment_number'); ?></th>
                                            <th><?= lang('due_date'); ?></th>
                                            <th><?= lang('amount'); ?></th>
                                            <th><?= lang('paid_amount'); ?></th>
                                            <th><?= lang('paid_date'); ?></th>
                                            <th><?= lang('status'); ?></th>
                                            <th><?= lang('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inps_payments as $payment): ?>
                                        <tr>
                                            <td><?= $payment->installment_number; ?></td>
                                            <td><?= $this->sma->hrld($payment->due_date); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->amount); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->paid_amount); ?></td>
                                            <td><?= $payment->paid_date ? $this->sma->hrld($payment->paid_date) : '-'; ?></td>
                                            <td>
                                                <?php
                                                if ($payment->status == 'paid') {
                                                    echo '<span class="label label-success">' . lang('paid') . '</span>';
                                                } elseif ($payment->status == 'overdue') {
                                                    echo '<span class="label label-danger">' . lang('overdue') . '</span>';
                                                } else {
                                                    echo '<span class="label label-warning">' . lang('pending') . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($payment->status != 'paid'): ?>
                                                <a href="#" class="btn btn-xs btn-success update-payment" 
                                                   data-payment-id="<?= $payment->id ?>" 
                                                   data-payment-type="inps"
                                                   data-amount="<?= $payment->amount ?>">
                                                    <i class="fa fa-check"></i> <?= lang('mark_paid') ?>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div class="col-lg-12">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        <?= lang('no_tax_calculation_found_for_year') ?> <?= $year ?>. 
                        <a href="#" class="calculate-tax-alert" data-customer-id="<?= $customer->id ?>"><?= lang('calculate_now') ?></a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
$('.calculate-tax-alert').click(function(e) {
    e.preventDefault();
    var customer_id = $(this).data('customer-id');
    var year = <?= $year ?>;
    
    $.ajax({
        type: 'POST',
        url: '<?= admin_url('tax_calculations/calculate') ?>',
        data: {
            customer_id: customer_id, 
            year: year,
            <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(data) {
            if (data.error == 0) {
                location.reload();
            } else {
                bootbox.alert(data.msg);
            }
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', status, error);
            console.log('Response:', xhr.responseText);
            bootbox.alert('Request failed: ' + error);
        }
    });
});
</script>

