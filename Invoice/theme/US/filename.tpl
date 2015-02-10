{if $order.transaction_type == 0}
    {if $order.status < 2}
        {"Purchase Quote"|t}
    {else}
        {"Purchase Order"|t}
    {/if}
{else}
    {if $order.status == 4}
        {"Packing List"|t}
    {else}
        {if $order.status > 2}
            {"Invoice"|t}
        {else}
            {"Sales Quote"|t}
        {/if}
    {/if}
{/if}