
<h1><?php echo $a?></h1>

<?php if ($a == 'Hello CyTpl') : ?>

    <strong>fuck yeah :D</strong>
<?php elseif ($a == 'omg') : ?>
    <strong>asdasd</strong>
<?php else : ?>
    <strong>asdasd</strong>
<?php endif; ?>

<form <?php foreach ( $formtag as $k => $v) echo ' ' . $k . '="' . $v . '"'; ?>>

<ul>
<?php foreach ($arr as $itm) : ?>
        <li><?php echo $itm?></li>
<?php endforeach; ?>
</ul>

<?php foreach ($arr as $key => $value) : ?>
    <?php echo $key?> => <?php echo $value?><br/>
<?php endforeach; ?>

<?php if (array_key_exists('method', $formtag)) : ?>

<?php endif; ?>