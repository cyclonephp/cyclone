<div class="input">
<? if (array_key_exists('errors', $model)) : ?>
<? foreach ($model['errors'] as $error) : ?>
    <div class="error"><?=$error?></div>
<? endforeach; ?>
<? endif; ?>
    <label for="<?=$model['name']?>"><?=$model['label']?></label>
    <div class="group">
    <? foreach ($model['items'] as $item) : ?>
    <input type="radio" name="<?= $model['name']?>" value="<?=$item['value']?>" <?= get('value', $model) == $item['value'] ? 'checked' : ''?>/><?=$item['text']?><br/>
    <? endforeach; ?>
    </div>
    <div class="clear"></div>
</div>
