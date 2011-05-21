<div>
        <? foreach ($items as $name => $val) : ?>
            <?= $name ?>
        <span class="arg-val">
            <pre><?= $val ?></pre>
        </span>
        <? endforeach; ?>
</div>