<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;"
                onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <?php
            
            if ($logo) { ?>
            <div class="text-center" style="margin-bottom:20px;">
                <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>"
                    alt="<?= $Settings->site_name; ?>">
            </div>
            <?php } ?>
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-4"><?= lang("ref"); ?>: <?= $transfer[0]->reference_no; ?>
                    </div>
                    <div class="col-xs-6 pull-right text-right order_barcodes">
                        <?= $this->sma->qrcode('link', urlencode(admin_url('transfers/view/' .  $transfer[0]->reference_no)), 2); ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="row">
                <div class="col-xs-6">
                    <?= lang("customer"); ?>:<br />
                    <h3 style="margin-top:10px;"><?=  $transfer[0]->customer_name; ?></h3>
                    <?= "<p>" . $to_warehouse->address . "</p><p>" . $to_warehouse->phone . "<br>" . $to_warehouse->email . "</p>";
                    ?>
                </div>
            </div>

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

            <div class="row">
                <div class="col-xs-4 pull-left">
                    <p><?= lang("created_by"); ?>: <?= $created_by->first_name.' '.$created_by->last_name; ?> </p>
                    <p><?= lang("Created_At"); ?>: <?= $this->sma->hrsd($transfer[0]->created_at); ?> </p>

                </div>
            </div>
            <div class="buttons">
                <div class="btn-group btn-group-justified">


                    <div class="btn-group">
                        <a href="<?= admin_url('transfers/pdf?id='. $transfer[0]->reference_no) ?>"
                            class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                            <i class="fa fa-download"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('transfers/details?id=' . $transfer[0]->reference_no) ?>"
                            class="tip btn btn-warning sledit" title="<?= lang('Details') ?>">
                            <i class="fa fa-th"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('Details') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete") ?></b>"
                            data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('transfers/delete?id=' . $transfer[0]->reference_no) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                            data-html="true" data-placement="top">
                            <i class="fa fa-trash-o"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $('.tip').tooltip();
});
</script>