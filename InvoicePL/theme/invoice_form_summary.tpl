<table>
	<tr>
		<td width="357px">
		</td>
		<td width="167px">
			<table border="1">
				{foreach item=v key=k from=$gross_total}
					<tr>
						<td width="32px" align="center">
							<font size="7">
								X
							</font>
						</td>
						<td width="45px" align="right">
							<font size="7">
								{$gross_total.$k}&nbsp;
							</font>
						</td>
						<td width="45px" align="right">
							<font size="7">
								{$net_total.$k}&nbsp;
							</font>
						</td>
						<td width="45px" align="right">
							<font size="7">
								{$tax_total.$k}&nbsp;
							</font>
						</td>
					</tr>
				{/foreach}
			</table>
		</td>
	</tr>
</table>