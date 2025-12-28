<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
$(document).ready(function() {
    $(document).on('click', '.sledit', function(e) {
        if (localStorage.getItem('slitems')) {
            e.preventDefault();
            var href = $(this).attr('href');
            bootbox.confirm("<?=lang('you_will_loss_sale_data')?>", function(result) {
                if (result) {
                    window.location.href = href;
                }
            });
        }
    });
});
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><?= lang("ref"); ?>: <?= $transfer[0]->reference_no; ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div class="print-only col-xs-12">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>"
                        alt="<?= $Settings->site_name; ?>">
                </div>
                <div class="well well-sm">

                    <div class="col-xs-6 border-right">

                        <div class="col-xs-2"><i class="fa fa-3x fa-user padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $customer->name. ' '.$customer->last_name; ?></h2>
                            <?= $customer->company && $customer->company != '-' ? "" : "Attn: " . $customer->name ?>

                            <?php
                            echo $customer->address . "<br>" . $customer->city . " " . $customer->postal_code . " " . $customer->state . "<br>" . $customer->country;

                            echo "<p>";

                            if ($customer->vat_no != "-" && $customer->vat_no != "") {
                                echo "<br>" . lang("vat_no") . ": " . $customer->vat_no;
                            }
                            if ($customer->gst_no != "-" && $customer->gst_no != "") {
                                echo "<br>" . lang("dob") . ": " . $customer->gst_no;
                            }
                            if ($customer->cf1 != "-" && $customer->cf1 != "") {
                                echo "<br>" . lang("document_number") . ": " . $customer->cf1;
                            }
                            if ($customer->cf2 != "-" && $customer->cf2 != "") {
                                echo "<br>" . lang("document_issue_date") . ": " . $customer->cf2;
                            }
                            if ($customer->cf3 != "-" && $customer->cf3 != "") {
                                echo "<br>" . lang("document_expire_date") . ": " . $customer->cf3;
                            }
                            if ($customer->cf4 != "-" && $customer->cf4 != "") {
                                echo "<br>" . lang("ccf4") . ": " . $customer->cf4;
                            }
                            if ($customer->cf5 != "-" && $customer->cf5 != "") {
                                echo "<br>" . lang("ccf5") . ": " . $customer->cf5;
                            }
                            if ($customer->cf6 != "-" && $customer->cf6 != "") {
                                echo "<br>" . lang("ccf6") . ": " . $customer->cf6;
                            }

                            echo "</p>";
                            echo lang("tel") . ": " . $customer->phone . "<br>" . lang("email") . ": " . $customer->email;
                            ?>


                        </div>


                        <div class="clearfix"></div>
                    </div>


                    <div class="col-xs-6  border-right">
                        <div class="col-xs-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped order-table">
                                    <thead>
                                        <tr>
                                            <th style="text-align:center; vertical-align:middle;"><?= lang("no."); ?>
                                            </th>
                                            <th style="vertical-align:middle;"><?= lang("Period"); ?></th>
                                            <th style="text-align:center; vertical-align:middle;">
                                                <?= lang("Sales_Amount"); ?></th>
                                            <th style="text-align:center; vertical-align:middle;"><?= lang("Taxes"); ?>
                                            </th>
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
                          $totalAmount += $row->total_taxable_sales;
                          $totalTaxes += $row->total_sale_taxes;
                        endforeach; ?>
                                    </tbody>
                                    <tfoot>

                                        <tr>
                                            <td colspan="<?= ($col +2); ?>" style="text-align:right;">
                                                <?= lang("total"); ?>
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
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped print-table order-table">

                        <thead>

                            <tr>
                                <th><?= lang("no."); ?></th>
                                <th><?= lang("Sale_ID"); ?> </th>
                                <th><?= lang("Sale_Entry_Date"); ?></th>
                                <th><?= lang("Sale_Date"); ?></th>
                                <th style="padding-right:20px;"><?= lang("Sales_Amount"); ?></th>
                                <th style="padding-right:20px;"><?= lang("Sales_Taxes"); ?></th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php 
                            
                            $r = 1;
                        foreach ($transfer_list as $rows):
                            ?>
                            <tr>
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle;">
                                    <?= $rows->id_sending; ?>
                                </td>
                                <td style="vertical-align:middle;">
                                    <?= $rows->date_detection; ?>
                                </td>
                                <td style="vertical-align:middle;">
                                    <?= $rows->date_transmission; ?>
                                </td>
                                <td style="width: 100px; text-align:center; vertical-align:middle;">
                                    <?= $this->sma->formatQuantity($rows->taxable_sales); ?>
                                </td>
                                <td style="width: 100px; text-align:center; vertical-align:middle;">
                                    <?= $this->sma->formatQuantity($rows->sale_taxes); ?>
                                </td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                        ?>
                        </tbody>
                        <tfoot>
                            <?php
                        $col = 5;
                        $total +=$rows->taxable_sales;
                        $total_taxes +=$rows->sale_Taxes;
                        
                        ?>

                            <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;">
                                    <?= lang("total_amount"); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <td style="text-align:right; padding-right:10px; font-weight:bold;">
                                    <?= $this->sma->formatMoney($totalAmount); ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;">
                                    <?= lang("Total_Taxes"); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <td style="text-align:right; font-weight:bold;">
                                    <?= $this->sma->formatMoney($totalTaxes); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row">

                    <div class="col-xs-6">
                        <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_sale ? $inv->product_tax+$return_sale->product_tax : $inv->product_tax)) : ''; ?>
                        <div class="well well-sm">
                            <p><?= lang("created_by"); ?>:
                                <?= $transfer[0]->created_by ? $created_by->first_name . ' ' . $created_by->last_name :''; ?>
                            </p>
                            <p><?= lang("date"); ?>: <?= $this->sma->hrld($transfer[0]->created_at); ?></p>
                        </div>
                    </div>
                </div>

                <?php if ($payments) { ?>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-condensed print-table">
                                <thead>
                                    <tr>
                                        <th><?= lang('date') ?></th>
                                        <th><?= lang('payment_reference') ?></th>
                                        <th><?= lang('paid_by') ?></th>
                                        <th><?= lang('amount') ?></th>
                                        <th><?= lang('created_by') ?></th>
                                        <th><?= lang('type') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment) { ?>
                                    <tr <?= $payment->type == 'returned' ? 'class="warning"' : ''; ?>>
                                        <td><?= $this->sma->hrld($payment->date) ?></td>
                                        <td><?= $payment->reference_no; ?></td>
                                        <td><?= lang($payment->paid_by);
                                                if ($payment->paid_by == 'gift_card' || $payment->paid_by == 'CC') {
                                                    echo ' (' . $payment->cc_no . ')';
                                                } elseif ($payment->paid_by == 'Cheque') {
                                                    echo ' (' . $payment->cheque_no . ')';
                                                }
                                                ?></td>
                                        <td><?= $this->sma->formatMoney($payment->amount); ?></td>
                                        <td><?= $payment->first_name . ' ' . $payment->last_name; ?></td>
                                        <td><?= lang($payment->type); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php if (!$Supplier || !$Customer) { ?>
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
                    <a href="<?= admin_url('transfers') ?>" class="tip btn btn-warning sledit"
                        title="<?= lang('List') ?>">
                        <i class="fa fa-th"></i>
                        <span class="hidden-sm hidden-xs"><?= lang('List') ?></span>
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
        <?php } ?>
    </div>
</div>