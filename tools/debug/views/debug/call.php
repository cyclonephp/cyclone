<tr>
    <td><?= $routine ?></td>
    <td><?= $file ?></td>
    <td>
        <a id="<?= $id ?>" class="btn-args">view</a>
    </td>
</tr>
<tr class="arguments" id="args-<?= $id ?>">
    <td colspan="3">
        <?= $arguments ?>
        <input type="button" value="hide" data-id="<?= $id ?>" class="btn-hide"/>
    </td>
</tr>