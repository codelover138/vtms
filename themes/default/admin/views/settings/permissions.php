<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .table td:first-child {
        font-weight: bold;
    }

    label {
        margin-right: 10px;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('group_permissions'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang("set_permissions"); ?></p>

                <?php if (!empty($p)) {
                    if ($p->group_id != 1) {

                        echo admin_form_open("system_settings/permissions/" . $id); ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped reports-table">

                                <thead>
                                <tr>
                                    <th colspan="6"
                                        class="text-center"><?php echo $group->description . ' ( ' . $group->name . ' ) ' . $this->lang->line("group_permissions"); ?></th>
                                </tr>
                                <tr>
                                    <th rowspan="2" class="text-center"><?= lang("module_name"); ?>
                                    </th>
                                    <th colspan="5" class="text-center"><?= lang("permissions"); ?></th>
                                </tr>
                                <tr>
                                    <th class="text-center"><?= lang("view"); ?></th>
                                    <th class="text-center"><?= lang("add"); ?></th>
                                    <th class="text-center"><?= lang("edit"); ?></th>
                                    <th class="text-center"><?= lang("delete"); ?></th>
                                    <th class="text-center"><?= lang("misc"); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?= lang("products"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-index" <?php echo $p->{'products-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-add" <?php echo $p->{'products-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-edit" <?php echo $p->{'products-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-delete" <?php echo $p->{'products-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-price" class="checkbox" name="products-price" <?php echo $p->{'products-price'} ? "checked" : ''; ?>>
                                            <label for="products-price" class="padding05"><?= lang('product_price') ?></label>
                                        </span>

                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang("sales"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-index" <?php echo $p->{'sales-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-add" <?php echo $p->{'sales-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-edit" <?php echo $p->{'sales-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-delete" <?php echo $p->{'sales-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-email" class="checkbox" name="sales-email" <?php echo $p->{'sales-email'} ? "checked" : ''; ?>>
                                            <label for="sales-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-pdf" class="checkbox" name="sales-pdf" <?php echo $p->{'sales-pdf'} ? "checked" : ''; ?>>
                                            <label for="sales-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-payments" class="checkbox" name="sales-payments" <?php echo $p->{'sales-payments'} ? "checked" : ''; ?>>
                                            <label for="sales-payments" class="padding05"><?= lang('payments') ?></label>
                                        </span>

                                    </td>
                                </tr>
                                
                                   <tr>
                                    <td><?= lang("Communication"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="communication-index" <?php echo $p->{'communication-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="communication-add" <?php echo $p->{'communication-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="communication-edit" <?php echo $p->{'communication-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="communication-delete" <?php echo $p->{'communication-delete'} ? "checked" : ''; ?>>
                                    </td>
                                   
                                </tr>


                                <tr>
                                    <td><?= lang("purchases"); ?></td>
                                    <td class="text-center">
                                    </td>
                                    <td class="text-center">
                                    </td>
                                    <td class="text-center">
                                    </td>
                                    <td class="text-center">
                                    </td>
                                    <td>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-expenses" class="checkbox" name="purchases-expenses" <?php echo $p->{'purchases-expenses'} ? "checked" : ''; ?>>
                                            <label for="purchases-expenses" class="padding05"><?= lang('expenses') ?></label>
                                        </span>

                                    </td>
                                </tr>
                                
                        


                                <tr>
                                    <td><?= lang("customers"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-index" <?php echo $p->{'customers-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-add" <?php echo $p->{'customers-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-edit" <?php echo $p->{'customers-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-delete" <?php echo $p->{'customers-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="customers-deposits" class="checkbox" name="customers-deposits" <?php echo $p->{'customers-deposits'} ? "checked" : ''; ?>>
                                            <label for="customers-deposits" class="padding05"><?= lang('deposits') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="customers-delete_deposit" class="checkbox" name="customers-delete_deposit" <?php echo $p->{'customers-delete_deposit'} ? "checked" : ''; ?>>
                                            <label for="customers-delete_deposit" class="padding05"><?= lang('delete_deposit') ?></label>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= lang("File_Manager"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" id="document-file_manager" class="checkbox" name="document-file_manager" <?php echo $p->{'document-file_manager'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                    </td>
                                    <td class="text-center">
                                    </td>
                                    <td class="text-center">
                                    </td>
                                    <td>
                                        <input type="checkbox" value="1" id="document-folder_create" class="checkbox" name="document-folder_create" <?php echo $p->{'document-folder_create'} ? "checked" : ''; ?>>
                                        <label for="document-folder_create" class="padding05"><?= lang('Folder_Create') ?></label>
                                        <input type="checkbox" value="1" id="document-folder_download" class="checkbox" name="document-folder_download" <?php echo $p->{'document-folder_download'} ? "checked" : ''; ?>>
                                        <label for="document-folder_download" class="padding05"><?= lang('Folder_Download') ?></label>
                                        <input type="checkbox" value="1" id="document-upload" class="checkbox" name="document-upload" <?php echo $p->{'document-upload'} ? "checked" : ''; ?>>
                                        <label for="document-upload" class="padding05"><?= lang('Upload') ?></label>
                                        <input type="checkbox" value="1" id="document-file_delete" class="checkbox" name="document-file_delete" <?php echo $p->{'document-file_delete'} ? "checked" : ''; ?>>
                                        <label for="document-file_delete" class="padding05"><?= lang('File_Delete') ?></label>


                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang("reports"); ?></td>
                                    <td colspan="5">

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="products"
                                            name="reports-products" <?php echo $p->{'reports-products'} ? "checked" : ''; ?>><label for="products" class="padding05"><?= lang('products') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="daily_sales" name="reports-daily_sales" <?php echo $p->{'reports-daily_sales'} ? "checked" : ''; ?>>
                                            <label for="daily_sales" class="padding05"><?= lang('daily_sales') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="monthly_sales" name="reports-monthly_sales" <?php echo $p->{'reports-monthly_sales'} ? "checked" : ''; ?>>
                                            <label for="monthly_sales" class="padding05"><?= lang('monthly_sales') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="sales" name="reports-sales" <?php echo $p->{'reports-sales'} ? "checked" : ''; ?>>
                                            <label for="sales" class="padding05"><?= lang('sales') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="payments" name="reports-payments" <?php echo $p->{'reports-payments'} ? "checked" : ''; ?>>
                                            <label for="payments" class="padding05"><?= lang('payments') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="tax" name="reports-tax" <?php echo $p->{'reports-tax'} ? "checked" : ''; ?>>
                                            <label for="tax" class="padding05"><?= lang('tax_report') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="expenses" name="reports-expenses" <?php echo $p->{'reports-expenses'} ? "checked" : ''; ?>>
                                            <label for="expenses" class="padding05"><?= lang('expenses') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="customers" name="reports-customers" <?php echo $p->{'reports-customers'} ? "checked" : ''; ?>>
                                            <label for="customers" class="padding05"><?= lang('customers') ?></label>
                                        </span>

                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang("misc"); ?></td>
                                    <td colspan="5">
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                            name="bulk_actions" <?php echo $p->bulk_actions ? "checked" : ''; ?>>
                                            <label for="bulk_actions" class="padding05"><?= lang('bulk_actions') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="edit_price"
                                            name="edit_price" <?php echo $p->edit_price ? "checked" : ''; ?>>
                                            <label for="edit_price" class="padding05"><?= lang('edit_price_on_sale') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                </tbody>
                            </table>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><?=lang('update')?></button>
                        </div>
                        <?php echo form_close();
                    } else {
                        echo $this->lang->line("group_x_allowed");
                    }
                } else {
                    echo $this->lang->line("group_x_allowed");
                } ?>


            </div>
        </div>
    </div>
</div>
