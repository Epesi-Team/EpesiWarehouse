<table border="1">
	<tr>
		<td width="9%" align="center">
			<font size="7">
				{$lp}
			</font>
		</td>
		<td width="23%">
			<font size="7">
				&nbsp;{$details.item_details.item_name}
			</font>
		</td>
		<td width="9%">
			<font size="7">
				&nbsp;{if isset($details.sww)}{$details.sww}{/if}
			</font>
		</td>
		<td width="9%" align="center">
			<font size="7">
				{$details.quantity}
			</font>
		</td>
		<td width="7%" align="center">
			<font size="7">
				{$details.units}
			</font>
		</td>
		<td width="9%" align="right">
			<font size="7">
				{$details.net_price}&nbsp;
			</font>
		</td>
		<td width="7%" align="center">
			<font size="7">
				{$details.tax_name}
			</font>
		</td>
		<td width="9%" align="right">
			<font size="7">
				{$details.gross_total}&nbsp;
			</font>
		</td>
		<td width="9%" align="right">
			<font size="7">
				{$details.net_total}&nbsp;
			</font>
		</td>
		<td width="9%" align="right">
			<font size="7">
				{$details.tax_total}&nbsp;
			</font>
		</td>
	</tr>
</table>
