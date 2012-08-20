<h1>Request details</h1>

<dl>
    <dt>Request method:</dt>
        <dd><?= $method ?></dd>
    <dt>Ajax request?</dt>
        <dd><?= $is_ajax ?></dd>
    <dt>User agent:</dt>
        <dd><?= $user_agent ?></dd>
    <dt>Protocol:</dt>
        <dd><?= $protocol ?></dd>
</dl>

<table>
    <caption>Query string parameters</caption>
    <thead>
    <tr>
        <th>key</th>
        <th>value</th>
    </tr>
    </thead>
    <tbody>
    <? foreach ($query as $name => $value) : ?>
    <tr>
        <td><?= $name ?></td>
        <td><?= $value ?></td>
    </tr>
        <? endforeach; ?>
    </tbody>
</table>


<table>
    <caption>POSTDATA</caption>
    <thead>
    <tr>
        <th>key</th>
        <th>value</th>
    </tr>
    </thead>
    <tbody>
    <? foreach ($post as $name => $value) : ?>
    <tr>
        <td><?= $name ?></td>
        <td><?= $value ?></td>
    </tr>
        <? endforeach; ?>
    </tbody>
</table>

<table>
    <caption>Cookies</caption>
    <thead>
    <tr>
        <th>key</th>
        <th>value</th>
    </tr>
    </thead>
    <tbody>
    <? foreach ($cookies as $name => $value) : ?>
    <tr>
        <td><?= $name ?></td>
        <td><?= $value ?></td>
    </tr>
        <? endforeach; ?>
    </tbody>
</table>