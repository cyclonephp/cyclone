<? use cyclone as cy; ?>
<base href="<?= cy\URL::base() ?>"/>
<? foreach ($res['css'] as $file) : ?><link rel="stylesheet" type="text/css" href="<?= $file ?>"/>
<? endforeach; ?>
<? foreach ($res['js'] as $file) : ?><script type="text/javascript" src="<?= $file ?>"></script>
<? endforeach; ?>
<? if (count($js_params)) : ?>
<script type="text/javascript">
    if ( ! $) {
        $ = {};
    }
    $.cy = {params: <?= json_encode($js_params) ?>}
</script><? endif; ?>
