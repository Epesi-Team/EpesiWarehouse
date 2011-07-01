<br/>
<font size=11><b><u>{$labels.amount_due} {$total}.</u></b></font>
{if $total_word}
<br/>
	<font size=7>{$labels.in_words} {$total_word}</font>
{/if}
<br/>
<br/>
<br/>
<br/>
<table>
	<tr>
		<td align="center">
		{if $order.receipt}
			&nbsp;
		{else}
			<font size=8>
				___________________________<br/>
				{$labels.receiver_sig}
			</font>
		{/if}
		</td>
		<td align="center">
			<font size=8>
				___________________________<br/>
				{$labels.employee_sig}
			</font>
		</td>
	</tr>
</table>
<br/>
<br/>
<font size=6>
{$labels.legal_notice}
</font>
