<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
.payment-pdf-actions {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.payment-pdf-actions .file-input-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.payment-pdf-actions input.input-file-pdf {
    position: absolute;
    width: 0.1px;
    height: 0.1px;
    opacity: 0;
    overflow: hidden;
    z-index: -1;
}
.payment-pdf-actions .btn-file-label {
    display: inline-block;
    padding: 5px 12px;
    font-size: 12px;
    line-height: 1.5;
    color: #555;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    cursor: pointer;
    margin: 0;
    transition: background 0.2s, border-color 0.2s;
}
.payment-pdf-actions .btn-file-label:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}
.payment-pdf-actions .file-name {
    font-size: 11px;
    color: #6c757d;
    max-width: 70px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    margin-left: 4px;
}
.payment-pdf-actions .btn-upload-pdf {
    padding: 5px 12px;
    font-size: 12px;
    line-height: 1.5;
    border-radius: 4px;
    border: 1px solid #28a745;
    color: #28a745;
    background: #fff;
    transition: background 0.2s, color 0.2s;
}
.payment-pdf-actions .btn-upload-pdf:hover {
    background: #28a745;
    color: #fff;
    border-color: #28a745;
}
.payment-pdf-actions .btn-download-pdf {
    padding: 5px 12px;
    font-size: 12px;
    line-height: 1.5;
    border-radius: 4px;
    background: #dc3545;
    border: 1px solid #dc3545;
    color: #fff;
    transition: background 0.2s, border-color 0.2s;
}
.payment-pdf-actions .btn-download-pdf:hover {
    background: #c82333;
    border-color: #bd2130;
    color: #fff;
}
</style>
<script>
$(document).ready(function() {
    $('#year-selector').change(function() {
        var year = $(this).val();
        var customer_id = <?= $customer->id ?>;
        window.location.href =
            '<?= admin_url('tax_calculations/view?customer_id=' . $customer->id . '&year=') ?>' + year;
    });

    $('.update-payment').click(function(e) {
        e.preventDefault();
        var payment_id = $(this).data('payment-id');
        var payment_type = $(this).data('payment-type');
        var amount = parseFloat($(this).data('amount'));

        var modalHtml = '<form id="payment-update-form" class="form-horizontal">' +
            '<div class="form-group">' +
            '<label for="paid_amount" class="col-sm-4 control-label"><?= lang('paid_amount') ?> *</label>' +
            '<div class="col-sm-8">' +
            '<div class="input-group">' +
            '<span class="input-group-addon">€</span>' +
            '<input type="number" class="form-control" id="paid_amount" name="paid_amount" step="0.01" min="0" value="' +
            amount + '" required>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="form-group">' +
            '<label for="paid_date" class="col-sm-4 control-label"><?= lang('paid_date') ?> *</label>' +
            '<div class="col-sm-8">' +
            '<input type="date" class="form-control" id="paid_date" name="paid_date" value="<?= date('Y-m-d') ?>" required>' +
            '</div>' +
            '</div>' +
            '</form>';

        var dialog = bootbox.dialog({
            title: "<?= lang('mark_payment_as_paid') ?>",
            message: modalHtml,
            buttons: {
                cancel: {
                    label: "Cancel",
                    className: "btn-default"
                },
                confirm: {
                    label: "<?= lang('save') ?>",
                    className: "btn-primary",
                    callback: function() {
                        var paid_amount = $('#paid_amount').val();
                        var paid_date = $('#paid_date').val();

                        if (!paid_amount || paid_amount <= 0) {
                            bootbox.alert("<?= lang('please_enter_valid_amount') ?>");
                            return false;
                        }

                        if (!paid_date) {
                            bootbox.alert("<?= lang('please_enter_paid_date') ?>");
                            return false;
                        }

                        $.ajax({
                            type: 'POST',
                            url: '<?= admin_url('tax_calculations/updatePayment') ?>',
                            data: {
                                <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>',
                                payment_id: payment_id,
                                payment_type: payment_type,
                                paid_amount: paid_amount,
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
                            },
                            error: function(xhr, status, error) {
                                bootbox.alert('Error: ' + error);
                            }
                        });

                        return false; // Prevent modal from closing until AJAX completes
                    }
                }
            }
        });

        // Make modal larger
        dialog.find('.modal-dialog').addClass('modal-lg');
    });

    // Edit Payment Modal
    $('.edit-payment').click(function(e) {
        e.preventDefault();
        var payment_id = $(this).data('payment-id');
        var payment_type = $(this).data('payment-type');
        var amount = parseFloat($(this).data('amount'));
        var due_date = $(this).data('due-date');
        var paid_amount = parseFloat($(this).data('paid-amount')) || 0;
        var paid_date = $(this).data('paid-date') || '';
        var status = $(this).data('status') || 'pending';

        var modalHtml = '<form id="payment-edit-form" class="form-horizontal">' +
            '<div class="form-group">' +
            '<label for="edit_amount" class="col-sm-4 control-label"><?= lang('amount') ?> *</label>' +
            '<div class="col-sm-8">' +
            '<div class="input-group">' +
            '<span class="input-group-addon">€</span>' +
            '<input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0" value="' + amount + '" required>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="form-group">' +
            '<label for="edit_due_date" class="col-sm-4 control-label"><?= lang('due_date') ?> *</label>' +
            '<div class="col-sm-8">' +
            '<input type="date" class="form-control" id="edit_due_date" name="due_date" value="' + due_date + '" required>' +
            '</div>' +
            '</div>' +
            '<div class="form-group">' +
            '<label for="edit_paid_amount" class="col-sm-4 control-label"><?= lang('paid_amount') ?></label>' +
            '<div class="col-sm-8">' +
            '<div class="input-group">' +
            '<span class="input-group-addon">€</span>' +
            '<input type="number" class="form-control" id="edit_paid_amount" name="paid_amount" step="0.01" min="0" value="' + paid_amount + '">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="form-group">' +
            '<label for="edit_paid_date" class="col-sm-4 control-label"><?= lang('paid_date') ?></label>' +
            '<div class="col-sm-8">' +
            '<input type="date" class="form-control" id="edit_paid_date" name="paid_date" value="' + paid_date + '">' +
            '</div>' +
            '</div>' +
            '<div class="form-group">' +
            '<label for="edit_status" class="col-sm-4 control-label"><?= lang('status') ?> *</label>' +
            '<div class="col-sm-8">' +
            '<select class="form-control" id="edit_status" name="status" required>' +
            '<option value="pending"' + (status == 'pending' ? ' selected' : '') + '><?= lang('pending') ?></option>' +
            '<option value="paid"' + (status == 'paid' ? ' selected' : '') + '><?= lang('paid') ?></option>' +
            '<option value="overdue"' + (status == 'overdue' ? ' selected' : '') + '><?= lang('overdue') ?></option>' +
            '</select>' +
            '</div>' +
            '</div>' +
            '</form>';

        var dialog = bootbox.dialog({
            title: "<?= lang('edit_payment') ?>",
            message: modalHtml,
            buttons: {
                cancel: {
                    label: "<?= lang('cancel') ?>",
                    className: "btn-default"
                },
                confirm: {
                    label: "<?= lang('save') ?>",
                    className: "btn-primary",
                    callback: function() {
                        var edit_amount = $('#edit_amount').val();
                        var edit_due_date = $('#edit_due_date').val();
                        var edit_paid_amount = $('#edit_paid_amount').val() || 0;
                        var edit_paid_date = $('#edit_paid_date').val() || null;
                        var edit_status = $('#edit_status').val();

                        if (!edit_amount || edit_amount <= 0) {
                            bootbox.alert("<?= lang('please_enter_valid_amount') ?>");
                            return false;
                        }

                        if (!edit_due_date) {
                            bootbox.alert("<?= lang('please_enter_due_date') ?>");
                            return false;
                        }

                        $.ajax({
                            type: 'POST',
                            url: '<?= admin_url('tax_calculations/editPayment') ?>',
                            data: {
                                <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>',
                                payment_id: payment_id,
                                payment_type: payment_type,
                                amount: edit_amount,
                                due_date: edit_due_date,
                                paid_amount: edit_paid_amount,
                                paid_date: edit_paid_date,
                                status: edit_status
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
                                bootbox.alert('Error: ' + error);
                            }
                        });

                        return false;
                    }
                }
            }
        });

        dialog.find('.modal-dialog').addClass('modal-lg');
    });

    // PDF Download without opening new tab
    $('.download-pdf').click(function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        
        // Create a hidden iframe to trigger download
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = url;
        document.body.appendChild(iframe);
        
        // Remove iframe after download starts
        setTimeout(function() {
            document.body.removeChild(iframe);
        }, 5000);
    });

    // Show selected filename next to Choose file
    $('.payment-pdf-actions .input-file-pdf').on('change', function() {
        var name = $(this).val().split(/\\|\//).pop() || '';
        $(this).siblings('.file-name').text(name ? (name.length > 12 ? name.substring(0, 10) + '…' : name) : '');
    });

    // Upload payment PDF (AJAX)
    $('.upload-payment-pdf-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var paymentId = form.data('payment-id');
        var paymentType = form.data('payment-type');
        var fileInput = form.find('input[type="file"]')[0];
        if (!fileInput || !fileInput.files || !fileInput.files.length) {
            bootbox.alert('<?= lang("please_select_pdf") ?>');
            return;
        }
        var fd = new FormData();
        fd.append('payment_id', paymentId);
        fd.append('payment_type', paymentType);
        fd.append('payment_pdf', fileInput.files[0]);
        fd.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');
        $.ajax({
            url: '<?= admin_url('tax_calculations/upload_payment_pdf') ?>',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.error == 0) {
                    bootbox.alert(res.msg, function() { location.reload(); });
                } else {
                    bootbox.alert(res.msg || '<?= lang("error") ?>');
                }
            },
            error: function() {
                bootbox.alert('<?= lang("request_failed") ?>');
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
                    <?php if ($tax_calculation): ?>
                    <a href="#" class="btn btn-success download-pdf"
                        data-url="<?= admin_url('tax_calculations/annual_tax_report_pdf?customer_id=' . $customer->id . '&year=' . $year) ?>">
                        <i class="fa fa-file-pdf-o"></i> <?= lang('annual_tax_report_pdf') ?>
                    </a>
                    <?php endif; ?>
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
                            <div class="pull-right" style="margin-top: -25px;">
                                <a href="#" class="btn btn-sm btn-success download-pdf"
                                    data-url="<?= admin_url('tax_calculations/annual_tax_report_pdf?customer_id=' . $customer->id . '&year=' . $year) ?>"
                                    title="<?= lang('download_conto_economico_pdf') ?>">
                                    <i class="fa fa-file-pdf-o"></i> <?= lang('annual_tax_report_pdf') ?>
                                </a>
                            </div>
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
                                            <td><?= $this->sma->formatMoney($tax_calculation->previous_year_inps); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('coefficient_of_profitability'); ?></strong></td>
                                            <td><?= number_format($tax_calculation->coefficient_used, 2); ?>%</td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('taxable_income'); ?></strong></td>
                                            <td><strong><?= $this->sma->formatMoney($tax_calculation->taxable_income); ?></strong>
                                            </td>
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
                                            <td><strong><?= $this->sma->formatMoney($tax_calculation->tax_due); ?></strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('advance_payments_made'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($tax_calculation->advance_payments_made); ?>
                                            </td>
                                        </tr>
                                        <tr class="info">
                                            <td><strong><?= lang('balance_payment'); ?></strong></td>
                                            <td><strong><?= $this->sma->formatMoney($tax_calculation->balance_payment); ?></strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('next_year_advance_base'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($tax_calculation->next_year_advance_base); ?>
                                            </td>
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
                                <small class="text-muted">(<?= lang('payments_due_in') ?>
                                    <?= $year + 1 ?>-<?= $year + 2 ?>)</small>
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
                                            <td><?= $this->sma->hrsd($payment->due_date); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->amount); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->paid_amount); ?></td>
                                            <td><?= $payment->paid_date ? $this->sma->hrsd($payment->paid_date) : '-'; ?>
                                            </td>
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
                                                    data-payment-id="<?= $payment->id ?>" data-payment-type="tax"
                                                    data-amount="<?= $payment->amount ?>">
                                                    <i class="fa fa-check"></i> <?= lang('mark_paid') ?>
                                                </a>
                                                <?php endif; ?>
                                                <a href="#" class="btn btn-xs btn-primary edit-payment"
                                                    data-payment-id="<?= $payment->id ?>" 
                                                    data-payment-type="tax"
                                                    data-amount="<?= $payment->amount ?>"
                                                    data-due-date="<?= $payment->due_date ?>"
                                                    data-paid-amount="<?= $payment->paid_amount ?>"
                                                    data-paid-date="<?= $payment->paid_date ?>"
                                                    data-status="<?= $payment->status ?>">
                                                    <i class="fa fa-edit"></i> <?= lang('edit') ?>
                                                </a>
                                                <?php if (!empty($can_download_payment_pdf)): ?>
                                                <div class="payment-pdf-actions">
                                                    <form class="upload-payment-pdf-form" data-payment-id="<?= $payment->id ?>" data-payment-type="tax">
                                                        <span class="file-input-wrap">
                                                            <input type="file" name="payment_pdf" accept=".pdf" class="input-file-pdf" id="pdf-file-tax-<?= $payment->id ?>">
                                                            <label for="pdf-file-tax-<?= $payment->id ?>" class="btn-file-label"><i class="fa fa-paperclip"></i> <?= lang('choose_file') ?></label>
                                                            <span class="file-name"></span>
                                                        </span>
                                                        <button type="submit" class="btn btn-upload-pdf"><i class="fa fa-upload"></i> <?= lang('upload_pdf') ?></button>
                                                        <?php if (!empty($payment->uploaded_pdf)): ?>
                                                        <a href="#" class="btn btn-download-pdf download-pdf"
                                                            data-url="<?= admin_url('tax_calculations/payment_pdf/' . $payment->id . '/tax') ?>">
                                                            <i class="fa fa-file-pdf-o"></i> <?= lang('pdf') ?>
                                                        </a>
                                                        <?php endif; ?>
                                                    </form>
                                                </div>
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
                <?php if (isset($inps_calculation) && $inps_calculation): ?>
                <div class="col-lg-12">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= lang('inps_calculation') ?> - <?= lang('year') ?>: <?= $year ?>
                            </h3>
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
                                            <td><strong><?= $this->sma->formatMoney($inps_calculation->inps_amount_after_discount); ?></strong>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <?php 
                            // Display slab breakdown if available
                            $slab_details = null;
                            if (!empty($inps_calculation->slab_details)) {
                                $slab_details = is_string($inps_calculation->slab_details) 
                                    ? json_decode($inps_calculation->slab_details, true) 
                                    : $inps_calculation->slab_details;
                            }
                            
                            if ($slab_details && is_array($slab_details) && count($slab_details) > 0): 
                            ?>
                            <div class="row" style="margin-top: 20px;">
                                <div class="col-md-12">
                                    <h4><?= lang('inps_slab_breakdown'); ?></h4>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center;"><?= lang('start'); ?></th>
                                                <th style="text-align: center;"><?= lang('end'); ?></th>
                                                <th style="text-align: right;"><?= lang('amount'); ?></th>
                                                <th style="text-align: right;"><?= lang('inps'); ?></th>
                                                <th style="text-align: center;"><?= lang('rate'); ?> (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $total_inps = 0;
                                            foreach ($slab_details as $slab): 
                                                $total_inps += $slab['tax'];
                                            ?>
                                            <tr>
                                                <td style="text-align: center;"><?= number_format($slab['from'], 2); ?>
                                                </td>
                                                <td style="text-align: center;"><?= number_format($slab['to'], 2); ?>
                                                </td>
                                                <td style="text-align: right;">
                                                    <?= $this->sma->formatMoney($slab['income']); ?></td>
                                                <td style="text-align: right;">
                                                    <?= $this->sma->formatMoney($slab['tax']); ?></td>
                                                <td style="text-align: center;">
                                                    <?php 
                                                    if ($slab['fixed_amount'] > 0 && $slab['rate'] == 0) {
                                                        echo '<span class="label label-info">' . lang('fixed') . '</span>';
                                                    } else {
                                                        echo number_format($slab['rate'], 2);
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="success">
                                                <td colspan="3" style="text-align: right;">
                                                    <strong><?= lang('total'); ?></strong>
                                                </td>
                                                <td style="text-align: right;">
                                                    <strong><?= $this->sma->formatMoney($total_inps); ?></strong>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- INPS Payments Section -->
                <?php if (!empty($inps_payments)): ?>
                <div class="col-lg-12">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= lang('inps_payments') ?> - <?= lang('year') ?>: <?= $year ?>
                            </h3>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= lang('payment_type'); ?></th>
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
                                            <td><?= !empty($payment->notes) ? $payment->notes : $payment->installment_number . '° Rata'; ?></td>
                                            <td><?= $this->sma->hrsd($payment->due_date); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->amount); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->paid_amount); ?></td>
                                            <td><?= $payment->paid_date ? $this->sma->hrsd($payment->paid_date) : '-'; ?>
                                            </td>
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
                                                    data-payment-id="<?= $payment->id ?>" data-payment-type="inps"
                                                    data-amount="<?= $payment->amount ?>">
                                                    <i class="fa fa-check"></i> <?= lang('mark_paid') ?>
                                                </a>
                                                <?php endif; ?>
                                                <a href="#" class="btn btn-xs btn-primary edit-payment"
                                                    data-payment-id="<?= $payment->id ?>" 
                                                    data-payment-type="inps"
                                                    data-amount="<?= $payment->amount ?>"
                                                    data-due-date="<?= $payment->due_date ?>"
                                                    data-paid-amount="<?= $payment->paid_amount ?>"
                                                    data-paid-date="<?= $payment->paid_date ?>"
                                                    data-status="<?= $payment->status ?>">
                                                    <i class="fa fa-edit"></i> <?= lang('edit') ?>
                                                </a>
                                                <?php if (!empty($can_download_payment_pdf)): ?>
                                                <div class="payment-pdf-actions">
                                                    <form class="upload-payment-pdf-form" data-payment-id="<?= $payment->id ?>" data-payment-type="inps">
                                                        <span class="file-input-wrap">
                                                            <input type="file" name="payment_pdf" accept=".pdf" class="input-file-pdf" id="pdf-file-inps-<?= $payment->id ?>">
                                                            <label for="pdf-file-inps-<?= $payment->id ?>" class="btn-file-label"><i class="fa fa-paperclip"></i> <?= lang('choose_file') ?></label>
                                                            <span class="file-name"></span>
                                                        </span>
                                                        <button type="submit" class="btn btn-upload-pdf"><i class="fa fa-upload"></i> <?= lang('upload_pdf') ?></button>
                                                        <?php if (!empty($payment->uploaded_pdf)): ?>
                                                        <a href="#" class="btn btn-download-pdf download-pdf"
                                                            data-url="<?= admin_url('tax_calculations/payment_pdf/' . $payment->id . '/inps') ?>">
                                                            <i class="fa fa-file-pdf-o"></i> <?= lang('pdf') ?>
                                                        </a>
                                                        <?php endif; ?>
                                                    </form>
                                                </div>
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

                <!-- INAIL Calculation Section (Only for Artigiani) -->
                <?php if ($inail_calculation): ?>
                <div class="col-lg-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= lang('inail_calculation') ?> - <?= lang('year') ?>: <?= $year ?>
                            </h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <td><strong><?= lang('taxable_income'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($inail_calculation->taxable_income); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('inail_coefficient'); ?></strong></td>
                                            <td><?= number_format($inail_calculation->inail_coefficient, 2); ?>%</td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('inail_base_amount'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($inail_calculation->inail_base_amount); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('inail_rate'); ?></strong></td>
                                            <td><?= number_format($inail_calculation->inail_rate, 2); ?>%</td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('inail_calculated_amount'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($inail_calculation->inail_calculated_amount); ?>
                                            </td>
                                        </tr>
                                        <?php if ($inail_calculation->inail_calculated_amount < $inail_calculation->inail_minimum_payment): ?>
                                        <tr class="warning">
                                            <td><strong><?= lang('inail_minimum_payment'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($inail_calculation->inail_minimum_payment); ?>
                                                <small>(<?= lang('minimum_applied'); ?>)</small>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($inail_calculation->ateco_code): ?>
                                        <tr>
                                            <td><strong><?= lang('ateco_code'); ?></strong></td>
                                            <td><?= $inail_calculation->ateco_code; ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr class="success">
                                            <td><strong><?= lang('inail_final_amount'); ?></strong></td>
                                            <td><strong><?= $this->sma->formatMoney($inail_calculation->inail_final_amount); ?></strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('payment_frequency'); ?></strong></td>
                                            <td><?= lang('one_payment_per_year'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('due_date'); ?></strong></td>
                                            <td><?= lang('february_16_following_year'); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- INAIL Payments Section -->
                <?php if (!empty($inail_payments)): ?>
                <div class="col-lg-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= lang('inail_payments') ?> - <?= lang('year') ?>: <?= $year ?>
                            </h3>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= lang('due_date'); ?></th>
                                            <th><?= lang('amount'); ?></th>
                                            <th><?= lang('paid_amount'); ?></th>
                                            <th><?= lang('paid_date'); ?></th>
                                            <th><?= lang('status'); ?></th>
                                            <th><?= lang('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inail_payments as $payment): ?>
                                        <tr>
                                            <td><?= $this->sma->hrsd($payment->due_date); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->amount); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->paid_amount); ?></td>
                                            <td><?= $payment->paid_date ? $this->sma->hrsd($payment->paid_date) : '-'; ?>
                                            </td>
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
                                                    data-payment-id="<?= $payment->id ?>" data-payment-type="inail"
                                                    data-amount="<?= $payment->amount ?>">
                                                    <i class="fa fa-check"></i> <?= lang('mark_paid') ?>
                                                </a>
                                                <?php endif; ?>
                                                <a href="#" class="btn btn-xs btn-primary edit-payment"
                                                    data-payment-id="<?= $payment->id ?>" 
                                                    data-payment-type="inail"
                                                    data-amount="<?= $payment->amount ?>"
                                                    data-due-date="<?= $payment->due_date ?>"
                                                    data-paid-amount="<?= $payment->paid_amount ?>"
                                                    data-paid-date="<?= $payment->paid_date ?>"
                                                    data-status="<?= $payment->status ?>">
                                                    <i class="fa fa-edit"></i> <?= lang('edit') ?>
                                                </a>
                                                <?php if (!empty($can_download_payment_pdf)): ?>
                                                <div class="payment-pdf-actions">
                                                    <form class="upload-payment-pdf-form" data-payment-id="<?= $payment->id ?>" data-payment-type="inail">
                                                        <span class="file-input-wrap">
                                                            <input type="file" name="payment_pdf" accept=".pdf" class="input-file-pdf" id="pdf-file-inail-<?= $payment->id ?>">
                                                            <label for="pdf-file-inail-<?= $payment->id ?>" class="btn-file-label"><i class="fa fa-paperclip"></i> <?= lang('choose_file') ?></label>
                                                            <span class="file-name"></span>
                                                        </span>
                                                        <button type="submit" class="btn btn-upload-pdf"><i class="fa fa-upload"></i> <?= lang('upload_pdf') ?></button>
                                                        <?php if (!empty($payment->uploaded_pdf)): ?>
                                                        <a href="#" class="btn btn-download-pdf download-pdf"
                                                            data-url="<?= admin_url('tax_calculations/payment_pdf/' . $payment->id . '/inail') ?>">
                                                            <i class="fa fa-file-pdf-o"></i> <?= lang('pdf') ?>
                                                        </a>
                                                        <?php endif; ?>
                                                    </form>
                                                </div>
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

                <!-- Diritto Annuale Payments Section (for Artigiani and Commercianti) -->
                <?php if (!empty($diritto_annuale_payments) && in_array($customer->customer_type, array('Artigiani', 'Commercianti'))): ?>
                <div class="col-lg-12">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= lang('diritto_annuale_payments') ?> - <?= lang('year') ?>:
                                <?= $year ?></h3>
                        </div>
                        <div class="panel-body">
                            <p class="text-muted">
                                <i class="fa fa-info-circle"></i> <?= lang('diritto_annuale_info'); ?>
                            </p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= lang('due_date'); ?></th>
                                            <th><?= lang('amount'); ?></th>
                                            <th><?= lang('paid_amount'); ?></th>
                                            <th><?= lang('paid_date'); ?></th>
                                            <th><?= lang('status'); ?></th>
                                            <th><?= lang('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($diritto_annuale_payments as $payment): ?>
                                        <tr>
                                            <td><?= $this->sma->hrsd($payment->due_date); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->amount); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->paid_amount); ?></td>
                                            <td><?= $payment->paid_date ? $this->sma->hrsd($payment->paid_date) : '-'; ?>
                                            </td>
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
                                                    data-payment-type="diritto_annuale"
                                                    data-amount="<?= $payment->amount ?>">
                                                    <i class="fa fa-check"></i> <?= lang('mark_paid') ?>
                                                </a>
                                                <?php endif; ?>
                                                <a href="#" class="btn btn-xs btn-primary edit-payment"
                                                    data-payment-id="<?= $payment->id ?>" 
                                                    data-payment-type="diritto_annuale"
                                                    data-amount="<?= $payment->amount ?>"
                                                    data-due-date="<?= $payment->due_date ?>"
                                                    data-paid-amount="<?= $payment->paid_amount ?>"
                                                    data-paid-date="<?= $payment->paid_date ?>"
                                                    data-status="<?= $payment->status ?>">
                                                    <i class="fa fa-edit"></i> <?= lang('edit') ?>
                                                </a>
                                                <?php if (!empty($can_download_payment_pdf)): ?>
                                                <div class="payment-pdf-actions">
                                                    <form class="upload-payment-pdf-form" data-payment-id="<?= $payment->id ?>" data-payment-type="diritto_annuale">
                                                        <span class="file-input-wrap">
                                                            <input type="file" name="payment_pdf" accept=".pdf" class="input-file-pdf" id="pdf-file-diritto-<?= $payment->id ?>">
                                                            <label for="pdf-file-diritto-<?= $payment->id ?>" class="btn-file-label"><i class="fa fa-paperclip"></i> <?= lang('choose_file') ?></label>
                                                            <span class="file-name"></span>
                                                        </span>
                                                        <button type="submit" class="btn btn-upload-pdf"><i class="fa fa-upload"></i> <?= lang('upload_pdf') ?></button>
                                                        <?php if (!empty($payment->uploaded_pdf)): ?>
                                                        <a href="#" class="btn btn-download-pdf download-pdf"
                                                            data-url="<?= admin_url('tax_calculations/payment_pdf/' . $payment->id . '/diritto_annuale') ?>">
                                                            <i class="fa fa-file-pdf-o"></i> <?= lang('pdf') ?>
                                                        </a>
                                                        <?php endif; ?>
                                                    </form>
                                                </div>
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

                <!-- Fattura Tra Privati Section -->
                <?php if (!empty($fattura_tra_privati_calculation) && $fattura_tra_privati_calculation->total_invoices > 0): ?>
                <div class="col-lg-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= lang('fattura_tra_privati_calculation') ?> -
                                <?= lang('year') ?>: <?= $year ?></h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <td><strong><?= lang('total_invoices'); ?></strong></td>
                                            <td><?= number_format($fattura_tra_privati_calculation->total_invoices, 0); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('total_sales_amount'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($fattura_tra_privati_calculation->total_sales_amount); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('minimum_invoice_amount'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($fattura_tra_privati_calculation->minimum_invoice_amount); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('payment_per_invoice'); ?></strong></td>
                                            <td><?= $this->sma->formatMoney($fattura_tra_privati_calculation->payment_per_invoice); ?>
                                            </td>
                                        </tr>
                                        <tr class="success">
                                            <td><strong><?= lang('total_payment_amount'); ?></strong></td>
                                            <td><strong><?= $this->sma->formatMoney($fattura_tra_privati_calculation->total_payment_amount); ?></strong>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <td><strong><?= lang('payment_frequency'); ?></strong></td>
                                            <td><?= lang('one_payment_per_year'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('due_date'); ?></strong></td>
                                            <td><?= lang('february_16_following_year'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= lang('calculation_basis'); ?></strong></td>
                                            <td><?= lang('fattura_tra_privati_basis'); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Fattura Tra Privati Payments Section -->
                <?php if (!empty($fattura_tra_privati_payments)): ?>
                <div class="col-lg-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= lang('fattura_tra_privati_payments') ?> - <?= lang('year') ?>:
                                <?= $year ?></h3>
                        </div>
                        <div class="panel-body">
                            <p class="text-muted">
                                <i class="fa fa-info-circle"></i> <?= lang('fattura_tra_privati_info'); ?>
                            </p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= lang('due_date'); ?></th>
                                            <th><?= lang('amount'); ?></th>
                                            <th><?= lang('paid_amount'); ?></th>
                                            <th><?= lang('paid_date'); ?></th>
                                            <th><?= lang('status'); ?></th>
                                            <th><?= lang('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($fattura_tra_privati_payments as $payment): ?>
                                        <tr>
                                            <td><?= $this->sma->hrsd($payment->due_date); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->amount); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->paid_amount); ?></td>
                                            <td><?= $payment->paid_date ? $this->sma->hrsd($payment->paid_date) : '-'; ?>
                                            </td>
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
                                                    data-payment-type="fattura_tra_privati"
                                                    data-amount="<?= $payment->amount ?>">
                                                    <i class="fa fa-check"></i> <?= lang('mark_paid') ?>
                                                </a>
                                                <?php endif; ?>
                                                <a href="#" class="btn btn-xs btn-primary edit-payment"
                                                    data-payment-id="<?= $payment->id ?>" 
                                                    data-payment-type="fattura_tra_privati"
                                                    data-amount="<?= $payment->amount ?>"
                                                    data-due-date="<?= $payment->due_date ?>"
                                                    data-paid-amount="<?= $payment->paid_amount ?>"
                                                    data-paid-date="<?= $payment->paid_date ?>"
                                                    data-status="<?= $payment->status ?>">
                                                    <i class="fa fa-edit"></i> <?= lang('edit') ?>
                                                </a>
                                                <?php if (!empty($can_download_payment_pdf)): ?>
                                                <div class="payment-pdf-actions">
                                                    <form class="upload-payment-pdf-form" data-payment-id="<?= $payment->id ?>" data-payment-type="fattura_tra_privati">
                                                        <span class="file-input-wrap">
                                                            <input type="file" name="payment_pdf" accept=".pdf" class="input-file-pdf" id="pdf-file-fattura-<?= $payment->id ?>">
                                                            <label for="pdf-file-fattura-<?= $payment->id ?>" class="btn-file-label"><i class="fa fa-paperclip"></i> <?= lang('choose_file') ?></label>
                                                            <span class="file-name"></span>
                                                        </span>
                                                        <button type="submit" class="btn btn-upload-pdf"><i class="fa fa-upload"></i> <?= lang('upload_pdf') ?></button>
                                                        <?php if (!empty($payment->uploaded_pdf)): ?>
                                                        <a href="#" class="btn btn-download-pdf download-pdf"
                                                            data-url="<?= admin_url('tax_calculations/payment_pdf/' . $payment->id . '/fattura_tra_privati') ?>">
                                                            <i class="fa fa-file-pdf-o"></i> <?= lang('pdf') ?>
                                                        </a>
                                                        <?php endif; ?>
                                                    </form>
                                                </div>
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
                        <a href="#" class="calculate-tax-alert"
                            data-customer-id="<?= $customer->id ?>"><?= lang('calculate_now') ?></a>
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