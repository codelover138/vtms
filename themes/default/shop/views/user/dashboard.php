<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- Dashboard CSS is loaded from external file: themes/default/shop/assets/css/dashboard.css -->

<!-- Dashboard Loader -->
<div class="dashboard-loader" id="dashboard-loader">
    <div class="loader-spinner"></div>
    <div class="loader-text"><?= lang('loading_data') ?>...</div>
</div>

<script>
$(document).ready(function() {
    // Hide loader when page is fully loaded
    $(window).on('load', function() {
        setTimeout(function() {
            $('#dashboard-loader').addClass('hidden');
            setTimeout(function() {
                $('#dashboard-loader').remove();
            }, 300);
        }, 500);
    });
    
    // Show loader when year selector changes
    $('#year-selector').change(function() {
        // Show loader
        if ($('#dashboard-loader').length === 0) {
            $('body').append('<div class="dashboard-loader" id="dashboard-loader"><div class="loader-spinner"></div><div class="loader-text"><?= lang('loading_data') ?>...</div></div>');
        } else {
            $('#dashboard-loader').removeClass('hidden');
        }
        
        var year = $(this).val();
        window.location.href = '<?= site_url("dashboard") ?>?year=' + year;
    });
    
    // Tab functionality
    $('.tab').click(function() {
        var target = $(this).data('target');
        $('.tab').removeClass('active');
        $('.tab-content').removeClass('active');
        $(this).addClass('active');
        $('#' + target).addClass('active');
    });
    
    // If page is already loaded, hide loader immediately
    if (document.readyState === 'complete') {
        setTimeout(function() {
            $('#dashboard-loader').addClass('hidden');
            setTimeout(function() {
                $('#dashboard-loader').remove();
            }, 300);
        }, 500);
    }
});
</script>

