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
{*		<th nowrap="1">
			{$header.net_price}
		</th>
		<th nowrap="1">
			{$header.tax}
		</th>*}
		<th nowrap="1">
			{$header.gross_price}
		</th>
	</tr>
	{foreach item=i from=$items}
		<tr>
			<td nowrap="1">
				{$i.sku}
			</td>
			<td nowrap="1">
				{$i.item_name}
			</td>
			<td nowrap="1">
				{$i.quantity}
			</td>
{*			<td nowrap="1">
				{$i.net_price}
			</td>
			<td nowrap="1">
				{$i.tax}
			</td>*}
			<td nowrap="1">
				{$i.gross_price}
			</td>
		</tr>
	{/foreach}
</table>
