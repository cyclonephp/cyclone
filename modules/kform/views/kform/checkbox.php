<div class="input">
<? if (array_key_exists('errors', $model)) : ?>
<? foreach ($model['errors'] as $error) : ?>
    <div class="error"><?=$error?></div>
<? endforeach; ?>
<? endif; ?>
    <label for="<?=$model['name']?>"><?=$model['label']?>
    <? if (array_key_exists('description', $model)) echo "<span class=\"descr\">${model['description']}</span>";?>
    </label>
    <input type="checkbox" name="<?php echo $model['name']?>"<?php if (array_key_exists('attributes', $model)) foreach ($model['attributes'] as $k => $v) echo " $k=\"$v\""; if (Arr::get($model, 'value')) echo ' checked="checked"';?>/>
    <? if (array_key_exists('attributes', $model) && array_key_exists('disabled', $model['attributes'])) : ?><input type="hidden" name="<?=$model['name']?>" value="<?=Arr::get($model, 'value')?>"/><? endif; ?>
    <div class="clear"></div>
</div>
<br/>