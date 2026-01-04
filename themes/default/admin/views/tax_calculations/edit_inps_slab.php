<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$slab = isset($slab) ? $slab : NULL;
$is_edit = $slab ? true : false;
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-<?= $is_edit ? 'edit' : 'plus' ?>"></i><?= $is_edit ? lang('edit') : lang('add') ?> <?= lang('inps_slab'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php if ($error) { ?>
                    <div class="alert alert-danger">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?= is_array($error) ? print_r($error, true) : $error; ?>
                    </div>
                <?php } ?>

                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open('tax_calculations/edit_inps_slab' . ($is_edit ? '?id=' . $slab->id : ''), $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?= lang('year', 'slab_year'); ?>
                            <?php
                            $year_options = array();
                            $current_year = date('Y');
                            for ($y = $current_year - 5; $y <= $current_year + 5; $y++) {
                                $year_options[$y] = $y;
                            }
                            echo form_dropdown('slab_year', $year_options, ($slab ? $slab->slab_year : $current_year), 'class="form-control select" id="slab_year" required="required"');
                            ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?= lang('customer_type', 'customer_type'); ?>
                            <?php
                            echo form_dropdown('customer_type', $customer_types, ($slab ? $slab->customer_type : ''), 'class="form-control select" id="customer_type"');
                            ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?= lang('income_from', 'income_from'); ?>
                            <?php
                            echo form_input('income_from', ($slab ? $slab->income_from : ''), 'class="form-control" id="income_from" required="required" type="number" step="0.01"');
                            ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?= lang('income_to', 'income_to'); ?>
                            <?php
                            echo form_input('income_to', ($slab ? $slab->income_to : ''), 'class="form-control" id="income_to" type="number" step="0.01" placeholder="' . lang('leave_empty_for_unlimited') . '"');
                            ?>
                            <small class="help-block"><?= lang('leave_empty_for_unlimited_help'); ?></small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?= lang('inps_rate', 'inps_rate'); ?> (%)
                            <?php
                            echo form_input('inps_rate', ($slab ? $slab->inps_rate : ''), 'class="form-control" id="inps_rate" type="number" step="0.01" placeholder="' . lang('optional') . '"');
                            ?>
                            <small class="help-block"><?= lang('inps_rate_help_text'); ?></small>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?= lang('fixed_amount', 'fixed_amount'); ?>
                            <?php
                            echo form_input('fixed_amount', ($slab ? $slab->fixed_amount : ''), 'class="form-control" id="fixed_amount" type="number" step="0.01" placeholder="' . lang('optional') . '"');
                            ?>
                            <small class="help-block"><?= lang('fixed_amount_help'); ?></small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?= lang('description', 'description'); ?>
                            <?php
                            echo form_textarea('description', ($slab ? $slab->description : ''), 'class="form-control" id="description" rows="3"');
                            ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <?php
                                    $is_active = ($slab && $slab->is_active == 1) ? true : (!$slab ? true : false);
                                    echo form_checkbox('is_active', '1', $is_active, 'id="is_active"');
                                    ?>
                                    <?= lang('is_active'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <?php echo form_submit('add_inps_slab', ($is_edit ? lang('update') : lang('add')) . ' ' . lang('inps_slab'), 'class="btn btn-primary"'); ?>
                    <a href="<?= admin_url('tax_calculations/inps_slabs') ?>" class="btn btn-default"><?= lang('cancel') ?></a>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.select').select2();
    });
</script>

