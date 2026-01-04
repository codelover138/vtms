<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <base href="<?= site_url() ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= $Settings->site_name ?></title>
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png" />
    <link href="<?= $assets ?>styles/theme.css" rel="stylesheet" />
    <link href="<?= $assets ?>styles/style.css" rel="stylesheet" />
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
    <!--[if lt IE 9]>
    <script src="<?= $assets ?>js/jquery.js"></script>
    <![endif]-->
    <noscript>
        <style type="text/css">
        #loading {
            display: none;
        }
        </style>
    </noscript>
    <?php if ($Settings->user_rtl) { ?>
    <link href="<?= $assets ?>styles/helpers/bootstrap-rtl.min.css" rel="stylesheet" />
    <link href="<?= $assets ?>styles/style-rtl.css" rel="stylesheet" />
    <script type="text/javascript">
    $(document).ready(function() {
        $('.pull-right, .pull-left').addClass('flip');
    });
    </script>
    <?php } ?>
    <script type="text/javascript">
    $(window).load(function() {
        $("#loading").fadeOut("slow");
    });
    </script>
</head>

<body>
    <noscript>
        <div class="global-site-notice noscript">
            <div class="notice-inner">
                <p><strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled
                    in
                    your browser to utilize the functionality of this website.</p>
            </div>
        </div>
    </noscript>
    <div id="loading"></div>
    <div id="app_wrapper">
        <header id="header" class="navbar">
            <div class="container">
                <a class="navbar-brand" href="<?= admin_url() ?>"><span
                        class="logo"><?= $Settings->site_name ?></span></a>

                <div class="btn-group visible-xs pull-right btn-visible-sm">
                    <button class="navbar-toggle btn" type="button" data-toggle="collapse" data-target="#sidebar_menu">
                        <span class="fa fa-bars"></span>
                    </button>

                    <a href="<?= admin_url('calendar') ?>" class="btn">
                        <span class="fa fa-calendar"></span>
                    </a>
                    <a href="<?= admin_url('users/profile/' . $this->session->userdata('user_id')); ?>" class="btn">
                        <span class="fa fa-user"></span>
                    </a>
                    <a href="<?= admin_url('logout'); ?>" class="btn">
                        <span class="fa fa-sign-out"></span>
                    </a>
                </div>
                <div class="header-nav">
                    <ul class="nav navbar-nav pull-right">
                        <li class="dropdown">
                            <a class="btn account dropdown-toggle" data-toggle="dropdown" href="#">
                                <img alt=""
                                    src="<?= $this->session->userdata('avatar') ? base_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : base_url('assets/images/' . $this->session->userdata('gender') . '.png'); ?>"
                                    class="mini_avatar img-rounded">

                                <div class="user">
                                    <span><?= lang('welcome') ?> <?= $this->session->userdata('username'); ?></span>
                                </div>
                            </a>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a href="<?= admin_url('users/profile/' . $this->session->userdata('user_id')); ?>">
                                        <i class="fa fa-user"></i> <?= lang('profile'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="<?= admin_url('users/profile/' . $this->session->userdata('user_id') . '/#cpassword'); ?>"><i
                                            class="fa fa-key"></i> <?= lang('change_password'); ?>
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?= admin_url('logout'); ?>">
                                        <i class="fa fa-sign-out"></i> <?= lang('logout'); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav pull-right">
                        <li class="dropdown hidden-xs"><a class="btn tip" title="<?= lang('dashboard') ?>"
                                data-placement="bottom"
                                href="<?= ($this->Customer ? site_url(uri: 'dashboard') : admin_url('welcome'));?>"><i
                                    class="fa fa-dashboard"></i></a></li>

                        <?php if ($Owner) { ?>
                        <li class="dropdown hidden-sm">
                            <a class="btn tip" title="<?= lang('settings') ?>" data-placement="bottom"
                                href="<?= admin_url('system_settings') ?>">
                                <i class="fa fa-cogs"></i>
                            </a>
                        </li>
                        <?php } ?>
                        <li class="dropdown hidden-xs">
                            <a class="btn tip" title="<?= lang('calculator') ?>" data-placement="bottom" href="#"
                                data-toggle="dropdown">
                                <i class="fa fa-calculator"></i>
                            </a>
                            <ul class="dropdown-menu pull-right calc">
                                <li class="dropdown-content">
                                    <span id="inlineCalc"></span>
                                </li>
                            </ul>
                        </li>

                        <?php if ($info) { ?>
                        <li class="dropdown hidden-sm">
                            <a class="btn tip" title="<?= lang('notifications') ?>" data-placement="bottom" href="#"
                                data-toggle="dropdown">
                                <i class="fa fa-info-circle"></i>
                                <span class="number blightOrange black"><?= sizeof($info) ?></span>
                            </a>
                            <ul class="dropdown-menu pull-right content-scroll">
                                <li class="dropdown-header"><i class="fa fa-info-circle"></i>
                                    <?= lang('notifications'); ?></li>
                                <li class="dropdown-content">
                                    <div class="scroll-div">
                                        <div class="top-menu-scroll">
                                            <ol class="oe">
                                                <?php foreach ($info as $n) {
                                                    echo '<li>' . $n->comment . '</li>';
                                                } ?>
                                            </ol>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <?php } ?>
                        <?php if ($events) { ?>
                        <li class="dropdown hidden-xs">
                            <a class="btn tip" title="<?= lang('calendar') ?>" data-placement="bottom" href="#"
                                data-toggle="dropdown">
                                <i class="fa fa-calendar"></i>
                                <span class="number blightOrange black"><?= sizeof($events) ?></span>
                            </a>
                            <ul class="dropdown-menu pull-right content-scroll">
                                <li class="dropdown-header">
                                    <i class="fa fa-calendar"></i> <?= lang('upcoming_events'); ?>
                                </li>
                                <li class="dropdown-content">
                                    <div class="top-menu-scroll">
                                        <ol class="oe">
                                            <?php foreach ($events as $event) {
                                                echo '<li>' . date($dateFormats['php_ldate'], strtotime($event->start)) . ' <strong>' . $event->title . '</strong><br>'.$event->description.'</li>';
                                            } ?>
                                        </ol>
                                    </div>
                                </li>
                                <li class="dropdown-footer">
                                    <a href="<?= admin_url('calendar') ?>" class="btn-block link">
                                        <i class="fa fa-calendar"></i> <?= lang('calendar') ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php } else { ?>
                        <li class="dropdown hidden-xs">
                            <a class="btn tip" title="<?= lang('calendar') ?>" data-placement="bottom"
                                href="<?= admin_url('calendar') ?>">
                                <i class="fa fa-calendar"></i>
                            </a>
                        </li>
                        <?php } ?>
                        <li class="dropdown hidden-sm">
                            <a class="btn tip" title="<?= lang('styles') ?>" data-placement="bottom"
                                data-toggle="dropdown" href="#">
                                <i class="fa fa-css3"></i>
                            </a>
                            <ul class="dropdown-menu pull-right">
                                <li class="bwhite noPadding">
                                    <a href="#" id="fixed" class="">
                                        <i class="fa fa-angle-double-left"></i>
                                        <span id="fixedText">Fixed</span>
                                    </a>
                                    <a href="#" id="cssLight" class="grey">
                                        <i class="fa fa-stop"></i> Grey
                                    </a>
                                    <a href="#" id="cssBlue" class="blue">
                                        <i class="fa fa-stop"></i> Blue
                                    </a>
                                    <a href="#" id="cssBlack" class="black">
                                        <i class="fa fa-stop"></i> Black
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown hidden-xs">
                            <a class="btn tip" title="<?= lang('language') ?>" data-placement="bottom"
                                data-toggle="dropdown" href="#">
                                <img src="<?= base_url('assets/images/' . $Settings->user_language . '.png'); ?>"
                                    alt="">
                            </a>
                            <ul class="dropdown-menu pull-right">
                                <?php $scanned_lang_dir = array_map(function ($path) {
                                return basename($path);
                            }, glob(APPPATH . 'language/*', GLOB_ONLYDIR));
                            foreach ($scanned_lang_dir as $entry) { ?>
                                <li>
                                    <a href="<?= admin_url('welcome/language/' . $entry); ?>">
                                        <img src="<?= base_url('assets/images/'.$entry.'.png'); ?>"
                                            class="language-img">
                                        &nbsp;&nbsp;<?= ucwords($entry); ?>
                                    </a>
                                </li>
                                <?php } ?>
                                <li class="divider"></li>

                            </ul>
                        </li>
                        <?php /* if ($Owner && $Settings->update) { ?>
                        <li class="dropdown hidden-sm">
                            <a class="btn blightOrange tip" title="<?= lang('update_available') ?>"
                                data-placement="bottom" data-container="body"
                                href="<?= admin_url('system_settings/updates') ?>">
                                <i class="fa fa-download"></i>
                            </a>
                        </li>
                        <?php } */ ?>
                    </ul>
                </div>
            </div>
        </header>

        <div class="container" id="container">
            <div class="row" id="main-con">
                <table class="lt">
                    <tr>
                        <td class="sidebar-con">
                            <div id="sidebar-left">
                                <div class="sidebar-nav nav-collapse collapse navbar-collapse" id="sidebar_menu">
                                    <ul class="nav main-menu">
                                        <li class="mm_welcome">
                                            <?php if ($this->Customer) { ?>
                                            <a href="<?=  site_url('dashboard') ?>">
                                                <i class="fa fa-dashboard"></i>
                                                <span class="text"> <?= lang('dashboard'); ?></span>
                                            </a>

                                            <?php }else{ ?>
                                            <a href="<?= admin_url() ?>">
                                                <i class="fa fa-dashboard"></i>
                                                <span class="text"> <?= lang('dashboard'); ?></span>
                                            </a>
                                            <?php } ?>
                                        </li>

                                        <?php
                        if ($Owner || $Admin) {
                            ?>

                                        <li class="mm_products">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-barcode"></i>
                                                <span class="text"> <?= lang('products'); ?> </span>
                                                <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <li id="products_index">
                                                    <a class="submenu" href="<?= admin_url('products'); ?>">
                                                        <i class="fa fa-barcode"></i>
                                                        <span class="text"> <?= lang('list_products'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="products_add">
                                                    <a class="submenu" href="<?= admin_url('products/add'); ?>">
                                                        <i class="fa fa-plus-circle"></i>
                                                        <span class="text"> <?= lang('add_product'); ?></span>
                                                    </a>
                                                </li>

                                            </ul>
                                        </li>

                                        <li
                                            class="mm_sales <?= strtolower($this->router->fetch_method()) == 'sales' ? 'mm_pos' : '' ?>">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-heart"></i>
                                                <span class="text"> <?= lang('sales'); ?>
                                                </span> <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <li id="sales_index">
                                                    <a class="submenu" href="<?= admin_url('sales'); ?>">
                                                        <i class="fa fa-heart"></i>
                                                        <span class="text"> <?= lang('list_sales'); ?></span>
                                                    </a>
                                                </li>

                                                <li id="sales_add">
                                                    <a class="submenu" href="<?= admin_url('sales/add'); ?>">
                                                        <i class="fa fa-plus-circle"></i>
                                                        <span class="text"> <?= lang('add_sale'); ?></span>
                                                    </a>
                                                </li>


                                                <li id="sales_gift_cards">
                                                    <a class="submenu" href="<?= admin_url('sales/gift_cards'); ?>">
                                                        <i class="fa fa-gift"></i>
                                                        <span class="text"> <?= lang('list_gift_cards'); ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>

                                        <li class="mm_communication">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-star"></i>
                                                <span class="text"> <?= lang('Communication'); ?>
                                                </span> <span class="chevron closed"></span>
                                            </a>
                                            <ul>

                                                <li id="communication_index">
                                                    <a class="submenu" href="<?= admin_url('communication/'); ?>">
                                                        <i class="fa fa-th-list"></i>
                                                        <span class="text"> <?= lang('List_Communication'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="communication_add">
                                                    <a class="submenu" href="<?= admin_url('communication/add'); ?>">
                                                        <i class="fa fa-plus-circle"></i>
                                                        <span class="text"> <?= lang('Add_Communication'); ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>

                                        <li class="mm_transfers">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-star-o"></i>
                                                <span class="text"> <?= lang('transfers'); ?> </span>
                                                <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <li id="transfers_index">
                                                    <a class="submenu" href="<?= admin_url('transfers'); ?>">
                                                        <i class="fa fa-star-o"></i><span class="text">
                                                            <?= lang('list_transfers'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="transfers_transfer_by_fattura_privati">
                                                    <a class="submenu"
                                                        href="<?= admin_url('transfers/transfer_by_fattura_privati'); ?>">
                                                        <i class="fa fa-plus-circle"></i><span class="text">
                                                            <?= lang('Add_Transfer_Fattura_Privati'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="transfers_transfer_by_fattura">
                                                    <a class="submenu"
                                                        href="<?= admin_url('transfers/transfer_by_fattura'); ?>">
                                                        <i class="fa fa-plus-circle"></i><span class="text">
                                                            <?= lang('Add_Transfer_By_Fattura'); ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="mm_tax_calculations">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-star-o"></i>
                                                <span class="text"> <?= lang('tax_calculations'); ?> </span>
                                                <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <li id="tax_calculations_index">
                                                    <a class="submenu" href="<?= admin_url('tax_calculations'); ?>">
                                                        <i class="fa fa-star-o"></i><span class="text">
                                                            <?= lang('list_tax_calculations'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="tax_calculations_inps_slabs">
                                                    <a class="submenu"
                                                        href="<?= admin_url('tax_calculations/inps_slabs'); ?>">
                                                        <i class="fa fa-plus-circle"></i><span class="text">
                                                            <?= lang('inps_slabs'); ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>

                                        <li class="mm_payments">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-dollar"></i>
                                                <span class="text"> <?= lang('payments'); ?> </span>
                                                <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <li id="payments_index">
                                                    <a class="submenu" href="<?= admin_url('payments'); ?>">
                                                        <i class="fa fa-dollar"></i><span class="text">
                                                            <?= lang('List_Payments'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="payments_add">
                                                    <a class="submenu" href="<?= admin_url('payments/add'); ?>">
                                                        <i class="fa fa-plus-circle"></i><span class="text">
                                                            <?= lang('add'); ?></span>
                                                    </a>
                                                </li>

                                            </ul>
                                        </li>


                                        <li class="mm_purchases">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-star"></i>
                                                <span class="text"> <?= lang('purchases'); ?>
                                                </span> <span class="chevron closed"></span>
                                            </a>
                                            <ul>

                                                <li id="purchases_expenses">
                                                    <a class="submenu" href="<?= admin_url('purchases/expenses'); ?>">
                                                        <i class="fa fa-dollar"></i>
                                                        <span class="text"> <?= lang('list_expenses'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="purchases_add_expense">
                                                    <a class="submenu" href="<?= admin_url('purchases/add_expense'); ?>"
                                                        data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-plus-circle"></i>
                                                        <span class="text"> <?= lang('add_expense'); ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>

                                        <li class="mm_auth mm_customers mm_suppliers mm_billers">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-users"></i>
                                                <span class="text"> <?= lang('people'); ?> </span>
                                                <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <?php if ($Owner) { ?>
                                                <li id="auth_users">
                                                    <a class="submenu" href="<?= admin_url('users'); ?>">
                                                        <i class="fa fa-users"></i><span class="text">
                                                            <?= lang('list_users'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="auth_create_user">
                                                    <a class="submenu" href="<?= admin_url('users/create_user'); ?>">
                                                        <i class="fa fa-user-plus"></i><span class="text">
                                                            <?= lang('new_user'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="billers_index">
                                                    <a class="submenu" href="<?= admin_url('billers'); ?>">
                                                        <i class="fa fa-users"></i><span class="text">
                                                            <?= lang('list_billers'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="billers_index">
                                                    <a class="submenu" href="<?= admin_url('billers/add'); ?>"
                                                        data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-plus-circle"></i><span class="text">
                                                            <?= lang('add_biller'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                                <li id="customers_index">
                                                    <a class="submenu" href="<?= admin_url('customers'); ?>">
                                                        <i class="fa fa-users"></i><span class="text">
                                                            <?= lang('list_customers'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="customers_index">
                                                    <a class="submenu" href="<?= admin_url('customers/add'); ?>"
                                                        data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-plus-circle"></i><span class="text">
                                                            <?= lang('add_customer'); ?></span>
                                                    </a>
                                                </li>

                                            </ul>
                                        </li>
                                        <li class="mm_notifications">
                                            <a class="submenu" href="<?= admin_url('notifications'); ?>">
                                                <i class="fa fa-info-circle"></i><span class="text">
                                                    <?= lang('notifications'); ?></span>
                                            </a>
                                        </li>
                                        <li class="mm_document">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-list-alt"></i>
                                                <span class="text"> <?= lang('document'); ?> </span>
                                                <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <li id="document_file_manager">
                                                    <a class="submenu"
                                                        href="<?= admin_url('document/file_manager'); ?>">
                                                        <i class="fa fa-search"></i><span class="text">
                                                            <?= lang('file_manager'); ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="mm_calendar">
                                            <a class="submenu" href="<?= admin_url('calendar'); ?>">
                                                <i class="fa fa-calendar"></i><span class="text">
                                                    <?= lang('calendar'); ?></span>
                                            </a>
                                        </li>
                                        <?php if ($Owner) { ?>
                                        <li
                                            class="mm_system_settings <?= strtolower($this->router->fetch_method()) == 'sales' ? '' : 'mm_pos' ?>">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-cog"></i><span class="text"> <?= lang('settings'); ?>
                                                </span>
                                                <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <li id="system_settings_index">
                                                    <a href="<?= admin_url('system_settings') ?>">
                                                        <i class="fa fa-cogs"></i><span class="text">
                                                            <?= lang('system_settings'); ?></span>
                                                    </a>
                                                </li>

                                                <li id="system_settings_change_logo">
                                                    <a href="<?= admin_url('system_settings/change_logo') ?>"
                                                        data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-upload"></i><span class="text">
                                                            <?= lang('change_logo'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="system_settings_currencies">
                                                    <a href="<?= admin_url('system_settings/currencies') ?>">
                                                        <i class="fa fa-money"></i><span class="text">
                                                            <?= lang('currencies'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="system_settings_customer_groups">
                                                    <a href="<?= admin_url('system_settings/customer_groups') ?>">
                                                        <i class="fa fa-chain"></i><span class="text">
                                                            <?= lang('customer_groups'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="system_settings_price_groups">
                                                    <a href="<?= admin_url('system_settings/price_groups') ?>">
                                                        <i class="fa fa-dollar"></i><span class="text">
                                                            <?= lang('price_groups'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="system_settings_categories">
                                                    <a href="<?= admin_url('system_settings/categories') ?>">
                                                        <i class="fa fa-folder-open"></i><span class="text">
                                                            <?= lang('categories'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="system_settings_expense_categories">
                                                    <a href="<?= admin_url('system_settings/expense_categories') ?>">
                                                        <i class="fa fa-folder-open"></i><span class="text">
                                                            <?= lang('expense_categories'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="system_settings_units">
                                                    <a href="<?= admin_url('system_settings/units') ?>">
                                                        <i class="fa fa-wrench"></i><span class="text">
                                                            <?= lang('units'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="system_settings_brands">
                                                    <a href="<?= admin_url('system_settings/brands') ?>">
                                                        <i class="fa fa-th-list"></i><span class="text">
                                                            <?= lang('brands'); ?></span>
                                                    </a>
                                                </li>

                                                <li id="system_settings_tax_rates">
                                                    <a href="<?= admin_url('system_settings/tax_rates') ?>">
                                                        <i class="fa fa-plus-circle"></i><span class="text">
                                                            <?= lang('tax_rates'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="system_settings_warehouses">
                                                    <a href="<?= admin_url('system_settings/warehouses') ?>">
                                                        <i class="fa fa-building-o"></i><span class="text">
                                                            <?= lang('warehouses'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="system_settings_email_templates">
                                                    <a href="<?= admin_url('system_settings/email_templates') ?>">
                                                        <i class="fa fa-envelope"></i><span class="text">
                                                            <?= lang('email_templates'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="system_settings_user_groups">
                                                    <a href="<?= admin_url('system_settings/user_groups') ?>">
                                                        <i class="fa fa-key"></i><span class="text">
                                                            <?= lang('group_permissions'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="system_settings_backups">
                                                    <a href="<?= admin_url('system_settings/backups') ?>">
                                                        <i class="fa fa-database"></i><span class="text">
                                                            <?= lang('backups'); ?></span>
                                                    </a>
                                                </li>

                                            </ul>
                                        </li>
                                        <?php } ?>
                                        <li class="mm_reports">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-bar-chart-o"></i>
                                                <span class="text"> <?= lang('reports'); ?> </span>
                                                <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <li id="reports_index">
                                                    <a href="<?= admin_url('reports') ?>">
                                                        <i class="fa fa-bars"></i><span class="text">
                                                            <?= lang('overview_chart'); ?></span>
                                                    </a>
                                                </li>

                                                <li id="reports_best_sellers">
                                                    <a href="<?= admin_url('reports/best_sellers') ?>">
                                                        <i class="fa fa-line-chart"></i><span class="text">
                                                            <?= lang('best_sellers'); ?></span>
                                                    </a>
                                                </li>


                                                <li id="reports_products">
                                                    <a href="<?= admin_url('reports/products') ?>">
                                                        <i class="fa fa-barcode"></i><span class="text">
                                                            <?= lang('products_report'); ?></span>
                                                    </a>
                                                </li>

                                                <li id="reports_daily_sales">
                                                    <a href="<?= admin_url('reports/daily_sales') ?>">
                                                        <i class="fa fa-calendar"></i><span class="text">
                                                            <?= lang('daily_sales'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_monthly_sales">
                                                    <a href="<?= admin_url('reports/monthly_sales') ?>">
                                                        <i class="fa fa-calendar"></i><span class="text">
                                                            <?= lang('monthly_sales'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_sales">
                                                    <a href="<?= admin_url('reports/sales') ?>">
                                                        <i class="fa fa-heart"></i><span class="text">
                                                            <?= lang('sales_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_payments">
                                                    <a href="<?= admin_url('reports/payments') ?>">
                                                        <i class="fa fa-money"></i><span class="text">
                                                            <?= lang('payments_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_tax">
                                                    <a href="<?= admin_url('reports/tax') ?>">
                                                        <i class="fa fa-area-chart"></i><span class="text">
                                                            <?= lang('tax_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_profit_loss">
                                                    <a href="<?= admin_url('reports/profit_loss') ?>">
                                                        <i class="fa fa-money"></i><span class="text">
                                                            <?= lang('profit_and_loss'); ?></span>
                                                    </a>
                                                </li>

                                                <li id="reports_expenses">
                                                    <a href="<?= admin_url('reports/expenses') ?>">
                                                        <i class="fa fa-star"></i><span class="text">
                                                            <?= lang('expenses_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_customer_report">
                                                    <a href="<?= admin_url('reports/customers') ?>">
                                                        <i class="fa fa-users"></i><span class="text">
                                                            <?= lang('customers_report'); ?></span>
                                                    </a>
                                                </li>

                                                <li id="reports_staff_report">
                                                    <a href="<?= admin_url('reports/users') ?>">
                                                        <i class="fa fa-users"></i><span class="text">
                                                            <?= lang('staff_report'); ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <?php if ($Owner && file_exists(APPPATH.'controllers'.DIRECTORY_SEPARATOR.'shop'.DIRECTORY_SEPARATOR.'Shop.php')) { ?>
                                        <li class="mm_shop_settings mm_api_settings">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-shopping-cart"></i><span class="text">
                                                    <?= lang('front_end'); ?> </span>
                                                <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <li id="shop_settings_index">
                                                    <a href="<?= admin_url('shop_settings') ?>">
                                                        <i class="fa fa-cog"></i><span class="text">
                                                            <?= lang('shop_settings'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="shop_settings_slider">
                                                    <a href="<?= admin_url('shop_settings/slider') ?>">
                                                        <i class="fa fa-file"></i><span class="text">
                                                            <?= lang('slider_settings'); ?></span>
                                                    </a>
                                                </li>
                                                <?php if ($Settings->apis) { ?>
                                                <li id="api_settings_index">
                                                    <a href="<?= admin_url('api_settings') ?>">
                                                        <i class="fa fa-key"></i><span class="text">
                                                            <?= lang('api_keys'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                                <li id="shop_settings_pages">
                                                    <a href="<?= admin_url('shop_settings/pages') ?>">
                                                        <i class="fa fa-file"></i><span class="text">
                                                            <?= lang('list_pages'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="shop_settings_pages">
                                                    <a href="<?= admin_url('shop_settings/add_page') ?>">
                                                        <i class="fa fa-plus-circle"></i><span class="text">
                                                            <?= lang('add_page'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="shop_settings_sms_settings">
                                                    <a href="<?= admin_url('shop_settings/sms_settings') ?>">
                                                        <i class="fa fa-cogs"></i><span class="text">
                                                            <?= lang('sms_settings'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="shop_settings_send_sms">
                                                    <a href="<?= admin_url('shop_settings/send_sms') ?>">
                                                        <i class="fa fa-send"></i><span class="text">
                                                            <?= lang('send_sms'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="shop_settings_sms_log">
                                                    <a href="<?= admin_url('shop_settings/sms_log') ?>">
                                                        <i class="fa fa-file-text-o"></i><span class="text">
                                                            <?= lang('sms_log'); ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <?php } ?>

                                        <?php
                        } else { // not owner and not admin
                            ?>
                                        <?php if ($GP['products-index']) { ?>
                                        <li class="mm_products">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-barcode"></i>
                                                <span class="text"> <?= lang('products'); ?>
                                                </span> <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <li id="products_index">
                                                    <a class="submenu" href="<?= admin_url('products'); ?>">
                                                        <i class="fa fa-barcode"></i><span class="text">
                                                            <?= lang('list_products'); ?></span>
                                                    </a>
                                                </li>
                                                <?php if ($GP['products-add']) { ?>
                                                <li id="products_add">
                                                    <a class="submenu" href="<?= admin_url('products/add'); ?>">
                                                        <i class="fa fa-plus-circle"></i><span class="text">
                                                            <?= lang('add_product'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>

                                            </ul>
                                        </li>
                                        <?php } ?>

                                        <?php if ($GP['sales-index'] || $GP['sales-add'] ||  $GP['sales-gift_cards']) { ?>
                                        <li
                                            class="mm_sales <?= strtolower($this->router->fetch_method()) == 'sales' ? 'mm_pos' : '' ?>">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-heart"></i>
                                                <span class="text"> <?= lang('sales'); ?>
                                                </span> <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <li id="sales_index">
                                                    <a class="submenu" href="<?= admin_url('sales'); ?>">
                                                        <i class="fa fa-heart"></i><span class="text">
                                                            <?= lang('list_sales'); ?></span>
                                                    </a>
                                                </li>

                                                <?php if ($GP['sales-add']) { ?>
                                                <li id="sales_add">
                                                    <a class="submenu" href="<?= admin_url('sales/add'); ?>">
                                                        <i class="fa fa-plus-circle"></i><span class="text">
                                                            <?= lang('add_sale'); ?></span>
                                                    </a>
                                                </li>
                                                <?php }
                                   if ($GP['sales-gift_cards']) { ?>
                                                <li id="sales_gift_cards">
                                                    <a class="submenu" href="<?= admin_url('sales/gift_cards'); ?>">
                                                        <i class="fa fa-gift"></i><span class="text">
                                                            <?= lang('gift_cards'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                            </ul>
                                        </li>
                                        <?php } ?>

                                        <?php if ($GP['communication-index'] || $GP['communication-add'] ) { ?>
                                        <li
                                            class="mm_communication <?= strtolower($this->router->fetch_method()) == 'sales' ? 'mm_pos' : '' ?>">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-heart"></i>
                                                <span class="text"> <?= lang('Communication'); ?>
                                                </span> <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <?php if ($GP['communication-index']) { ?>
                                                <li id="communication_index">
                                                    <a class="submenu" href="<?= admin_url('communication/'); ?>">
                                                        <i class="fa fa-th-list"></i>
                                                        <span class="text"> <?= lang('List_Communication'); ?></span>
                                                    </a>
                                                </li>

                                                <?php }?>

                                                <?php if ($GP['communication-add']) { ?>
                                                <li id="communication_add">
                                                    <a class="submenu" href="<?= admin_url('communication/add'); ?>">
                                                        <i class="fa fa-plus-circle"></i>
                                                        <span class="text"> <?= lang('Add_Communication'); ?></span>
                                                    </a>
                                                </li>
                                                <?php }?>

                                            </ul>
                                        </li>
                                        <?php } ?>

                                        <?php if ($GP['purchases-expenses']) { ?>
                                        <li class="mm_purchases">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-star"></i>
                                                <span class="text"> <?= lang('purchases'); ?>
                                                </span> <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <?php if ($GP['purchases-expenses']) { ?>
                                                <li id="purchases_expenses">
                                                    <a class="submenu" href="<?= admin_url('purchases/expenses'); ?>">
                                                        <i class="fa fa-dollar"></i><span class="text">
                                                            <?= lang('list_expenses'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="purchases_add_expense">
                                                    <a class="submenu" href="<?= admin_url('purchases/add_expense'); ?>"
                                                        data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-plus-circle"></i><span class="text">
                                                            <?= lang('add_expense'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                            </ul>
                                        </li>
                                        <?php } ?>

                                        <?php if ($GP['customers-index'] || $GP['customers-add'] ) { ?>
                                        <li class="mm_auth mm_customers mm_suppliers mm_billers">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-users"></i>
                                                <span class="text"> <?= lang('people'); ?> </span>
                                                <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <?php if ($GP['customers-index']) { ?>
                                                <li id="customers_index">
                                                    <a class="submenu" href="<?= admin_url('customers'); ?>">
                                                        <i class="fa fa-users"></i><span class="text">
                                                            <?= lang('list_customers'); ?></span>
                                                    </a>
                                                </li>
                                                <?php }
                                    if ($GP['customers-add']) { ?>
                                                <li id="customers_index">
                                                    <a class="submenu" href="<?= admin_url('customers/add'); ?>"
                                                        data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-plus-circle"></i><span class="text">
                                                            <?= lang('add_customer'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                            </ul>
                                        </li>
                                        <?php } ?>
                                        <?php if ($GP['document-file_manager']
                            ) {
                                ?>
                                        <li class="mm_document">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-list-alt"></i>
                                                <span class="text"> <?= lang('document'); ?> </span>
                                                <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <?php if ($GP['document-file_manager']) { ?>
                                                <li id="document_file_manager">
                                                    <a class="submenu"
                                                        href="<?= admin_url('document/file_manager'); ?>">
                                                        <i class="fa fa-search"></i><span class="text">
                                                            <?= lang('File_Manager'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                            </ul>
                                        </li>
                                        <?php } ?>

                                        <?php if ( $GP['reports-products'] || $GP['reports-monthly_sales'] || $GP['reports-sales'] || $GP['reports-payments'] || $GP['reports-customers'] | $GP['reports-expenses']) { ?>
                                        <li class="mm_reports">
                                            <a class="dropmenu" href="#">
                                                <i class="fa fa-bar-chart-o"></i>
                                                <span class="text"> <?= lang('reports'); ?> </span>
                                                <span class="chevron closed"></span>
                                            </a>
                                            <ul>
                                                <?php if ($GP['reports-products']) { ?>
                                                <li id="reports_products">
                                                    <a href="<?= admin_url('reports/products') ?>">
                                                        <i class="fa fa-filter"></i><span class="text">
                                                            <?= lang('products_report'); ?></span>
                                                    </a>
                                                </li>

                                                <li id="reports_categories">
                                                    <a href="<?= admin_url('reports/categories') ?>">
                                                        <i class="fa fa-folder-open"></i><span class="text">
                                                            <?= lang('categories_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_brands">
                                                    <a href="<?= admin_url('reports/brands') ?>">
                                                        <i class="fa fa-cubes"></i><span class="text">
                                                            <?= lang('brands_report'); ?></span>
                                                    </a>
                                                </li>
                                                <?php }
                                    if ($GP['reports-daily_sales']) { ?>
                                                <li id="reports_daily_sales">
                                                    <a href="<?= admin_url('reports/daily_sales') ?>">
                                                        <i class="fa fa-calendar-o"></i><span class="text">
                                                            <?= lang('daily_sales'); ?></span>
                                                    </a>
                                                </li>
                                                <?php }
                                    if ($GP['reports-monthly_sales']) { ?>
                                                <li id="reports_monthly_sales">
                                                    <a href="<?= admin_url('reports/monthly_sales') ?>">
                                                        <i class="fa fa-calendar-o"></i><span class="text">
                                                            <?= lang('monthly_sales'); ?></span>
                                                    </a>
                                                </li>
                                                <?php }
                                    if ($GP['reports-sales']) { ?>
                                                <li id="reports_sales">
                                                    <a href="<?= admin_url('reports/sales') ?>">
                                                        <i class="fa fa-heart"></i><span class="text">
                                                            <?= lang('sales_report'); ?></span>
                                                    </a>
                                                </li>
                                                <?php }
                                    if ($GP['reports-payments']) { ?>
                                                <li id="reports_payments">
                                                    <a href="<?= admin_url('reports/payments') ?>">
                                                        <i class="fa fa-money"></i><span class="text">
                                                            <?= lang('payments_report'); ?></span>
                                                    </a>
                                                </li>
                                                <?php }
                                    if ($GP['reports-tax']) { ?>
                                                <li id="reports_tax">
                                                    <a href="<?= admin_url('reports/tax') ?>">
                                                        <i class="fa fa-area-chart"></i><span class="text">
                                                            <?= lang('tax_report'); ?></span>
                                                    </a>
                                                </li>
                                                <?php }

                                    if ($GP['reports-expenses']) { ?>
                                                <li id="reports_expenses">
                                                    <a href="<?= admin_url('reports/expenses') ?>">
                                                        <i class="fa fa-star"></i><span class="text">
                                                            <?= lang('expenses_report'); ?></span>
                                                    </a>
                                                </li>
                                                <?php }
                                    if ($GP['reports-customers']) { ?>
                                                <li id="reports_customer_report">
                                                    <a href="<?= admin_url('reports/customers') ?>">
                                                        <i class="fa fa-users"></i><span class="text">
                                                            <?= lang('customers_report'); ?></span>
                                                    </a>
                                                </li>
                                                <?php }
                                     ?>
                                            </ul>
                                        </li>
                                        <?php } ?>

                                        <?php } ?>
                                    </ul>
                                </div>
                                <a href="#" id="main-menu-act" class="full visible-md visible-lg">
                                    <i class="fa fa-angle-double-left"></i>
                                </a>
                            </div>
                        </td>
                        <td class="content-con">
                            <div id="content">
                                <div class="row">
                                    <div class="col-sm-12 col-md-12">
                                        <ul class="breadcrumb">
                                            <?php
                            foreach ($bc as $b) {
                                if ($b['link'] === '#') {
                                    echo '<li class="active">' . $b['page'] . '</li>';
                                } else {
                                    echo '<li><a href="' . $b['link'] . '">' . $b['page'] . '</a></li>';
                                }
                            }
                            ?>
                                            <li class="right_log hidden-xs">
                                                <?= lang('your_ip') . ' ' . $ip_address . " <span class='hidden-sm'>( " . lang('last_login_at') . ": " . date($dateFormats['php_ldate'], $this->session->userdata('old_last_login')) . " " . ($this->session->userdata('last_ip') != $ip_address ? lang('ip:') . ' ' . $this->session->userdata('last_ip') : '') . " )</span>" ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if ($message) { ?>
                                        <div class="alert alert-success">
                                            <button data-dismiss="alert" class="close" type="button">×</button>
                                            <?= $message; ?>
                                        </div>
                                        <?php } ?>
                                        <?php if ($error) { ?>
                                        <div class="alert alert-danger">
                                            <button data-dismiss="alert" class="close" type="button">×</button>
                                            <?= $error; ?>
                                        </div>
                                        <?php } ?>
                                        <?php if ($warning) { ?>
                                        <div class="alert alert-warning">
                                            <button data-dismiss="alert" class="close" type="button">×</button>
                                            <?= $warning; ?>
                                        </div>
                                        <?php } ?>
                                        <?php
                        if ($info) {
                            foreach ($info as $n) {
                                if (!$this->session->userdata('hidden' . $n->id)) {
                                    ?>
                                        <div class="alert alert-info">
                                            <a href="#" id="<?= $n->id ?>" class="close hideComment external"
                                                data-dismiss="alert">&times;</a>
                                            <?= $n->comment; ?>
                                        </div>
                                        <?php }
                            }
                        } ?>
                                        <div class="alerts-con"></div>