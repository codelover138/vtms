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
    
    // Initialize customer select2
    var $customer = $('#slcustomer');
    $customer.change(function (e) {
        localStorage.setItem('slcustomer', $(this).val());
    });
    
    if (slcustomer = localStorage.getItem('slcustomer')) {
        $customer.val(slcustomer).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "customers/getCustomer/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
    } else {
        // Initialize customer select2 without localStorage value
        $customer.select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
    }
});
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Transfer_By_Fattura_Privati'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("transfers/transfer_by_fattura_privati", $attrib)
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

