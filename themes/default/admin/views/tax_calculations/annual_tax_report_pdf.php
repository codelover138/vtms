<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Conto Economico <?= $year ?></title>
    <style>
    @page {
        margin: 40mm 28mm 15mm 28mm;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: "Times New Roman", Times, serif;
        font-size: 12px;
        color: #000;
        line-height: 1.4;
        background: #fff;
        padding: 10px;
    }

    /* Top Right Info */
    .top-right-info {
        text-align: right;
        font-size: 10px;
        margin-bottom: 20px;
        margin-top: 0;
        color: #000;
        line-height: 1.3;
        width: 100%;
        display: block;
    }

    .top-right-info div {
        margin-bottom: 2px;
        display: block;
    }

    /* Header Section - Centered */
    .header-section {
        width: 100%;
        margin-bottom: 15px;
        text-align: center;
    }

    .business-name {
        font-size: 15px;
        font-weight: bold;
        color: #000;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .info-line {
        font-size: 11px;
        color: #000;
        margin-bottom: 2px;
        line-height: 1.3;
    }

    .pec-email {
        font-size: 11px;
        color: #000;
        margin-bottom: 6px;
    }

    /* Tax Regime and Activity - Centered */
    .tax-info {
        text-align: center;
        font-size: 11px;
        color: #000;
        margin-top: 6px;
        margin-bottom: 15px;
        line-height: 1.4;
    }

    .tax-info div {
        margin-bottom: 2px;
    }

    /* Period Section - Centered */
    .period-section {
        text-align: center;
        font-size: 12px;
        color: #000;
        margin: 18px 0;
        font-weight: normal;
    }

    /* Report Title Section */
    .report-title-section {
        width: 100%;
        margin: 20px 0 15px 0;
        display: table;
    }

    .report-title-left {
        display: table-cell;
        text-align: left;
        font-size: 13px;
        font-weight: bold;
        color: #000;
        vertical-align: bottom;
    }

    .report-title-right {
        display: table-cell;
        text-align: right;
        font-size: 13px;
        font-weight: bold;
        color: #000;
        text-decoration: underline;
        vertical-align: bottom;
    }

    /* Income Statement Table */
    .income-statement {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
        font-size: 11px;
    }

    .income-statement thead th {
        background-color: #e0e0e0;
        color: #000;
        padding: 7px 10px;
        font-size: 11px;
        font-weight: bold;
        text-align: left;
        border: none;
    }

    .income-statement tbody td {
        padding: 5px 10px;
        border: none;
        font-size: 11px;
        color: #000;
        vertical-align: top;
    }

    .income-statement tbody td.label {
        text-align: left;
        width: 75%;
    }

    .income-statement tbody td.value {
        text-align: right;
        width: 25%;
        font-weight: normal;
        border-bottom: 1px solid #000;
        padding-bottom: 8px;
    }

    .income-statement tbody tr.total-row td {
        background-color: #e0e0e0;
        font-weight: normal;
        padding: 7px 10px;
    }

    .income-statement tbody tr.total-row td.value {
        border-bottom: none;
        padding-bottom: 7px;
    }

    .income-statement tbody tr.net-income-row td {
        background-color: #e0e0e0;
        font-weight: bold;
        padding: 8px 10px;
        font-size: 11px;
    }

    .income-statement tbody tr.net-income-row td.value {
        border-bottom: none;
        padding-bottom: 8px;
    }

    /* Footer Section */
    .footer-section {
        width: 100%;
        margin-top: 35px;
    }

    .footer-left {
        text-align: left;
    }

    .sig-title {
        font-size: 11px;
        color: #000;
        margin-bottom: 4px;
    }

    .owner-name {
        font-size: 11px;
        font-weight: normal;
        color: #000;
        margin-top: 3px;
        text-transform: uppercase;
    }

    .currency {
        font-family: "Times New Roman", Times, serif;
        white-space: nowrap;
    }
    </style>
</head>

