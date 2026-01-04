<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="<?= $Settings->language ?>">

<head>
    <meta charset="utf-8">
    <base href="<?= site_url() ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : $shop_settings->shop_name ?> - <?= $Settings->site_name ?></title>
    <?php 
    // Use admin assets as shop assets may not exist
    $admin_assets = base_url('themes/default/admin/assets/');
    ?>
    <link rel="shortcut icon" href="<?= $admin_assets ?>images/icon.png" />
    <link href="<?= $admin_assets ?>styles/bootstrap.min.css" rel="stylesheet" />
    <link href="<?= $admin_assets ?>styles/style.css" rel="stylesheet" />
    <link href="<?= $admin_assets ?>styles/theme.css" rel="stylesheet" />
    <link href="<?= $admin_assets ?>fonts/fontawesome-webfont.css" rel="stylesheet" />
    <?php 
    // Load dashboard CSS if on dashboard page
    $is_dashboard = (isset($page_title) && stripos($page_title, 'dashboard') !== false) || 
                    (isset($page) && stripos($page, 'dashboard') !== false) ||
                    (isset($_SERVER['REQUEST_URI']) && stripos($_SERVER['REQUEST_URI'], 'dashboard') !== false);
    if ($is_dashboard): 
    ?>
    <link href="<?= base_url('themes/default/shop/assets/css/dashboard.css') ?>" rel="stylesheet" />
    <?php endif; ?>
    <script type="text/javascript" src="<?= $admin_assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $admin_assets ?>js/jquery-migrate-1.2.1.min.js"></script>
    <style>
    body {
        background-color: #f5f5f5;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .navbar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 0;
        margin-bottom: 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
        color: white !important;
        font-weight: bold;
        font-size: 20px;
    }

    .navbar-nav>li>a {
        color: white !important;
    }

    .navbar-nav>li>a:hover {
        background-color: rgba(255, 255, 255, 0.1) !important;
    }

    .container-fluid {
        padding: 20px;
    }

    .alert {
        border-radius: 5px;
        margin-bottom: 20px;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="<?= site_url('/') ?>">
                    <i class="fa fa-dashboard"></i> <?= $Settings->site_name ?>
                </a>
            </div>
            <div class="navbar-collapse">

                <ul class="nav navbar-nav navbar-right">
                    <?php if (isset($loggedIn) && $loggedIn && isset($loggedInUser) && $loggedInUser): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-user"></i> <?= $loggedInUser->first_name . ' ' . $loggedInUser->last_name ?>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= site_url('profile') ?>"><i class="fa fa-user"></i>
                                    <?= lang('profile') ?></a></li>
                            <li><a href="<?= site_url('logout') ?>"><i class="fa fa-sign-out"></i>
                                    <?= lang('logout') ?></a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li><a href="<?= site_url('login') ?>"><i class="fa fa-sign-in"></i> <?= lang('login') ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <?php if (isset($message) && $message): ?>
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <?= $message ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <?= $error ?>
        </div>
        <?php endif; ?>

        <?php if (isset($warning) && $warning): ?>
        <div class="alert alert-warning alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <?= $warning ?>
        </div>
        <?php endif; ?>