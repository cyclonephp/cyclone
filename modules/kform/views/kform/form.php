<fieldset class="kform">
    <? if (isset($title)) : ?><legend><?= $title ?></legend> <? endif; ?>
    <form  <?= HTML::attributes($attributes)?>>
        <? foreach($fields as $field) echo $field; ?>
    </form>
</fieldset>