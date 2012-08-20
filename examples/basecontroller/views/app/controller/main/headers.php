<h1>Headers demo</h1>

<table>
    <thead>
        <tr>
            <th>Header key</th>
            <th>Header value</th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($headers as $name => $value) : ?>
    <tr>
        <td><?= $name ?></td>
        <td><?= $value ?></td>
    </tr>
    <? endforeach; ?>
    </tbody>
</table>