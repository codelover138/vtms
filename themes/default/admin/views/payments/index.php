<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
var csrf_token_name = '<?= $this->security->get_csrf_token_name(); ?>'; // Get CSRF token name
var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>'; // Get CSRF token hash
</script>

<script>
$(document).ready(function() {
    oTable = $('#SLData').dataTable({
        "aaSorting": [
            [1, "desc"],
        ],
        "aLengthMenu": [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "<?=lang('all')?>"]
        ],
        "iDisplayLength": <?=$Settings->rows_per_page?>,
        'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': '<?=admin_url('payments/getData'); ?>',
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
        "aoColumns": [{
            "bSortable": false,
            "mRender": checkbox,
            "sWidth": "20px"
        }, {
            "mRender": fsd
        }, null, null, null, null, null, null, {
            "bSortable": false
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
            filter_default_label: "[<?=lang('Customer');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 4,
            filter_default_label: "[<?=lang('Payments_Type');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 5,
            filter_default_label: "[<?=lang('Method');?>]",
            filter_type: "text",
            data: []
        }, {
            column_number: 7,
            filter_default_label: "[<?=lang('Created_By');?>]",
            filter_type: "text",
            data: []
        }

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
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?=lang('Payments') ;?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <?= anchor('admin/payments/add', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');?>
                        </li>

                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?=lang("Delete")?></b>"
                                data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                                data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?=lang('Delete')?>
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
                <table id="SLData" class="table table-bordered table-hover table-striped" cellpadding="0" border="0">
                    <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check" />
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("Customer"); ?></th>
                            <th><?= lang("Payments_Type"); ?></th>
                            <th><?= lang("Method"); ?></th>
                            <th><?= lang("Amount"); ?></th>
                            <th><?= lang("Created_By"); ?></th>
                            <th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang("loading_data"); ?></td>
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

                            <th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</div>