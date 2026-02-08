<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
var csrf_token_name = '<?= $this->security->get_csrf_token_name(); ?>'; // Get CSRF token name
var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>'; // Get CSRF token hash
</script>

<script>
function communication_status(x) {
    if (x == null || x === '') return '';
    var s = (x + '').toLowerCase();
    if (s === 'new') return '<div class="text-center"><span class="row_status label label-default">' + x +
        '</span></div>';
    if (s === 'in progress') return '<div class="text-center"><span class="row_status label label-info">' + x +
        '</span></div>';
    if (s === 'completed') return '<div class="text-center"><span class="row_status label label-success">' + x +
        '</span></div>';
    if (s === 'hold') return '<div class="text-center"><span class="row_status label label-danger">' + x +
        '</span></div>';
    return '<div class="text-center"><span class="row_status label label-default">' + x + '</span></div>';
}
$(document).ready(function() {
    oTable = $('#SLData').dataTable({
        "aaSorting": [
            [1, "desc"],
            [2, "desc"]
        ],
        "aLengthMenu": [
            [10, 25, 50, 100, 200],
            [10, 25, 50, 100, 200]
        ],
        "iDisplayLength": <?=$Settings->rows_per_page?>,
        'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': '<?=admin_url('communication/getData'); ?>',
        'fnServerData': function(sSource, aoData, fnCallback) {
            aoData.push({
                "name": "<?=$this->security->get_csrf_token_name()?>",
                "value": "<?=$this->security->get_csrf_hash()?>"
            });
            $.ajax({
                'dataType': 'json',
                'type': 'POST',
                'url': sSource,
                'data': aoData,
                'success': fnCallback
            });
        },
        'fnRowCallback': function(nRow, aData, iDisplayIndex) {
            var oSettings = oTable.fnSettings();
            nRow.id = aData[0];
            nRow.className = "communication_link";
            return nRow;
        },
        "aoColumns": [null, {
            "mRender": fsd
        }, null, null, null, null, {
            "mRender": communication_status
        }, null, {
            "bSortable": false
        }],
        "aoColumnDefs": [{
            "bVisible": false,
            "aTargets": [0]
        }],
        "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {


        }
    }).fnSetFilteringDelay().dtFilter([{
            column_number: 1,
            filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 2,
            filter_default_label: "[<?=lang('reference_no');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 3,
            filter_default_label: "[<?=lang('warhouse');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 4,
            filter_default_label: "[<?=lang('customer');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 5,
            filter_default_label: "[<?=lang('created by');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 6,
            filter_default_label: "[Status]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 7,
            filter_default_label: "[Assign To]",
            filter_type: "text",
            data: []
        },
    ], "footer");

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

        localStorage.removeItem('remove_slls');
    }

});
</script>


<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?=lang('Communication') ;?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?=admin_url('communication/add')?>">
                                <i class="fa fa-plus-circle"></i> <?=lang('Add')?>
                            </a>
                        </li>


                    </ul>
                </li>

            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">


            <div class="table-responsive">
                <table id="SLData" class="table table-bordered table-hover table-striped" cellpadding="0"
                    cellspacing="0" border="0">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="col-xs-1"><?= lang("date"); ?></th>
                            <th class="col-xs-1"><?= lang("reference_no"); ?></th>
                            <th class="col-xs-1"><?= lang("warehouse"); ?></th>
                            <th class="col-xs-2"><?= lang("customer"); ?></th>
                            <th class="col-xs-2"><?= lang("Created_By"); ?></th>
                            <th class="col-xs-1">Status</th>
                            <th class="col-xs-1">Assign To</th>
                            <th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                    </tbody>
                    <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</div>