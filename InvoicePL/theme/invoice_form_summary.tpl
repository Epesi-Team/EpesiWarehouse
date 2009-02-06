<table>
	<tr>
		<td width="357px">
		</td>
		<td width="167px">
			<table border="1">
				{foreach item=v key=k from=$gross_total}
					<tr>
						<td width="32px" align="center">
							<font size="6">
								X
							</font>
						</td>
						<td width="45px" align="right">
							<font size="6">
								{$gross_total.$k}&nbsp;
							</font>
						</td>
						<td width="45px" align="right">
							<font size="6">
								{$net_total.$k}&nbsp;
							</font>
						</td>
						<td width="45px" align="right">
							<font size="6">
								{$tax_total.$k}&nbsp;
							</font>
						</td>
					</tr>
				{/foreach}
			</table>
		</td>
	</tr>
</table>