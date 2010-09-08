<div class="input">
<? if (array_key_exists('errors', $model)) : ?>
<? foreach ($model['errors'] as $error) : ?>
    <div class="error"><?=$error?></div>
<? endforeach; ?>
<? endif; ?>
    <label for="<?=$model['name']?>"><?=$model['label']?>
    <? if (array_key_exists('description', $model)) echo "<div class='descr'>${model['description']}</div>";?>
    </label>
    <select name="<?= $model['name']?>">
    <? foreach ($model['items'] as $item) : ?>
    <option value="<?=$item['value']?>" <?= Arr::get($model, 'value') == $item['value'] ? 'selected' : ''?>><?=$item['text']?></option>
    <? endforeach; ?>
    </select>
    <div class="clear"></div>
</div>