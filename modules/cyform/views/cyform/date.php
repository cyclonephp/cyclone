<div class="input">
    <? if (isset($errors)) foreach ($errors as $error) : ?>
    <div class="error"><?= $error ?></div>
    <? endforeach; ?>
    <label><?= $label ?>
<? if (isset($description)) : ?>
    <span class="descr"> <?= $description ?></span>
<? endif; ?>
    </label>
    <div class="select-cnt">
    <? foreach ($segments as $segment) {
        echo Form::select($segment['name'], $segment['items'], $segment['value']);
    } ?>
    </div>
    <div class="clear"></div>
</div>