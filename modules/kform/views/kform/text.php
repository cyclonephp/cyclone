<div class="input">
<? if (array_key_exists('errors', $model)) : ?>
<? foreach ($model['errors'] as $error) : ?>
    <div class="error"><?=$error?></div>
<? endforeach; ?>
<? endif; ?>
    <label for="<?=$model['name']?>"><?=$model['label']?>
    <? if (array_key_exists('description', $model)) echo "<span class=\"descr\">${model['description']}</span>";?>
    </label>
    <input type="text" name="<?= Arr::get($model, 'name')?>" value="<?= Arr::get($model, 'value')?>" <?if (array_key_exists('attributes', $model)) foreach ($model['attributes'] as $k => $v) echo " $k=\"$v\"";?>/>
    <div class="clear"></div>
</div>