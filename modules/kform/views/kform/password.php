<div class="input">
<? if (array_key_exists('errors', $model)) : ?>
<? foreach ($model['errors'] as $error) : ?>
    <div class="error"><?=$error?></div>
<? endforeach; ?>
<? endif; ?>
    <label for="<?=$model['name']?>"><?=$model['label']?></label>
    <input type="password" name="<?= Arr::get($model, 'name')?>" value="<?= Arr::get($model, 'value')?>"/>
    <div class="clear"></div>
</div>