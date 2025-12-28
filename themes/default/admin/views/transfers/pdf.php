<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->lang->line("transfer") . " " . $transfer->transfer_no; ?></title>
    <link href="<?= $assets ?>styles/pdf/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
</head>

<body>
    <div id="wrap">
        <div class="col-xs-11">
            <?php if ($logo) {
            $path = base_url() . 'assets/uploads/logos/' . $Settings->logo;
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            ?>
            <div class="text-center" style="margin-bottom:20px;">
                <img src="<?= $base64; ?>" alt="<?=$Settings->site_name;?>">
            </div>
            <?php
        } ?>

            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-4"><?= lang("ref"); ?>: <?= $transfer[0]->reference_no; ?>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>

        <div class="clearfix"></div>
        <div class="col-xs-5">
            <?= lang("customer"); ?>:<br />
            <h3 style="margin-top:10px;"><?=  $transfer[0]->customer_name; ?></h3>
            <?= "<p>" . $to_warehouse->address . "</p><p>" . $to_warehouse->phone . "<br>" . $to_warehouse->email . "</p>";
                    ?>
        </div>
        <div class="clearfix"></div>

        <div class="col-xs-11">
            <div class="clearfix"></div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped order-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; vertical-align:middle;"><?= lang("no."); ?></th>
                            <th style="vertical-align:middle;"><?= lang("Period"); ?></th>
                            <th style="text-align:center; vertical-align:middle;"><?= lang("Sales_Amount"); ?></th>
                            <th style="text-align:center; vertical-align:middle;"><?= lang("Taxes"); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $r = 1;
                        foreach ($transfer as $row): ?>
                        <tr>
                            <td style="text-align:center; width:25px;"><?= $r; ?></td>
                            <td style="text-align:left;">
                                <?= $row->month; ?>
                            </td>
                            <td style="width: 100px; text-align:right; vertical-align:middle;">
                                <?= $this->sma->formatMoney($row->total_taxable_sales); ?></td>
                            <td style="width: 100px; text-align:right; vertical-align:middle;">
                                <?= $this->sma->formatMoney($row->total_sale_taxes); ?></td>
                        </tr>
                        <?php $r++;
                          $totalAmount +=$row->total_taxable_sales;
                          $totalTaxes +=$row->total_sale_taxes;
                        endforeach; ?>
                    </tbody>
                    <tfoot>

                        <tr>
                            <td colspan="<?= ($col +2); ?>" style="text-align:right;"><?= lang("total"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right;">
                                <?= $this->sma->formatMoney($totalAmount); ?>
                            </td>
                            <td style="text-align:right;">
                                <?= $this->sma->formatMoney($totalTaxes); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="col-xs-4 pull-left">
            <p><?= lang("created_by"); ?>: <?= $created_by->first_name.' '.$created_by->last_name; ?> </p>
            <p><?= lang("Created_At"); ?>: <?= $this->sma->hrsd($transfer[0]->created_at); ?> </p>

        </div>

    </div>
</body>

</html>