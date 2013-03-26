<center>

    <table id="Premium_Warehouse_Invoice" cellspacing="0" cellpadding="0" style="margin: 10px;">
        <tr>

            {assign var=x value=0}
            {foreach item=i from=$icons}
            {assign var=x value=$x+1}

            <td>
                <a {$i.href}>
                    <div class="big-button">
                        {$i.label}
                    </div>
                </a>
            </td>

            {if ($x%3)==0}
        </tr>
        <tr>
            {/if}

            {/foreach}

        </tr>
    </table>

</center>
