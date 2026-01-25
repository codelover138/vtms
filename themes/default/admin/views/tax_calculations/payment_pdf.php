<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title><?= lang('payment_receipt') ? lang('payment_receipt') : 'Payment Receipt' ?></title>
    <style>
    @page {
        margin: 20mm 20mm 15mm 20mm;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        color: #333;
        line-height: 1.5;
        background: #fff;
        padding: 10px;
    }

    /* Top Document Bar */
    .doc-bar {
        width: 100%;
        margin-bottom: 25px;
        margin-top: 5px;
        border-collapse: collapse;
    }

    .doc-bar td {
        padding: 0;
        vertical-align: middle;
    }

    .doc-type {
        font-size: 11px;
        color: #e67e22;
        font-weight: bold;
        text-transform: uppercase;
    }

    .doc-info {
        text-align: right;
        font-size: 10px;
        color: #555;
    }

    .doc-info span {
        margin-left: 20px;
    }

    .doc-info strong {
        color: #222;
    }

    /* Header Section */
    .header-section {
        width: 100%;
        margin-bottom: 30px;
        border-collapse: collapse;
    }

    .header-left {
        width: 55%;
        vertical-align: top;
        padding: 10px 30px 10px 5px;
    }

    .header-right {
        width: 45%;
        vertical-align: top;
        padding: 10px 5px 10px 25px;
        border-left: 2px solid #eee;
    }

    .section-title {
        font-size: 9px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
        font-weight: normal;
    }

    .main-name {
        font-size: 14px;
        font-weight: bold;
        color: #222;
        margin-bottom: 10px;
    }

    .info-line {
        font-size: 10px;
        color: #444;
        margin-bottom: 4px;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 5px 18px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 10px;
    }

    .status-paid {
        background-color: #27ae60;
        color: white;
    }

    .status-pending {
        background-color: #e67e22;
        color: white;
    }

    .status-overdue {
        background-color: #c0392b;
        color: white;
    }

    /* Title Bar */
    .title-bar {
        background-color: #90959a;
        color: white;
        padding: 14px 25px;
        margin: 25px 0;
        font-size: 13px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Data Table */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .data-table thead th {
        background-color: #90959a;
        color: white;
        padding: 12px 15px;
        font-size: 9px;
        font-weight: bold;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .data-table thead th.text-right {
        text-align: right;
    }

    .data-table thead th.text-center {
        text-align: center;
    }

    .data-table tbody td {
        padding: 15px;
        border-bottom: 1px solid #eee;
        vertical-align: top;
        font-size: 10px;
    }

    .data-table tbody td.text-right {
        text-align: right;
    }

    .data-table tbody td.text-center {
        text-align: center;
    }

    .item-title {
        font-weight: bold;
        color: #222;
        margin-bottom: 3px;
    }

    .item-desc {
        font-size: 9px;
        color: #777;
    }

    /* Totals Section */
    .totals-table {
        width: 100%;
        border-collapse: collapse;
    }

    .totals-table td {
        padding: 12px 15px;
        font-size: 10px;
    }

    .totals-table .label-cell {
        text-align: right;
        color: #555;
        font-weight: bold;
        width: 80%;
    }

    .totals-table .value-cell {
        text-align: right;
        width: 20%;
        font-weight: bold;
        color: #222;
    }

    .totals-table .total-row {
        background-color: #f7f7f7;
        border-top: 2px solid #34495e;
    }

    .totals-table .total-row td {
        font-size: 12px;
        padding: 14px 12px;
    }

    .text-success {
        color: #27ae60;
    }

    .text-danger {
        color: #c0392b;
    }

    /* Note Box */
    .note-box {
        background-color: #fef9e7;
        border-left: 4px solid #f1c40f;
        padding: 15px 20px;
        margin: 25px 0;
    }

    .note-title {
        font-weight: bold;
        color: #7d6608;
        margin-bottom: 4px;
        font-size: 10px;
    }

    .note-text {
        color: #7d6608;
        font-size: 9px;
    }

    /* Footer Section */
    .footer-section {
        width: 100%;
        margin-top: 45px;
        border-collapse: collapse;
    }

    .footer-section td {
        vertical-align: top;
        padding: 15px;
    }

    .footer-left {
        width: 50%;
    }

    .footer-right {
        width: 50%;
    }

    .sig-title {
        font-size: 9px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }

    .sig-box {
        border: 1px solid #ddd;
        background-color: #fafafa;
        height: 70px;
        padding: 10px;
    }

    .sig-line {
        border-bottom: 1px solid #333;
        margin-top: 45px;
    }

    .gen-info {
        background-color: #f5f5f5;
        border: 1px solid #e0e0e0;
        padding: 12px 15px;
        font-size: 10px;
        text-align: right;
    }

    .gen-info strong {
        color: #333;
    }

    /* Watermark */
    .watermark {
        position: fixed;
        top: 40%;
        left: 20%;
        font-size: 100px;
        font-weight: bold;
        text-transform: uppercase;
        z-index: -1;
        transform: rotate(-35deg);
    }

    .watermark.paid {
        color: rgba(39, 174, 96, 0.08);
    }

    .watermark.pending {
        color: rgba(230, 126, 34, 0.08);
    }

    .watermark.overdue {
        color: rgba(192, 57, 43, 0.08);
    }
    </style>
</head>

<body>

    <!-- Watermark -->
    <div class="watermark <?= strtolower($payment->status) ?>">
        <?= strtoupper($payment->status) ?>
    </div>

    <!-- Document Info Bar -->
    <table class="doc-bar">
        <tr>
            <td>
                <span class="doc-type">Document: <?= strtoupper($payment_type) ?> PAYMENT RECEIPT</span>
            </td>
            <td class="doc-info">
                <span>Year:
                    <strong><?= isset($payment->payment_year) ? $payment->payment_year : date('Y') ?></strong></span>
                <span>Date: <strong><?= date('d/m/Y') ?></strong></span>
            </td>
        </tr>
    </table>

    <!-- Header Section -->
    <table class="header-section">
        <tr>
            <!-- Customer Details -->
            <td class="header-left">
                <div class="section-title">Customer Details</div>
                <div class="main-name">
                    <?= strtoupper($customer->name . ' ' . ($customer->last_name ? $customer->last_name : '')) ?></div>

                <?php if (!empty($customer->address)): ?>
                <div class="info-line"><?= $customer->address ?></div>
                <?php endif; ?>

                <?php if (!empty($customer->city) || !empty($customer->postal_code)): ?>
                <div class="info-line">
                    <?= !empty($customer->city) ? $customer->city : '' ?>
                    <?= !empty($customer->postal_code) ? ' ' . $customer->postal_code : '' ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($customer->cf)): ?>
                <div class="info-line" style="margin-top: 8px;">VAT No: <?= $customer->cf ?></div>
                <?php endif; ?>

                <?php if (!empty($customer->phone)): ?>
                <div class="info-line">Tel: <?= $customer->phone ?></div>
                <?php endif; ?>

                <?php if (!empty($customer->email)): ?>
                <div class="info-line">Email: <?= strtoupper($customer->email) ?></div>
                <?php endif; ?>
            </td>

            <!-- Service Provider (Biller) -->
            <td class="header-right">
                <div class="section-title">Service Provider</div>
                <?php if ($biller): ?>
                <div class="main-name"><?= strtoupper($biller->company ? $biller->company : $biller->name) ?></div>

                <?php if (!empty($biller->address)): ?>
                <div class="info-line"><?= $biller->address ?></div>
                <?php endif; ?>

                <?php if (!empty($biller->city) || !empty($biller->postal_code)): ?>
                <div class="info-line">
                    <?= !empty($biller->city) ? $biller->city : '' ?>
                    <?= !empty($biller->postal_code) ? ' ' . $biller->postal_code : '' ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($biller->phone)): ?>
                <div class="info-line">Tel: <?= $biller->phone ?></div>
                <?php endif; ?>

                <?php if (!empty($biller->email)): ?>
                <div class="info-line">Email: <?= strtoupper($biller->email) ?></div>
                <?php endif; ?>

                <?php if (!empty($biller->vat_no)): ?>
                <div class="info-line">P.IVA: <?= $biller->vat_no ?></div>
                <?php endif; ?>
                <?php else: ?>
                <div class="main-name"><?= strtoupper($Settings->site_name) ?></div>
                <?php if (!empty($Settings->address)): ?>
                <div class="info-line"><?= $Settings->address ?></div>
                <?php endif; ?>
                <?php endif; ?>

                <div class="status-badge status-<?= strtolower($payment->status) ?>">
                    <?= strtoupper($payment->status) ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- Title Bar -->
    <div class="title-bar">
        <?= strtoupper($payment_type_name) ?> - Payment Details
    </div>

    <!-- Payment Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40px;" class="text-center">No.</th>
                <th>Description</th>
                <th style="width: 80px;" class="text-center">Tax Year</th>
                <th style="width: 100px;" class="text-center">Due Date</th>
                <th style="width: 100px;" class="text-right">Amount (EUR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>
                    <div class="item-title"><?= $payment_type_name ?></div>
                    <div class="item-desc"><?= ucfirst($payment_type) ?> contribution for tax year
                        <?= isset($payment->payment_year) ? $payment->payment_year : date('Y') ?></div>
                </td>
                <td class="text-center"><?= isset($payment->payment_year) ? $payment->payment_year : '-' ?></td>
                <td class="text-center"><?= date('d/m/Y', strtotime($payment->due_date)) ?></td>
                <td class="text-right"><?= number_format($payment->amount, 2, '.', ',') ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Totals Section -->
    <table class="totals-table">
        <tr class="total-row">
            <td class="label-cell">Total Amount (EUR)</td>
            <td class="value-cell"><?= number_format($payment->amount, 2, '.', ',') ?></td>
        </tr>
        <tr>
            <td class="label-cell text-success">Paid (EUR)</td>
            <td class="value-cell text-success"><?= number_format($payment->paid_amount, 2, '.', ',') ?></td>
        </tr>
        <tr>
            <td class="label-cell text-danger">Balance Due (EUR)</td>
            <td class="value-cell text-danger">
                <?= number_format($payment->amount - $payment->paid_amount, 2, '.', ',') ?></td>
        </tr>
    </table>

    <?php if (!empty($payment->paid_date) && $payment->paid_amount > 0): ?>
    <!-- Payment Note -->
    <div class="note-box">
        <div class="note-title">Payment Information</div>
        <div class="note-text">Payment received on <?= date('d/m/Y', strtotime($payment->paid_date)) ?>. Amount paid:
            &euro; <?= number_format($payment->paid_amount, 2, ',', '.') ?></div>
    </div>
    <?php endif; ?>

    <!-- Footer Section -->
    <table class="footer-section">
        <tr>
            <td class="footer-left">
                <div class="sig-title">Customer Signature</div>
                <div class="sig-box">
                    <div class="sig-line"></div>
                </div>
            </td>
            <td class="footer-right">
                <div class="gen-info">
                    <strong>Generated By:</strong>
                    <?= $biller ? ($biller->company ? $biller->company : $biller->name) : $Settings->site_name ?><br>
                    <strong>Date:</strong> <?= date('d/m/Y H:i') ?>
                </div>
                <div style="margin-top: 15px;">
                    <div class="sig-title">Authorized Signature / Stamp</div>
                    <div class="sig-box">
                        <div class="sig-line"></div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

</body>

</html>