<div class="dashboard-container">
    <!-- Premium Welcome Header -->
    <div class="dashboard-header">
        <div class="welcome-decoration">
            <i class="fa fa-star"></i>
        </div>
        <div class="welcome-content">
            <div class="welcome-greeting">
                <i class="fa fa-hand-spock-o"></i>
                <span><?= lang('welcome') ?></span>
            </div>
            <h1>
                <span class="user-name"><?= $user->first_name . ' ' . $user->last_name ?></span>!
            </h1>
            <p class="welcome-subtitle">
                <i class="fa fa-chart-line"></i>
                <?= lang('dashboard_overview') ?>
            </p>
            <div class="welcome-stats">
                <div class="welcome-stat-item">
                    <i class="fa fa-calendar-check-o"></i>
                    <div class="stat-text">
                        <span class="stat-label"><?= lang('year') ?></span>
                        <span class="stat-value"><?= $tax_year ?></span>
                    </div>
                </div>
                <div class="welcome-stat-item">
                    <i class="fa fa-file-text-o"></i>
                    <div class="stat-text">
                        <span class="stat-label"><?= lang('calculations') ?></span>
                        <span class="stat-value"><?= count($tax_calculations) ?></span>
                    </div>
                </div>
                <div class="welcome-stat-item">
                    <i class="fa fa-check-circle"></i>
                    <div class="stat-text">
                        <span class="stat-label"><?= lang('status') ?></span>
                        <span class="stat-value"><?= lang('active') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Year Selector -->
    <div class="year-selector-container">
        <label for="year-selector"><i class="fa fa-calendar"></i> <?= lang('select_year') ?>:</label>
        <select id="year-selector" name="year">
            <?php 
            $available_years = array();
            foreach ($tax_calculations as $calc) {
                $available_years[] = $calc->tax_year;
            }
            if (empty($available_years)) {
                $available_years[] = date('Y');
            }
            $available_years = array_unique($available_years);
            rsort($available_years);
            for ($y = date('Y'); $y >= date('Y') - 5; $y--): 
            ?>
            <option value="<?= $y ?>" <?= $tax_year == $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <!-- Income Prediction Card -->
    <?php if (isset($income_prediction) && $income_prediction && $income_prediction['missing_months_count'] > 0): ?>
    <div class="dashboard-section" style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border-left: 5px solid #667eea;">
        <div class="section-header">
            <h3><i class="fa fa-line-chart"></i> <?= lang('income_prediction') ?></h3>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="info-card" style="background: white;">
                    <div class="info-card-label"><?= lang('current_taxable_income') ?></div>
                    <div class="info-card-value"><?= $this->sma->formatMoney($income_prediction['current_taxable_income']) ?></div>
                    <small style="color: #718096; font-size: 12px;">
                        <?= lang('based_on') ?> <?= $income_prediction['total_months_with_data'] ?> <?= lang('months_of_data') ?>
                    </small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="info-card-label" style="color: rgba(255,255,255,0.9);"><?= lang('predicted_taxable_income') ?></div>
                    <div class="info-card-value" style="color: white; font-size: 32px;"><?= $this->sma->formatMoney($income_prediction['predicted_taxable_income']) ?></div>
                    <small style="color: rgba(255,255,255,0.8); font-size: 12px;">
                        <?= lang('if_all_months_completed') ?>
                    </small>
                </div>
            </div>
        </div>
        <div style="margin-top: 20px; padding: 20px; background: white; border-radius: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <strong style="color: #2d3748;"><?= lang('data_status') ?>:</strong>
                <span style="padding: 6px 12px; background: #fff3cd; color: #856404; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    <?= $income_prediction['total_months_with_data'] ?>/12 <?= lang('months_entered') ?>
                </span>
            </div>
            <div style="margin-bottom: 15px;">
                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                    <?php 
                    $all_months = range(1, 12);
                    $month_names = array(1 => lang('jan'), 2 => lang('feb'), 3 => lang('mar'), 4 => lang('apr'), 
                                        5 => lang('may'), 6 => lang('jun'), 7 => lang('jul'), 8 => lang('aug'),
                                        9 => lang('sep'), 10 => lang('oct'), 11 => lang('nov'), 12 => lang('dec'));
                    foreach ($all_months as $month): 
                        $has_data = in_array($month, $income_prediction['months_with_data']);
                    ?>
                    <span style="padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; 
                                  <?= $has_data ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;' ?>">
                        <?= isset($month_names[$month]) ? $month_names[$month] : $month ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #667eea;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: #718096; font-size: 14px;"><?= lang('average_monthly_sales') ?>:</span>
                    <strong style="color: #2d3748;"><?= $this->sma->formatMoney($income_prediction['average_monthly_sales']) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: #718096; font-size: 14px;"><?= lang('predicted_additional_sales') ?>:</span>
                    <strong style="color: #667eea;"><?= $this->sma->formatMoney($income_prediction['predicted_additional_sales']) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #718096; font-size: 14px;"><?= lang('predicted_total_sales') ?>:</span>
                    <strong style="color: #28a745; font-size: 16px;"><?= $this->sma->formatMoney($income_prediction['predicted_total_sales']) ?></strong>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-card-header">
                <div>
                    <div class="stat-label"><?= lang('total_due') ?></div>
                    <div class="stat-value"><?= $this->sma->formatMoney($total_due) ?></div>
                </div>
                <div class="stat-icon"><i class="fa fa-euro"></i></div>
            </div>
        </div>
        <div class="stat-card danger">
            <div class="stat-card-header">
                <div>
                    <div class="stat-label"><?= lang('overdue') ?></div>
                    <div class="stat-value"><?= $this->sma->formatMoney($total_overdue) ?></div>
                </div>
                <div class="stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-card-header">
                <div>
                    <div class="stat-label"><?= lang('upcoming_payments') ?></div>
                    <div class="stat-value"><?= $this->sma->formatMoney($total_upcoming) ?></div>
                </div>
                <div class="stat-icon"><i class="fa fa-calendar"></i></div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-card-header">
                <div>
                    <div class="stat-label"><?= lang('total_paid') ?></div>
                    <div class="stat-value"><?= $this->sma->formatMoney($total_paid) ?></div>
                </div>
                <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    <!-- Tax Information Tabs -->
    <div class="dashboard-section">
        <div class="tabs-container">
            <div class="tabs">
                <button class="tab active" data-target="tab-tax"><i class="fa fa-calculator"></i> <?= lang('tax_calculation') ?></button>
                <button class="tab" data-target="tab-inps"><i class="fa fa-file-text"></i> INPS</button>
                <?php if ($inail_calculation): ?>
                <button class="tab" data-target="tab-inail"><i class="fa fa-shield"></i> INAIL</button>
                <?php endif; ?>
                <?php if (!empty($diritto_annuale_payments)): ?>
                <button class="tab" data-target="tab-diritto"><i class="fa fa-certificate"></i> <?= lang('diritto_annuale_payments') ?></button>
                <?php endif; ?>
                <?php if ($fattura_tra_privati_calculation): ?>
                <button class="tab" data-target="tab-fattura"><i class="fa fa-file-invoice"></i> <?= lang('fattura_tra_privati_calculation') ?></button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tax Calculation Tab -->
        <div id="tab-tax" class="tab-content active">
            <?php 
            $display_calc = isset($tax_calculation) && $tax_calculation ? $tax_calculation : (isset($latest_tax_calculation) && $latest_tax_calculation ? $latest_tax_calculation : null);
            if ($display_calc): 
            ?>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-card-label"><?= lang('total_sales') ?></div>
                    <div class="info-card-value"><?= $this->sma->formatMoney($display_calc->total_sales) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('taxable_income') ?></div>
                    <div class="info-card-value highlight"><?= $this->sma->formatMoney($display_calc->taxable_income) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('tax_due') ?></div>
                    <div class="info-card-value highlight"><?= $this->sma->formatMoney($display_calc->tax_due) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('advance_payments_made') ?></div>
                    <div class="info-card-value"><?= $this->sma->formatMoney($display_calc->advance_payments_made) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('balance_payment') ?></div>
                    <div class="info-card-value" style="color: #dc3545;"><?= $this->sma->formatMoney($display_calc->balance_payment) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('coefficient_used') ?></div>
                    <div class="info-card-value"><?= number_format($display_calc->coefficient_used, 2) ?>%</div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('tax_rate_used') ?></div>
                    <div class="info-card-value"><?= number_format($display_calc->tax_rate_used, 2) ?>%</div>
                </div>
                <?php if (isset($display_calc->previous_year_inps) && $display_calc->previous_year_inps > 0): ?>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('previous_year_inps') ?></div>
                    <div class="info-card-value"><?= $this->sma->formatMoney($display_calc->previous_year_inps) ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-calculator"></i>
                <h4><?= lang('no_tax_calculations') ?></h4>
                <p><?= lang('no_tax_calculations_message') ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- INPS Tab -->
        <div id="tab-inps" class="tab-content">
            <?php if (isset($inps_calculation) && $inps_calculation): ?>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-card-label"><?= lang('inps_taxable_income') ?></div>
                    <div class="info-card-value highlight"><?= $this->sma->formatMoney($inps_calculation->taxable_income) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('inps_rate') ?></div>
                    <div class="info-card-value"><?= number_format($inps_calculation->inps_rate, 2) ?>%</div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('inps_amount') ?></div>
                    <div class="info-card-value highlight"><?= $this->sma->formatMoney($inps_calculation->inps_amount) ?></div>
                </div>
                <?php if ($inps_calculation->discount_percentage > 0): ?>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('discount_percentage') ?></div>
                    <div class="info-card-value"><?= number_format($inps_calculation->discount_percentage, 2) ?>%</div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('discount_amount') ?></div>
                    <div class="info-card-value" style="color: #28a745;">-<?= $this->sma->formatMoney($inps_calculation->discount_amount) ?></div>
                </div>
                <?php endif; ?>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('inps_amount_after_discount') ?></div>
                    <div class="info-card-value highlight"><?= $this->sma->formatMoney($inps_calculation->inps_amount_after_discount) ?></div>
                </div>
            </div>
            <?php if (!empty($inps_payments)): ?>
            <div class="payment-section">
                <div class="payment-category-title">
                    <i class="fa fa-calendar"></i> <?= lang('inps_payments') ?>
                </div>
                <?php foreach ($inps_payments as $payment): ?>
                <div class="payment-item <?= $payment->status == 'paid' ? 'paid' : (strtotime($payment->due_date) < strtotime(date('Y-m-d')) ? 'overdue' : 'upcoming') ?>">
                    <div class="payment-info">
                        <div class="payment-type"><?= lang('inps_payments') ?> - <?= lang('installment') ?> #<?= $payment->installment_number ?></div>
                        <div class="payment-details">
                            <span><i class="fa fa-calendar"></i> <?= lang('due_date') ?>: <?= date('d M Y', strtotime($payment->due_date)) ?></span>
                            <?php if ($payment->paid_date): ?>
                            <span><i class="fa fa-check"></i> <?= lang('paid_date') ?>: <?= date('d M Y', strtotime($payment->paid_date)) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="payment-amount"><?= $this->sma->formatMoney($payment->amount) ?></div>
                    <span class="payment-status <?= $payment->status ?>"><?= lang($payment->status) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-file-text"></i>
                <h4><?= lang('no_inps_calculation') ?></h4>
                <p><?= lang('no_inps_calculation_message') ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- INAIL Tab -->
        <?php if (isset($inail_calculation) && $inail_calculation): ?>
        <div id="tab-inail" class="tab-content">
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-card-label"><?= lang('inail_base_amount') ?></div>
                    <div class="info-card-value highlight"><?= $this->sma->formatMoney($inail_calculation->inail_base_amount) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('inail_rate') ?></div>
                    <div class="info-card-value"><?= number_format($inail_calculation->inail_rate, 2) ?>%</div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('inail_calculated_amount') ?></div>
                    <div class="info-card-value highlight"><?= $this->sma->formatMoney($inail_calculation->inail_calculated_amount) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('inail_minimum_payment') ?></div>
                    <div class="info-card-value"><?= $this->sma->formatMoney($inail_calculation->inail_minimum_payment) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('inail_final_amount') ?></div>
                    <div class="info-card-value highlight"><?= $this->sma->formatMoney($inail_calculation->inail_final_amount) ?></div>
                </div>
                <?php if ($inail_calculation->ateco_code): ?>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('ateco_code') ?></div>
                    <div class="info-card-value"><?= $inail_calculation->ateco_code ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($inail_payments)): ?>
            <div class="payment-section">
                <div class="payment-category-title">
                    <i class="fa fa-calendar"></i> <?= lang('inail_payments') ?>
                </div>
                <?php foreach ($inail_payments as $payment): ?>
                <div class="payment-item <?= $payment->status == 'paid' ? 'paid' : (strtotime($payment->due_date) < strtotime(date('Y-m-d')) ? 'overdue' : 'upcoming') ?>">
                    <div class="payment-info">
                        <div class="payment-type"><?= lang('inail_payments') ?></div>
                        <div class="payment-details">
                            <span><i class="fa fa-calendar"></i> <?= lang('due_date') ?>: <?= date('d M Y', strtotime($payment->due_date)) ?></span>
                            <?php if ($payment->paid_date): ?>
                            <span><i class="fa fa-check"></i> <?= lang('paid_date') ?>: <?= date('d M Y', strtotime($payment->paid_date)) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="payment-amount"><?= $this->sma->formatMoney($payment->amount) ?></div>
                    <span class="payment-status <?= $payment->status ?>"><?= lang($payment->status) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Diritto Annuale Tab -->
        <?php if (!empty($diritto_annuale_payments)): ?>
        <div id="tab-diritto" class="tab-content">
            <div class="payment-section">
                <div class="payment-category-title">
                    <i class="fa fa-certificate"></i> <?= lang('diritto_annuale_payments') ?>
                </div>
                <?php foreach ($diritto_annuale_payments as $payment): ?>
                <div class="payment-item <?= $payment->status == 'paid' ? 'paid' : (strtotime($payment->due_date) < strtotime(date('Y-m-d')) ? 'overdue' : 'upcoming') ?>">
                    <div class="payment-info">
                        <div class="payment-type"><?= lang('diritto_annuale_payments') ?></div>
                        <div class="payment-details">
                            <span><i class="fa fa-calendar"></i> <?= lang('due_date') ?>: <?= date('d M Y', strtotime($payment->due_date)) ?></span>
                            <?php if ($payment->paid_date): ?>
                            <span><i class="fa fa-check"></i> <?= lang('paid_date') ?>: <?= date('d M Y', strtotime($payment->paid_date)) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="payment-amount"><?= $this->sma->formatMoney($payment->amount) ?></div>
                    <span class="payment-status <?= $payment->status ?>"><?= lang($payment->status) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fattura Tra Privati Tab -->
        <?php if (isset($fattura_tra_privati_calculation) && $fattura_tra_privati_calculation): ?>
        <div id="tab-fattura" class="tab-content">
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-card-label"><?= lang('total_invoices') ?></div>
                    <div class="info-card-value highlight"><?= $fattura_tra_privati_calculation->total_invoices ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('payment_per_invoice') ?></div>
                    <div class="info-card-value"><?= $this->sma->formatMoney($fattura_tra_privati_calculation->payment_per_invoice) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label"><?= lang('total_amount') ?></div>
                    <div class="info-card-value highlight"><?= $this->sma->formatMoney($fattura_tra_privati_calculation->total_amount) ?></div>
                </div>
            </div>
            <?php if (!empty($fattura_tra_privati_payments)): ?>
            <div class="payment-section">
                <div class="payment-category-title">
                    <i class="fa fa-calendar"></i> <?= lang('fattura_tra_privati_payments') ?>
                </div>
                <?php foreach ($fattura_tra_privati_payments as $payment): ?>
                <div class="payment-item <?= $payment->status == 'paid' ? 'paid' : (strtotime($payment->due_date) < strtotime(date('Y-m-d')) ? 'overdue' : 'upcoming') ?>">
                    <div class="payment-info">
                        <div class="payment-type"><?= lang('fattura_tra_privati_payments') ?></div>
                        <div class="payment-details">
                            <span><i class="fa fa-calendar"></i> <?= lang('due_date') ?>: <?= date('d M Y', strtotime($payment->due_date)) ?></span>
                            <?php if ($payment->paid_date): ?>
                            <span><i class="fa fa-check"></i> <?= lang('paid_date') ?>: <?= date('d M Y', strtotime($payment->paid_date)) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="payment-amount"><?= $this->sma->formatMoney($payment->amount) ?></div>
                    <span class="payment-status <?= $payment->status ?>"><?= lang($payment->status) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Payment Schedule -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3><i class="fa fa-calendar-check-o"></i> <?= lang('payment_schedule') ?></h3>
        </div>
        
        <!-- Overdue Payments -->
        <?php if (!empty($overdue_payments)): ?>
        <div class="payment-category">
            <div class="payment-category-title overdue">
                <i class="fa fa-exclamation-triangle"></i> <?= lang('overdue_payments') ?> (<?= count($overdue_payments) ?>)
            </div>
            <?php foreach ($overdue_payments as $payment): ?>
            <div class="payment-item overdue">
                <div class="payment-info">
                    <div class="payment-type">
                        <?php 
                        $payment_type_label = ucfirst(str_replace('_', ' ', $payment->payment_type));
                        if (isset($payment->installment_number)) {
                            $payment_type_label .= ' - ' . lang('installment') . ' #' . $payment->installment_number;
                        }
                        echo $payment_type_label;
                        ?>
                    </div>
                    <div class="payment-details">
                        <span><i class="fa fa-calendar"></i> <?= lang('due_date') ?>: <?= date('d M Y', strtotime($payment->due_date)) ?></span>
                        <?php if (isset($payment->payment_year)): ?>
                        <span><i class="fa fa-calendar-o"></i> <?= lang('year') ?>: <?= $payment->payment_year ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="payment-amount"><?= $this->sma->formatMoney($payment->amount) ?></div>
                <span class="payment-status overdue"><?= lang('overdue') ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Upcoming Payments -->
        <?php if (!empty($upcoming_payments)): ?>
        <div class="payment-category">
            <div class="payment-category-title upcoming">
                <i class="fa fa-clock-o"></i> <?= lang('upcoming_payments') ?> (<?= count($upcoming_payments) ?>)
            </div>
            <?php foreach ($upcoming_payments as $payment): ?>
            <div class="payment-item upcoming">
                <div class="payment-info">
                    <div class="payment-type">
                        <?php 
                        $payment_type_label = ucfirst(str_replace('_', ' ', $payment->payment_type));
                        if (isset($payment->installment_number)) {
                            $payment_type_label .= ' - ' . lang('installment') . ' #' . $payment->installment_number;
                        }
                        echo $payment_type_label;
                        ?>
                    </div>
                    <div class="payment-details">
                        <span><i class="fa fa-calendar"></i> <?= lang('due_date') ?>: <?= date('d M Y', strtotime($payment->due_date)) ?></span>
                        <?php if (isset($payment->payment_year)): ?>
                        <span><i class="fa fa-calendar-o"></i> <?= lang('year') ?>: <?= $payment->payment_year ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="payment-amount"><?= $this->sma->formatMoney($payment->amount) ?></div>
                <span class="payment-status pending"><?= lang('pending') ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Paid Payments -->
        <?php if (!empty($paid_payments)): ?>
        <div class="payment-category">
            <div class="payment-category-title paid">
                <i class="fa fa-check-circle"></i> <?= lang('paid_payments') ?> (<?= count($paid_payments) ?>)
            </div>
            <?php foreach (array_slice($paid_payments, 0, 10) as $payment): ?>
            <div class="payment-item paid">
                <div class="payment-info">
                    <div class="payment-type">
                        <?php 
                        $payment_type_label = ucfirst(str_replace('_', ' ', $payment->payment_type));
                        if (isset($payment->installment_number)) {
                            $payment_type_label .= ' - ' . lang('installment') . ' #' . $payment->installment_number;
                        }
                        echo $payment_type_label;
                        ?>
                    </div>
                    <div class="payment-details">
                        <?php if (isset($payment->paid_date) && $payment->paid_date): ?>
                        <span><i class="fa fa-check"></i> <?= lang('paid_date') ?>: <?= date('d M Y', strtotime($payment->paid_date)) ?></span>
                        <?php endif; ?>
                        <?php if (isset($payment->payment_year)): ?>
                        <span><i class="fa fa-calendar-o"></i> <?= lang('year') ?>: <?= $payment->payment_year ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="payment-amount"><?= $this->sma->formatMoney($payment->paid_amount ? $payment->paid_amount : $payment->amount) ?></div>
                <span class="payment-status paid"><?= lang('paid') ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (count($paid_payments) > 10): ?>
            <p class="text-center" style="margin-top: 15px; color: #718096;">
                <small><?= lang('showing') ?> 10 <?= lang('of') ?> <?= count($paid_payments) ?> <?= lang('paid_payments') ?></small>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Empty State -->
        <?php if (empty($overdue_payments) && empty($upcoming_payments) && empty($paid_payments)): ?>
        <div class="empty-state">
            <i class="fa fa-calendar"></i>
            <h4><?= lang('no_payments') ?></h4>
            <p><?= lang('no_payments_message') ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- All Tax Calculations History -->
    <?php if (!empty($tax_calculations) && count($tax_calculations) > 1): ?>
    <div class="dashboard-section">
        <div class="section-header">
            <h3><i class="fa fa-history"></i> <?= lang('all_tax_calculations') ?></h3>
        </div>
        <div class="table-responsive">
            <table class="table table-striped" style="margin: 0;">
                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <tr>
                        <th style="padding: 15px; border: none;"><?= lang('year') ?></th>
                        <th style="padding: 15px; border: none;"><?= lang('total_sales') ?></th>
                        <th style="padding: 15px; border: none;"><?= lang('taxable_income') ?></th>
                        <th style="padding: 15px; border: none;"><?= lang('tax_due') ?></th>
                        <th style="padding: 15px; border: none;"><?= lang('balance_payment') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tax_calculations as $calc): ?>
                    <tr style="cursor: pointer;" onclick="window.location.href='<?= site_url('dashboard?year=' . $calc->tax_year) ?>'">
                        <td style="padding: 15px;"><strong><?= $calc->tax_year ?></strong></td>
                        <td style="padding: 15px;"><?= $this->sma->formatMoney($calc->total_sales) ?></td>
                        <td style="padding: 15px;"><?= $this->sma->formatMoney($calc->taxable_income) ?></td>
                        <td style="padding: 15px;"><?= $this->sma->formatMoney($calc->tax_due) ?></td>
                        <td style="padding: 15px;"><strong style="color: #dc3545;"><?= $this->sma->formatMoney($calc->balance_payment) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
