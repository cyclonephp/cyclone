<fieldset <? if (array_key_exists('width', $model)) : ?> style="width: <?= $model['width'] ?>"<? endif; ?>>
			<? if (array_key_exists('title', $model)) : ?><legend><?= $model['title'] ?></legend><? endif; ?>
            <? foreach ($model['errors'] as $err) : ?>
                <div class="error"><?= $err ?></div>
            <? endforeach; ?>
            <form <?php foreach ($model['attributes'] as $k => $v) echo "$k=\"$v\" ";?>>
            <?php foreach ($model['fields'] as $field) : ?>
            <?php echo $field['view']?>
            <?php endforeach; ?>
            </form>
</fieldset>
