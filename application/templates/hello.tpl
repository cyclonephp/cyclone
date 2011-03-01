
<h1>{$a}</h1>

{if $a == 'Hello CyTpl'}

    <strong>fuck yeah :D</strong>
{else if $a == 'omg'}
    <strong>asdasd</strong>
{else}
    <strong>asdasd</strong>
{/if}

<form {attrs $formtag}>

{foreach $arr as $itm}
    <ul>
        <li>{$itm}</li>
    </ul>
{/foreach}

{foreach $arr as $key => $value}
    {$key} => {$value}<br/>
{/foreach}