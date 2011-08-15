
<h1>{$a}</h1>

{if $a == 'Hello CyTpl'}

    <strong>fuck yeah :D</strong>
{else if $a == 'omg'}
    <strong>asdasd</strong>
{else}
    <strong>asdasd</strong>
{/if}

<form {attrs $formtag}>

<ul>
{foreach $arr as $itm}
        <li>{$itm}</li>
{/foreach}
</ul>

{foreach $arr as $key => $value}
    {$key} => {$value}<br/>
{/foreach}

{if exists $formtag[method]}
    
{/if}