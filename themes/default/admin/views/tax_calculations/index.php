<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
.dropdown-menu .input-group {
    margin: 0;
}
.dropdown-menu .input-group .form-control {
    border-radius: 4px 0 0 4px;
    border-right: 0;
}
.dropdown-menu .input-group .input-group-btn .btn {
    border-radius: 0 4px 4px 0;
    border-left: 0;
}
.dropdown-menu li.dropdown-header {
    padding: 5px 15px;
    font-weight: 600;
    color: #333;
    font-size: 12px;
    text-transform: uppercase;
}
.dropdown-menu > li > a {
    padding: 8px 15px;
}
.dropdown-menu > li > a.tip {
    display: block;
    clear: both;
    font-weight: normal;
    line-height: 1.42857143;
    color: #333;
    white-space: nowrap;
}
.dropdown-menu > li > a.tip:hover,
.dropdown-menu > li > a.tip:focus {
    text-decoration: none;
    color: #262626;
    background-color: #f5f5f5;
}
.dropdown-menu .input-group,
.dropdown-menu .input-group * {
    cursor: pointer;
}
.dropdown-menu .input-group input {
    cursor: text;
}
</style>
<script>
$(document).ready(function() {
    var tTable = $('#TaxData').dataTable({
        "aaSorting": [[1, "asc"]],
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
        "iDisplayLength": <?= $Settings->rows_per_page ?>,
        'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': '<?= admin_url('tax_calculations/getTaxCalculations') ?>',
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
        "aoColumns": [null, null, null, null, null, null, null, {
            "bSortable": false
        }],
        'fnRowCallback': function(nRow, aData, iDisplayIndex) {
            nRow.id = aData[0];
            return nRow;
        }
    }).dtFilter([
        {
            column_number: 1,
            filter_default_label: "[<?= lang('customer_name'); ?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 2,
            filter_default_label: "[<?= lang('company'); ?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 3,
            filter_default_label: "[<?= lang('customer_type'); ?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 4,
            filter_default_label: "[<?= lang('tax_regime'); ?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 5,
            filter_default_label: "[<?= lang('email'); ?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 6,
            filter_default_label: "[<?= lang('phone'); ?>]",
            filter_type: "text",
            data: []
        }
    ], "footer");

    // Prevent dropdown from closing when clicking on year input or input group
    $(document).on('click', '.dropdown-menu .input-group, .dropdown-menu .input-group *', function(e) {
        e.stopPropagation();
    });
    
    // Handle calculate button click
    $(document).on('click', '.calculate-tax-btn', function(e) {
        console.log($(this).data('customer-id'));
        e.preventDefault();
        e.stopPropagation();
        console.log($(this).data('customer-id'));
        var customer_id = $(this).data('customer-id');
        // Find the year input in the same dropdown menu
        var year_input = $(this).closest('.dropdown-menu').find('.year-input');
        var year = year_input.val() || new Date().getFullYear();
        
        // Close the dropdown
        $(this).closest('.btn-group').removeClass('open');
        
        bootbox.confirm("<?= lang('calculate_tax_for_year') ?> " + year + "?", function(result) {
            if (result) {
                $.ajax({
                    type: 'POST',
                    url: '<?= admin_url('tax_calculations/calculate') ?>',
                    data: {
                        customer_id: customer_id, 
                        year: year,
                        <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.error == 0) {
                            bootbox.alert(data.msg, function() {
                                window.location.href = '<?= admin_url('tax_calculations/view?customer_id=') ?>' + customer_id + '&year=' + year;
                            });
                        } else {
                            bootbox.alert(data.msg);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', status, error);
                        console.log('Response:', xhr.responseText);
                        bootbox.alert('Request failed: ' + error);
                    }
                });
            }
        });
    });
});
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calculator"></i><?= lang('tax_calculations'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="<?= admin_url('tax_calculations/inps_slabs') ?>" class="tip" title="<?= lang('manage_inps_rate_slabs') ?>">
                        <i class="icon fa fa-list"></i> <?= lang('inps_rate_slabs') ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="TaxData" cellpadding="0" cellspacing="0" border="0"
                        class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <tr class="primary">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th><?= lang("customer_name"); ?></th>
                                <th><?= lang("company"); ?></th>
                                <th><?= lang("customer_type"); ?></th>
                                <th><?= lang("tax_regime"); ?></th>
                                <th><?= lang("email"); ?></th>
                                <th><?= lang("phone"); ?></th>
                                <th style="min-width:100px; width:100px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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
                                <th style="min-width:100px; width:100px; text-align: center;"><?= lang("actions"); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
