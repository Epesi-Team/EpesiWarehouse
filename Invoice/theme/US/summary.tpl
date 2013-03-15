<table>
	{foreach item=v key=k from=$gross_total}
	{foreach item=vv key=vat from=$v}
		<tr>
			<td width="70%">
			</td>
			<td width="8%" align="right" border="1">
				<font size="7">
					{$net_total.$k.$vat}&nbsp; 
				</font>
			</td>
			<td width="6%" align="center" border="1">
				<font size="7">
					{$vat}
				</font>
			</td>
			<td width="8%" align="right" border="1">
				<font size="7">
					{$tax_total.$k.$vat}&nbsp; 
				</font>
			</td>
			<td width="8%" align="right" border="1">
				<font size="7">
					{$gross_total.$k.$vat}&nbsp; 
				</font>
			</td>
		</tr>
	{/foreach}
	{/foreach}
</table>