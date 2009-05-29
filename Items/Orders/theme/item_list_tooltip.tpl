<table>
	<tr>
		<th nowrap="1">
			{$header.sku}
		</th>
		<th nowrap="1">
			{$header.item_name}
		</th>
		<th nowrap="1">
			{$header.quantity}
		</th>
		<th nowrap="1">
			{$header.net_price}
		</th>
		<th nowrap="1">
			{$header.tax}
		</th>
		<th nowrap="1">
			{$header.gross_price}
		</th>
	</tr>
	{foreach item=i from=$items}
		<tr>
			<td nowrap="1" style="padding:2px; border-right:1px solid; border-bottom:1px solid;">
				{$i.sku}
			</td>
			<td nowrap="1" style="padding:2px; border-right:1px solid; border-bottom:1px solid;">
				{$i.item_name}
			</td>
			<td nowrap="1" style="padding:2px; border-right:1px solid; border-bottom:1px solid;" align="right">
				{$i.quantity}
			</td>
			<td nowrap="1" style="padding:2px; border-right:1px solid; border-bottom:1px solid;" align="right">
				{$i.net_price}
			</td>
			<td nowrap="1" style="padding:2px; border-right:1px solid; border-bottom:1px solid;">
				{$i.tax}
			</td>
			<td nowrap="1" style="padding:2px; border-right:1px solid; border-bottom:1px solid;" align="right">
				{$i.gross_price}
			</td>
		</tr>
	{/foreach}
</table>
