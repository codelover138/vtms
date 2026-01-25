<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {
    var tTable = $('#INPSSlabsData').dataTable({
        "aaSorting": [
            [1, "desc"],
            [2, "asc"]
        ],
        "aLengthMenu": [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "<?= lang('all') ?>"]
        ],
        "iDisplayLength": <?= $Settings->rows_per_page ?>,
        'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': '<?= admin_url('tax_calculations/getINPSSlabs') ?>',
        'fnServerData': function(sSource, aoData, fnCallback) {
            aoData.push({
                "name": "<?= $this->security->get_csrf_token_name() ?>",
                "value": "<?= $this->security->get_csrf_hash() ?>"
            });
            $.ajax({
                'dataType': 'json',
                'type': 'POST',
                'url': sSource,
                'data': aoData,
                'success': fnCallback
            });
        },
        "aoColumns": [null, null, null, null, null, null, null, null, {
            "bSortable": false
        }],
        'fnRowCallback': function(nRow, aData, iDisplayIndex) {
            nRow.id = aData[0];
            return nRow;
        }
    }).dtFilter([{
            column_number: 1,
            filter_default_label: "[<?= lang('year'); ?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 2,
            filter_default_label: "[<?= lang('customer_type'); ?>]",
            filter_type: "text",
            data: []
        }
    ], "footer");
});
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-list"></i><?= lang('inps_rate_slabs'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="<?= admin_url('tax_calculations/edit_inps_slab') ?>" class="tip"
                        title="<?= lang('add_inps_slab') ?>">
                        <i class="icon fa fa-plus"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php if ($message) { ?>
                <div class="alert alert-success">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <?= is_array($message) ? print_r($message, true) : $message; ?>
                </div>
                <?php } ?>
                <?php if ($error) { ?>
                <div class="alert alert-danger">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <?= is_array($error) ? print_r($error, true) : $error; ?>
                </div>
                <?php } ?>
                <p class="introtext"><?= lang('manage_inps_rate_slabs'); ?></p>

                <div class="table-responsive">
                    <table id="INPSSlabsData" cellpadding="0" cellspacing="0" border="0"
                        class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <tr class="primary">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th><?= lang("year"); ?></th>
                                <th><?= lang("customer_type"); ?></th>
                                <th><?= lang("income_from"); ?></th>
                                <th><?= lang("income_to"); ?></th>
                                <th><?= lang("inps_rate"); ?> (%)</th>
                                <th><?= lang("fixed_amount"); ?></th>
                                <th><?= lang("is_active"); ?></th>
                                <th style="min-width:150px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="min-width:150px; text-align: center;"><?= lang("actions"); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
