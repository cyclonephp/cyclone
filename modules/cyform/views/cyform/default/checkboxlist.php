<div class="input">
    <? if (isset($errors)) foreach ($errors as $error) : ?>
    <div class="error"><?= $error ?></div>
    <? endforeach; ?>
    <label for="<?= $name ?>"><?= $label ?>
<? if (isset($description)) : ?>
    <span class="descr"> <?= $description ?></span>
<? endif; ?>
    </label>
    <div class="checkboxlist">
    <? foreach ($items as $val => $text) : ?>
        <input type="checkbox" <?= HTML::attributes($attributes) ?> value="<?= $val ?>"<? if (in_array($val, $values)) echo ' checked="checked"'?>/><?= $text ?><br/>
    <? endforeach; ?>
    </div>
    <div class="clear"></div>
</div>