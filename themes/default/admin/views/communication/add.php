<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
var count = 1,


    //var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    //var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
    $(document).ready(function() {
        //  $('#add_sale').prop('disabled', false);

        // Enable the submit button

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

        // Same as Add Sale: default to today when no stored date, else restore from localStorage
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
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Add_Communication'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("communication/add", $attrib);
                
                ?>
                <div class="row">
                    <div class="col-lg-12">

                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("date", "sldate"); ?>
                                <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->sma->hrsd(date('Y-m-d'))), 'class="form-control input-tip date" id="sldate" required="required"'); ?>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("reference_no", "slref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $slnumber), 'class="form-control input-tip" id="slref" required="required"'); ?>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading">
                                </div>
                                <div class="panel-body" style="padding: 5px;">
                                    <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("warehouse", "slwarehouse"); ?>
                                            <?php
                                                $wh[''] = '';
                                                foreach ($warehouses as $warehouse) {
                                                    $wh[$warehouse->id] = $warehouse->name;
                                                }
                                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
                                                ?>
                                        </div>
                                    </div>
                                    <?php } else {
                                        $warehouse_input = array(
                                            'type' => 'hidden',
                                            'name' => 'warehouse',
                                            'id' => 'slwarehouse',
                                            'value' => $this->session->userdata('warehouse_id'),
                                        );

                                        echo form_input($warehouse_input);
                                    } ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("customer", "slcustomer"); ?>
                                            <?php
                                            echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="slcustomer" placeholder="' . lang("customer") . '" required="required" class="form-control input-tip"'); ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($has_assign_status)) { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="assign_id">Assign To</label>
                                            <?php
                                            $users_opts = array('' => lang('select'));
                                            if (!empty($users)) {
                                                foreach ($users as $u) {
                                                    $users_opts[$u->id] = $u->first_name . ' ' . $u->last_name;
                                                }
                                            }
                                            $default_assign = isset($_POST['assign_id']) ? $_POST['assign_id'] : (isset($default_assign_id) ? $default_assign_id : '');
                                            echo form_dropdown('assign_id', $users_opts, $default_assign, 'id="assign_id" class="form-control" required="required"');
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <?php
                                            echo form_dropdown('status', isset($status_options) ? $status_options : array('New' => 'New'), (isset($_POST['status']) ? $_POST['status'] : 'New'), 'id="status" class="form-control" required="required"');
                                            ?>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>

                        </div>



                        <div class="row" id="bt">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <?= lang("note", "slnote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="slnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="attachment"><?= lang('attachment'); ?> (video/audio/PDF/DOC)</label>
                                <input type="file" name="attachment[]" id="attachment" class="form-control file"
                                    data-browse-label="<?= lang('browse'); ?>" data-show-upload="false"
                                    data-show-preview="false" multiple
                                    accept=".pdf,.doc,.docx,.mp4,.webm,.avi,.mov,.mp3,.wav,.ogg,.m4a">
                                <p class="help-block">PDF, DOC, DOCX, MP4, WebM, MP3, WAV, etc. (max 20MB per file)
                                </p>
                            </div>
                            <div class="form-group">
                                <label>Record voice (like WhatsApp)</label>
                                <div class="voice-record-wrapper">
                                    <button type="button" id="btn-record-voice" class="btn btn-default"><i
                                            class="fa fa-microphone"></i> Record voice</button>
                                    <button type="button" id="btn-stop-voice" class="btn btn-danger"
                                        style="display:none;"><i class="fa fa-stop"></i> Stop</button>
                                    <span id="voice-status" class="text-muted small"></span>
                                </div>
                                <input type="text" id="voice-file-name" class="form-control" readonly
                                    placeholder="Recorded file name will appear here"
                                    style="margin-top:6px; background:#fff; cursor:default;">
                                <input type="file" name="attachment[]" id="voice_file" class="hidden" accept="audio/*">
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
    // Voice recording (WhatsApp-style)
    var mediaRecorder = null,
        recordedChunks = [];
    var $btnRecord = $('#btn-record-voice'),
        $btnStop = $('#btn-stop-voice'),
        $status = $('#voice-status');
    $('#btn-record-voice').on('click', function() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            $status.text(
                'Recording not supported in this browser. Use a modern browser (Chrome, Firefox, Edge).'
            );
            return;
        }
        navigator.mediaDevices.getUserMedia({
            audio: true
        }).then(function(stream) {
            recordedChunks = [];
            var mimeType = 'audio/webm';
            var ext = 'webm';
            if (typeof MediaRecorder !== 'undefined' && MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported('audio/mp4')) {
                mimeType = 'audio/mp4';
                ext = 'm4a';
            }
            try {
                mediaRecorder = new MediaRecorder(stream, mimeType ? { mimeType: mimeType } : undefined);
            } catch (e) {
                mediaRecorder = new MediaRecorder(stream);
                mimeType = (mediaRecorder.mimeType || '').indexOf('mp4') !== -1 ? 'audio/mp4' : 'audio/webm';
                ext = mimeType.indexOf('mp4') !== -1 ? 'm4a' : 'webm';
            }
            mediaRecorder.ondataavailable = function(e) {
                if (e.data.size > 0) recordedChunks.push(e.data);
            };
            mediaRecorder.onstop = function() {
                stream.getTracks().forEach(function(t) {
                    t.stop();
                });
                if (recordedChunks.length === 0) return;
                var blob = new Blob(recordedChunks, { type: mimeType });
                var fileName = 'voice_' + new Date().getTime() + '.' + ext;
                var file = new File([blob], fileName, { type: mimeType });
                var dt = new DataTransfer();
                dt.items.add(file);
                document.getElementById('voice_file').files = dt.files;
                $('#voice-file-name').val(file.name);
                $status.text('Voice recorded. It will be uploaded with the form.');
            };
            mediaRecorder.start();
            $btnRecord.hide();
            $btnStop.show();
            $status.text('Recording...');
        }).catch(function(err) {
            $status.text('Microphone access denied or error: ' + (err.message || err));
        });
    });
    $('#btn-stop-voice').on('click', function() {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
            $btnStop.hide();
            $btnRecord.show();
        }
    });
});
</script>