<table border="1">
	<tr>
		<td width="6%" align="center">
			<font size="7">
				{$lp}
			</font>
		</td>
		<td width="20%">
			<font size="7">
				&nbsp;{$details.item_details.item_name}
			</font>
		</td>
		<td width="8%">
			<font size="7">
				&nbsp;{if isset($details.sww)}{$details.sww}{/if}
			</font>
		</td>
		<td width="8%" align="center">
			<font size="7">
				{$details.quantity}
			</font>
		</td>
		<td width="6%" align="center">
			<font size="7">
				{$details.units}
			</font>
		</td>
        <td width="8%" align="right">
            <font size="7">
                {$details.unit_price}&nbsp;
            </font>
        </td>
        <td width="6%" align="center">
            <font size="7">
                {$details.markup_discount_rate}
            </font>
        </td>
        <td width="8%" align="right">
			<font size="7">
				{$details.net_price}&nbsp;
			</font>
		</td>
		<td width="6%" align="center">
			<font size="7">
				{$details.tax_name}
			</font>
		</td>
		<td width="8%" align="right">
			<font size="7">
				{$details.gross_total}&nbsp;
			</font>
		</td>
		<td width="8%" align="right">
			<font size="7">
				{$details.net_total}&nbsp;
			</font>
		</td>
		<td width="8%" align="right">
			<font size="7">
				{$details.tax_total}&nbsp;
			</font>
		</td>
	</tr>
</table>
