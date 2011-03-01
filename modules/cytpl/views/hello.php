
<h1><?php echo $a?></h1>

<?php if ($a == 'Hello CyTpl') : ?>

    <strong>fuck yeah :D</strong>
<?php elseif ($a == 'omg') : ?>
    <strong>asdasd</strong>
<?php else : ?>
    <strong>asdasd</strong>
<?php endif; ?>

<form <?php foreach ( $formtag as $k => $v) echo ' ' . $k . '="' . $v . '"'; ?>>

<?php foreach ($arr as $itm) : ?>
    <ul>
        <li><?php echo $itm?></li>
    </ul>
<?php endforeach; ?>

<?php foreach ($arr as $key => $value) : ?>
    <?php echo $key?> => <?php echo $value?><br/>
<?php endforeach; ?>