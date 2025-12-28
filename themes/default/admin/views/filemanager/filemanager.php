<link rel="stylesheet" type="text/css"
      href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css"/>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url('vendor/studio-42/elfinder/css/elfinder.min.css'); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo base_url('vendor/studio-42/elfinder/css/theme.css'); ?>">


<script src="<?php echo base_url('vendor/studio-42/elfinder/js/elfinder.min.js'); ?>"></script>

<?php
?>
<style>
    .ui-dialog{
        background-color: #dbdee0;
    }
</style>
<script type="text/javascript" charset="utf-8">

        $().ready(function () {
            window.setTimeout(function () {
                var locale = "<?= $languages->code;?>";
                var _locale = locale;
                if (locale == 'pt') {
                    _locale = 'pt_BR';
                }
                var elf = $('#elfinder').elfinder({
                    url: '<?= admin_url()?>document/elfinder_init',  // connector URL (REQUIRED)
                   // lang: _locale,
                    height: 700,
                    uiOptions: {
                        toolbar: [
                            ['back', 'forward'],
                            ['mkdir', 'upload'],
                            ['info'],
                            ['quicklook'],
                            ['copy','paste'],
                            ['rm'],
                            ['duplicate', 'rename', 'edit', 'resize'],
                            ['extract', 'archive'],
                            ['search'],
                            ['fullscreen'],
                            ['view'],
                        ],
                    }

                }).elfinder('instance');
            }, 200);
        });
</script>

<!-- Element where elFinder will be created (REQUIRED) -->
<div class="panel panel-custom">
        <div id="elfinder" style="background-color:ghostwhite"></div>
      </div>

</div>