<base href="<?= URL::base() ?>"/>
<? foreach ($res['css'] as $file) : ?><link rel="stylesheet" type="text/css" href="<?= $file ?>"/>
<? endforeach; ?>
<? foreach ($res['js'] as $file) : ?><script type="text/javascript" src="<?= $file ?>"></script>
<? endforeach; ?>
<? if (count($server_params)) : ?>
<script type="text/javascript">
    if ( ! $) {
        $ = {};
    }
    $.cy = {params: <?= json_encode($server_params) ?>}
</script><? endif; ?>