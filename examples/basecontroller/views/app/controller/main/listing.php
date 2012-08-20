<table>
    <caption><?= $caption ?></caption>
    <thead>
    <tr>
        <th>key</th>
        <th>value</th>
    </tr>
    </thead>
    <tbody>
    <? foreach ($data as $name => $value) : ?>
    <tr>
        <td><?= $name ?></td>
        <td><?= $value ?></td>
    </tr>
        <? endforeach; ?>
    </tbody>
</table>