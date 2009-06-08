<table>
	<tr>
		<th nowrap="1" width=100>
			{$header.distributor}
		</th>
		<th nowrap="1">
			{$header.price}
		</th>
		<th nowrap="1">
			{$header.quantity}
		</th>
		<th nowrap="1">
			{$header.quantity_info}
		</th>
	</tr>
	{foreach item=d from=$distros}
		<tr>
			<td nowrap="1" style="padding:2px; border-right:1px solid; border-bottom:1px solid;">
				{$d.distributor_name}
			</td>
			<td nowrap="1" style="padding:2px; border-right:1px solid; border-bottom:1px solid;" align="right">
				{$d.price}
			</td>
			<td nowrap="1" style="padding:2px; border-right:1px solid; border-bottom:1px solid;" align="right">
				{$d.quantity}
			</td>
			<td nowrap="1" style="padding:2px; border-right:1px solid; border-bottom:1px solid;">
				{$d.quantity_info}
			</td>
		</tr>
	{/foreach}
</table>
