<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->lang->line('sale') . ' ' . $inv->reference_no; ?></title>
    <link href="<?= $assets ?>styles/pdf/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
</head>

<body>
<div id="wrap">
    <div class="row">
        <div class="col-lg-12">
            <?php if ($logo) {
                $path = base_url() . 'assets/uploads/logos/' . $biller->logo;
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= $base64; ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                </div>
            <?php }
            ?>
            <div class="clearfix"></div>

            <div class="padding10">
                <div class="col-xs-5">
                    <span style="font-weight:bold;"><?= lang('customer'); ?></span>:<br>
                    <span ><?= $customer->name && $customer->last_name ? $customer->name.' '.$customer->last_name : $customer->company; ?></span><br>
                    <?php
                    echo $customer->address . '<br />' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . '<br />' . $customer->country;
                    echo '<p>';
                    if ($customer->vat_no != "-" && $customer->vat_no != "") {
                        echo "<br>" . lang("vat_no") . ": " . $customer->vat_no;
                    }
                    if ($customer->gst_no != "-" && $customer->gst_no != "") {
                        echo "<br>" . lang("dob") . ": " . $customer->gst_no;
                    }
                    if ($customer->cf1 != '-' && $customer->cf1 != '') {
                        echo '<br>' . lang('document_number') . ': ' . $customer->cf1;
                    }
                    if ($customer->cf2 != '-' && $customer->cf2 != '') {
                        echo '<br>' . lang('document_issue_date') . ': ' . $customer->cf2;
                    }
                    if ($customer->cf3 != '-' && $customer->cf3 != '') {
                        echo '<br>' . lang('document_expire_date') . ': ' . $customer->cf3;
                    }
                    if ($customer->cf4 != '-' && $customer->cf4 != '') {
                        echo '<br>' . lang('ccf4') . ': ' . $customer->cf4;
                    }
                    if ($customer->cf5 != '-' && $customer->cf5 != '') {
                        echo '<br>' . lang('ccf5') . ': ' . $customer->cf5;
                    }
                    if ($customer->cf6 != '-' && $customer->cf6 != '') {
                        echo '<br>' . lang('ccf6') . ': ' . $customer->cf6;
                    }
                    echo '</p>';
                    echo lang('tel') . ': ' . $customer->phone . '<br />' . lang('email') . ': ' . $customer->email;
                    ?>
                </div>

                <div class="col-xs-5">
                    <span style="font-weight:bold;"><?= lang('warehouse'); ?></span>:<br>
                    <?= $warehouse->name ?>
                    <?php
                    echo $warehouse->address . '<br>';
                    echo ($warehouse->phone ? lang('tel') . ': ' . $warehouse->phone . '<br>' : '') . ($warehouse->email ? lang('email') . ': ' . $warehouse->email : '');
                    ?>
                    <?php
                    if(isset($service_provider)){ ?>
                        <br><span><?=lang('assign_service_provider')?>: <?php echo $service_provider->first_name.' '.$service_provider->last_name;?></span>
                    <?php } ?>
                    <div class="bold">
                        <?= lang('date'); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                        <?= lang('ref'); ?>: <?= $inv->reference_no; ?><br>
                        <?php if (!empty($inv->return_sale_ref)) {
                            echo lang("return_ref").': '.$inv->return_sale_ref.'<br>';
                        } ?>
                        <div class="order_barcodes barcode">
                            <?php
                            $path = admin_url('misc/barcode/'.$this->sma->base64url_encode($inv->reference_no).'/code128/74/0/1');
                            $type = $Settings->barcode_img ? 'png' : 'svg+xml';
                            $data = file_get_contents($path);
                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            ?>
                            <?php echo $this->sma->qrcode('link', urlencode(admin_url('sales/view/' . $inv->id)), 2); ?>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                </div>

            </div>


                        <?php $r = '[';
                        $length=count($rows);
                        foreach ($rows as $row):{
                            $r .= ($length > 1) ? $row->product_name.',' :  $row->product_name;
                        }
                        endforeach;
                        $r .= ']';

                      ?>
            <div class="clearfix"></div>
            <br>
            <br>
            <br>

            <div class="text-center" style="margin-bottom:2px; text-decoration: underline;">
                <h2><?=lang('consent'); ?></h2>
            </div>


            <div style="margin-left: 10px; margin-right: 10px; margin-top: 10px; margin-bottom: 30px;">
                <p>
                    <?php
                    $name = ($customer->name && $customer->last_name) ? $customer->name . ' ' . $customer->last_name : $customer->company;
                    $noteTemplate = lang('consent_note'); // Retrieve the note from language file
                    $noteText = str_replace(['{name}', '{warehouse}','{task}'], [$name, $warehouse->name,$r], $noteTemplate);
                    echo $noteText;
                    ?>
                </p>

            </div>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <div class="clearfix"></div>
            <div class="margin05">
            <div class="col-xs-4 pull-left">
                <hr>
                <p> <?= $warehouse->name; ?><br>
                    <?=lang('stamp_sign'); ?></p>
            </div>
            <div class="col-xs-4 pull-right">
                <hr>
                <p><?= ($customer->name && $customer->last_name) ? $customer->name.' '. $customer->last_name : $customer->company; ?>
                    <br>
                    <?=lang('stamp_sign'); ?></p>
                <?php if ($customer->award_points != 0 && $Settings->each_spent > 0) { ?>
                    <div class="well well-sm">
                        <?=
                        '<p>'.lang('this_sale').': '.floor(($inv->grand_total/$Settings->each_spent)*$Settings->ca_point)
                        .'<br>'.
                        lang('total').' '.lang('award_points').': '. $customer->award_points . '</p>';?>
                    </div>
                <?php } ?>
            </div>
            </div>
            <div class="clearfix"></div>

        </div>
    </div>
</div>
</body>
</html>
