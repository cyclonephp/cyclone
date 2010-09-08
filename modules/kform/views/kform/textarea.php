<div class="input">
<? if (array_key_exists('errors', $model)) : ?>
<? foreach ($model['errors'] as $error) : ?>
    <div class="error"><?=$error?></div>
<? endforeach; ?>
<? endif; ?>
    <label for="<?=$model['name']?>"><?=$model['label']?></label>
    <textarea name="<?= Arr::get($model, 'name')?>" cols="<?= Arr::get($model, 'cols')?>" rows="<?= Arr::get($model, 'rows')?>">
    <?= Arr::get($model, 'value')?>
    </textarea>
    <div class="clear"></div>
</div>