<body>

    <!-- Top Right Info -->
    <?php 
    // Tax Number (Reg. Imp.) = customer table vat_no field
    // VAT number (P.Iva-) = customer table cf4 field
    // Try multiple sources to ensure we get the values
    $tax_num = '';
    if (isset($tax_number) && !empty($tax_number)) {
        $tax_num = trim($tax_number);
    } elseif (isset($customer->vat_no) && !empty($customer->vat_no)) {
        $tax_num = trim($customer->vat_no);
    }
    
    $vat_num = '';
    if (isset($vat_number) && !empty($vat_number)) {
        $vat_num = trim($vat_number);
    } elseif (isset($customer->cf4) && !empty($customer->cf4)) {
        $vat_num = trim($customer->cf4);
    }
    
    // Always show the section if either value exists
    if ($tax_num != '' || $vat_num != ''): 
    ?>
    <div class="top-right-info">
        <?php if ($tax_num != ''): ?>
        <div>Reg. Imp. <?= htmlspecialchars($tax_num) ?></div>
        <?php endif; ?>
        <?php if ($vat_num != ''): ?>
        <div>
            <?php 
            // Display VAT label based on language
            $lang = 'english';
            if (isset($user_language) && !empty($user_language)) {
                $lang = $user_language;
            } elseif (isset($Settings->user_language) && !empty($Settings->user_language)) {
                $lang = $Settings->user_language;
            } elseif (isset($Settings->language) && !empty($Settings->language)) {
                $lang = $Settings->language;
            }
            
            if ($lang == 'italian'): 
                echo 'P.Iva- ' . htmlspecialchars($vat_num);
            else: 
                echo 'P.IVA: ' . htmlspecialchars($vat_num);
            endif; 
            ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Header Section - Centered -->
    <div class="header-section">
        <div class="business-name">
            <?= strtoupper($customer->name . ' ' . ($customer->last_name ? $customer->last_name : '')) ?>
        </div>

        <?php if (!empty($customer->address)): ?>
        <div class="info-line"><?= $customer->address ?></div>
        <?php endif; ?>

        <?php if (!empty($customer->city) || !empty($customer->postal_code)): ?>
        <div class="info-line">
            <?= !empty($customer->postal_code) ? $customer->postal_code : '' ?>
            <?= !empty($customer->city) ? ' – ' . strtoupper($customer->city) : '' ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($pec_email)): ?>
        <div class="pec-email">PEC- <?= $pec_email ?></div>
        <?php endif; ?>
    </div>

    <!-- Tax Regime and Activity - Centered -->
    <div class="tax-info">
        <div>Contribuente in <?= $tax_regime == 'regime_forfettario' ? 'regime forfettario' : $tax_regime ?></div>
        <?php if (!empty($activity_description)): ?>
        <div>attività di <?= $activity_description ?></div>
        <?php endif; ?>
    </div>

    <!-- Period Section - Centered -->
    <div class="period-section">
        Situazione economica dal <?= $period_start ?> al <?= $period_end ?>
    </div>

    <!-- Report Title Section -->
    <div class="report-title-section">
        <div class="report-title-left">
            <strong>Conto Economico</strong>
        </div>
        <div class="report-title-right">
            <strong><?= $period_end ?></strong>
        </div>
    </div>

    <!-- Income Statement Table - Section A -->
    <table class="income-statement">
        <thead>
            <tr>
                <th colspan="2">A) <strong>Valore della produzione</strong></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="label">Ricavi delle prestazioni</td>
                <td class="value currency">€ <?= number_format($total_sales, 2, ',', '.') ?></td>
            </tr>
            <tr class="total-row">
                <td class="label">Totale valore della produzione</td>
                <td class="value currency">€ <?= number_format($total_sales, 2, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Income Statement Table - Section B -->
    <table class="income-statement">
        <thead>
            <tr>
                <th colspan="2">B) <strong>Costi della produzione</strong></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="label">
                    <?php if (!empty($ateco_code)): ?>
                    In base al codice ateco <?= $ateco_code ?> redditività <?= number_format($coefficient, 0) ?>%
                    <?php else: ?>
                    In base alla redditività <?= number_format($coefficient, 0) ?>%
                    <?php endif; ?>
                </td>
                <td class="value currency">€ <?= number_format($costs, 2, ',', '.') ?></td>
            </tr>
            <tr class="total-row">
                <td class="label">Totale costi della produzione</td>
                <td class="value currency">€ <?= number_format($costs, 2, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Net Income -->
    <table class="income-statement">
        <tbody>
            <tr class="net-income-row">
                <td class="label"><strong>Reddito netto</strong></td>
                <td class="value currency"><strong>€ <?= number_format($net_income, 2, ',', '.') ?></strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Footer Section -->
    <div class="footer-section">
        <div class="footer-left">
            <div class="sig-title">Titolare</div>
            <div class="owner-name">
                <?= strtoupper($customer->name . ' ' . ($customer->last_name ? $customer->last_name : '')) ?>
            </div>
        </div>
    </div>

</body>

</html>