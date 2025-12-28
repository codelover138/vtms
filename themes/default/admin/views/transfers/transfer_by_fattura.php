<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
$(document).ready(function() {
    <?php if ($Owner || $Admin) { ?>
    if (!localStorage.getItem('todate')) {
        $("#todate").datetimepicker({
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
    $(document).on('change', '#todate', function(e) {
        localStorage.setItem('todate', $(this).val());
    });
    if (todate = localStorage.getItem('todate')) {
        $('#todate').val(todate);
    }
    <?php } ?>

    $('#tostatus').change(function(e) {
        localStorage.setItem('tostatus', $(this).val());
    });
    if (tostatus = localStorage.getItem('tostatus')) {
        $('#tostatus').select2("val", tostatus);
        if (tostatus == 'completed') {
            $('#tostatus').select2("readonly", true);
        }
    }
    var old_shipping;
    $('#toshipping').focus(function() {
        old_shipping = $(this).val();
    }).change(function() {
        if (!is_numeric($(this).val())) {
            $(this).val(old_shipping);
            bootbox.alert('Unexpected value provided!');
            return;
        } else {
            shipping = $(this).val() ? parseFloat($(this).val()) : '0';
        }
        localStorage.setItem('toshipping', shipping);
        var gtotal = total + product_tax + shipping;
        $('#gtotal').text(formatMoney(gtotal));
    });
    if (toshipping = localStorage.getItem('toshipping')) {
        shipping = parseFloat(toshipping);
        $('#toshipping').val(shipping);
    }
    $('#toref').change(function(e) {
        localStorage.setItem('toref', $(this).val());
    });
    if (toref = localStorage.getItem('toref')) {
        $('#toref').val(toref);
    }
    $('#to_warehouse').change(function(e) {
        localStorage.setItem('to_warehouse', $(this).val());
    });
    if (to_warehouse = localStorage.getItem('to_warehouse')) {
        $('#to_warehouse').val(to_warehouse);
    }
    $('#from_warehouse').change(function(e) {
        localStorage.setItem('from_warehouse', $(this).val());
    });
    if (from_warehouse = localStorage.getItem('from_warehouse')) {
        $('#from_warehouse').val(from_warehouse);
    }
    $('#tostatus').change(function(e) {
        localStorage.setItem('tostatus', $(this).val());
    });
    if (tostatus = localStorage.getItem('tostatus')) {
        $('#tostatus').val(tostatus);
    }

    //$('#tonote').redactor('destroy');
    $('#tonote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold',
            'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'
        ],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function(e) {
            var v = this.get();
            localStorage.setItem('tonote', v);
        }
    });
    if (tonote = localStorage.getItem('tonote')) {
        $('#tonote').redactor('set', tonote);
    }

    $('#to_warehouse').on("select2-close", function(e) {
        if ($(this).val() == $('#from_warehouse').val()) {
            $(this).select2('val', '');
            bootbox.alert('<?= lang('please_select_different_warehouse') ?>');
        }
    });
    $('#from_warehouse').on("select2-close", function(e) {
        if ($(this).val() == $('#to_warehouse').val()) {
            $(this).select2('val', '');
            bootbox.alert('<?= lang('please_select_different_warehouse') ?>');
        }
    });
});
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Transfer_By_Fattura'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("transfers/transfer_by_fattura", $attrib)
                ?>


                <div class="row">
                    <div class="col-lg-12">

                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("reference_no", "toref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $rnumber), 'class="form-control input-tip" readonly id="toref"'); ?>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("customer", "slcustomer"); ?>
                                <div class="input-group">
                                    <?php
                                                echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
                                                ?>
                                    <div class="input-group-addon"> </div>
                                    <div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix">
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("csv_file", "csv_file") ?>
                                <input id="csv_file" type="file" data-browse-label="<?= lang('browse'); ?>"
                                    name="userfile" required="required" data-show-upload="false"
                                    data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <div class="col-md-12">


                            <div class="from-group">
                                <?php echo form_submit('add_transfer', $this->lang->line("submit"), 'id="add_transfer" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                            </div>
                        </div>

                    </div>
                </div>


                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>