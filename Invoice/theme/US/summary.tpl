<table>
	<tr>
		<td width="357px">
		</td>
		<td width="167px">
			<table border="1">
				{foreach item=v key=k from=$gross_total}
				{foreach item=vv key=vat from=$v}
					<tr>
						<td width="45px" align="right">
							<font size="7">
								{$net_total.$k.$vat}&nbsp;
							</font>
						</td>
						<td width="32px" align="center">
							<font size="7">
								{$vat}
							</font>
						</td>
						<td width="45px" align="right">
							<font size="7">
								{$tax_total.$k.$vat}&nbsp;
							</font>
						</td>
						<td width="45px" align="right">
							<font size="7">
								{$gross_total.$k.$vat}&nbsp;
							</font>
						</td>
					</tr>
				{/foreach}
				{/foreach}
			</table>
		</td>
	</tr>
</table>