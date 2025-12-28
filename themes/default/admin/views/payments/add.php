<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
var count = 1,
    $(document).ready(function() {
        if (localStorage.getItem('remove_slls')) {

            if (localStorage.getItem('slwarehouse')) {
                localStorage.removeItem('slwarehouse');
            }
            if (localStorage.getItem('slnote')) {
                localStorage.removeItem('slnote');
            }
            if (localStorage.getItem('slinnote')) {
                localStorage.removeItem('slinnote');
            }
            if (localStorage.getItem('slcustomer')) {
                localStorage.removeItem('slcustomer');
            }

            if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }

            localStorage.removeItem('remove_slls');
        }


        if (!localStorage.getItem('sldate')) {
            $("#sldate").datetimepicker({
                format: site.dateFormats.js_ldate,
                fontAwesome: true,
                language: 'sma',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0
            }).datetimepicker('update', new Date());
        }
        $(document).on('change', '#sldate', function(e) {
            localStorage.setItem('sldate', $(this).val());
        });
        if (sldate = localStorage.getItem('sldate')) {
            $('#sldate').val(sldate);
        }

        if (!localStorage.getItem('slref')) {
            localStorage.setItem('slref', '<?=$slnumber?>');
        }

    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Add'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("payments/add", $attrib);
                
                ?>
                <div class="row">
                    <div class="col-lg-12">

                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("date", "sldate"); ?>
                                <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip date" id="sldate" required="required"'); ?>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("reference_no", "slref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $payment_ref), 'class="form-control input-tip" readonly id="slref"'); ?>
                            </div>
                        </div>

                        <div class="clearfix"></div>

                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading">
                                </div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("warehouse", "slwarehouse"); ?>
                                            <?php
                                                   $pst = array('' => lang('Please_Select_The_Item'),'INPS' => lang('INPS'), 'Income-Tax' => lang('Income-Tax'), 'Vat' => lang('Vat'), 'Other' => lang('Other'));
                                                    echo form_dropdown('type', $pst, (isset($_POST['type']) ? $_POST['type'] : ''), 'id="type" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("Type") . '" required="required" style="width:100%;" ');
                                                ?>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <?= lang("customer", "slcustomer"); ?>
                                            <div class="input-group">
                                                <?php
                                                echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
                                                ?>
                                                <div class="input-group-addon no-print"
                                                    style="padding: 2px 8px; border-left: 0;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="clearfix"></div>


                    <div class="col-md-12">
                        <div id="payments">

                            <div class="well well-sm well_1">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="payment">
                                                <div class="form-group">
                                                    <?= lang("amount", "amount_1"); ?>
                                                    <input name="amount" type="text" id="amount_1"
                                                        class="pa form-control kb-pad amount" required="required" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <?= lang("paying_by", "paid_by_1"); ?>
                                                <select name="paid_by" id="paid_by_1" class="form-control paid_by"
                                                    required="required">
                                                    <?= $this->sma->paid_opts(); ?>
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="form-group gc" style="display: none;">
                                        <?= lang("gift_card_no", "gift_card_no"); ?>
                                        <input name="gift_card_no" type="text" id="gift_card_no"
                                            class="pa form-control kb-pad" />

                                        <div id="gc_details"></div>
                                    </div>
                                    <div class="pcc_1" style="display:none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <input name="pcc_no" type="text" id="pcc_no_1" class="form-control"
                                                        placeholder="<?= lang('cc_no') ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">

                                                    <input name="pcc_holder" type="text" id="pcc_holder_1"
                                                        class="form-control" placeholder="<?= lang('cc_holder') ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <select name="pcc_type" id="pcc_type_1"
                                                        class="form-control pcc_type"
                                                        placeholder="<?= lang('card_type') ?>">
                                                        <option value="Visa"><?= lang("Visa"); ?></option>
                                                        <option value="MasterCard"><?= lang("MasterCard"); ?>
                                                        </option>
                                                        <option value="Amex"><?= lang("Amex"); ?></option>
                                                        <option value="Discover"><?= lang("Discover"); ?></option>
                                                    </select>
                                                    <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />-->
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <input name="pcc_month" type="text" id="pcc_month_1"
                                                        class="form-control" placeholder="<?= lang('month') ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">

                                                    <input name="pcc_year" type="text" id="pcc_year_1"
                                                        class="form-control" placeholder="<?= lang('year') ?>" />
                                                </div>
                                            </div>
                                            <!--<div class="col-md-3">
                                    <div class="form-group">
                                        <input name="pcc_ccv" type="text" id="pcc_cvv2_1" class="form-control" placeholder="<?= lang('cvv2') ?>" />
                                    </div>
                                </div>-->
                                        </div>
                                    </div>
                                    <div class="pcheque_1" style="display:none;">
                                        <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                                            <input name="cheque_no" type="text" id="cheque_no_1"
                                                class="form-control cheque_no" />
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>

                        </div>
                    </div>
                    <div class="clearfix"></div>

                    <div class="row" id="bt">
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <?= lang("note", "slnote"); ?>
                                    <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" required="required" id="slnote" style="margin-top: 10px; height: 100px;"'); ?>

                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-12">
                        <div class="fprom-group">
                            <?php echo form_submit('add_sale', lang("submit"), 'id="add_sale" class="btn btn-primary"  style="padding: 6px 15px; margin:15px 0;"'); ?>
                            <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo form_close(); ?>

        </div>

    </div>
</div>
</div>


<script type="text/javascript">
$(document).ready(function() {
    $('#gccustomer').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "customers/suggestions",
            dataType: 'json',
            quietMillis: 15,
            data: function(term, page) {
                return {
                    term: term,
                    limit: 10
                };
            },
            results: function(data, page) {
                if (data.results != null) {
                    return {
                        results: data.results
                    };
                } else {
                    return {
                        results: [{
                            id: '',
                            text: 'No Match Found'
                        }]
                    };
                }
            }
        }
    });
    $('#genNo').click(function() {
        var no = generateCardNo();
        $(this).parent().parent('.input-group').children('input').val(no);
        return false;
    });
});
</script>