<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
           
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url() . 'assets/uploads/logos/WhatsApp_Image_2024-.jpeg'; ?>"
                         alt="biller">
                </div>
            
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                    <p class="bold">
                        <?= lang("date"); ?>: <?= $this->sma->hrsd($inv->date); ?><br>
                        <?= lang("ref"); ?>: <?= $inv->reference_no; ?><br>
                       
                    </p>
                    </div>
                    <div class="col-xs-7 text-right order_barcodes">
                        <?= $this->sma->qrcode('link', urlencode(admin_url('communication/model_view/' . $inv->id)), 2); ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="row" style="margin-bottom:15px;">

               
                <div class="col-xs-6">
                    <?php echo $this->lang->line("Customer"); ?>:<br/>
                    <h2 style="margin-top:10px;"><?= $customer->name.' '.$customer->last_name; ?></h2>
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

                <div class="col-xs-6">
                    <span style="font-weight:bold;"><?= lang('warehouse'); ?></span>:<br>
                    <?= $warehouse->name ?>
                    <?php
                    echo $warehouse->address . '<br>';
                    echo ($warehouse->phone ? lang('tel') . ': ' . $warehouse->phone . '<br>' : '') . ($warehouse->email ? lang('email') . ': ' . $warehouse->email : '');
                    ?>
                    <?php
                    if(isset($service_provider)){ ?>
                        <br><span><?=lang('assign_service_provider')?>: <?php echo $service_provider->first_name.' '.$service_provider->last_name;?></span>
                    <?php } ?>
                    <?php
                    if(isset($assign_marketing_officers)){ ?>
                        <br><span><?=lang('marketing_officer')?>: <?php echo $assign_marketing_officers->first_name.' '.$assign_marketing_officers->last_name;?></span>
                    <?php } ?>
                    <div class="clearfix"></div>
                </div>

            </div>

          

            

            <div class="row">
                <div class="col-xs-12">
                    
                            <div class="well well-sm">
                                <p class="bold"><?= lang("note"); ?>:</p>
                                <div><?= $this->sma->decode_html($inv->note); ?></div>
                            </div>
                       
                </div>

              

                <div class="col-xs-5 pull-right">
                    <div class="well well-sm">
                        <p>
                            <?= lang("created_by"); ?>: <?= $inv->created_by ? $created_by->first_name . ' ' . $created_by->last_name : $customer->name; ?> <br>
                            <?= lang("date"); ?>: <?= $this->sma->hrld($inv->created_date); ?>
                        </p>
                        <?php if ($inv->updated_by) { ?>
                        <p>
                            <?= lang("updated_by"); ?>: <?= $updated_by->first_name . ' ' . $updated_by->last_name;; ?><br>
                            <?= lang("Updated_at"); ?>: <?= $this->sma->hrld($inv->updated_at); ?>
                        </p>
                        <?php } ?>
                    </div>
                </div>
                
            
        </div>
        
        <div class="row " style="margin:2px;">
            
         <?php if ($inv->id) { ?>
                        <div class="btn-group">
                            <a href="<?= admin_url('communication/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                <i class="fa fa-edit"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("Delete") ?></b>"
                                data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('communication/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                data-html="true" data-placement="top">
                                <i class="fa fa-trash-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                            </a>
                        </div>
                        <?php } ?>
          
            </div>   
           
        </div>
        
    </div>
</div>
<script type="text/javascript">
    $(document).ready( function() {
        $('.tip').tooltip();
    });
</script>
