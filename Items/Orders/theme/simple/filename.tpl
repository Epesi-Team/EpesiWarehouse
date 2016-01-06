{if $order.transaction_type == 0}
    {if $order.status < 2}
        {"Purchase Quote"|t}
    {else}
        {"Purchase Order"|t}
    {/if}
{elseif $order.transaction_type == 1}
    {if $order.status == 4}
        {"Packing List"|t}
    {else}
        {if $order.status > 2}
            {"Sale Order"|t}
        {else}
            {"Sale Quote"|t}
        {/if}
    {/if}
{else}
    {"Transaction"|t}
{/if}