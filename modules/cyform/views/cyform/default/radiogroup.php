<div class="input">
    <? if (isset($errors)) foreach ($errors as $error) : ?>
    <div class="error"><?= $error ?></div>
    <? endforeach; ?>
    <label for="<?= $name ?>"><?= $label ?>
<? if (isset($description)) : ?>
    <span class="descr"> <?= $description ?></span>
<? endif; ?>
    </label>
    <div class="radiogroup">
        <? foreach ($items as $val => $text) : ?>
        <input type="radio" value="<?= $val ?>" <?= HTML::attributes($attributes) ?>/><?= $text ?><br/>
        <? endforeach; ?>
    </div>
    <div class="clear"></div>
</div>