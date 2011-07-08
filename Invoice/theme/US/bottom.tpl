<br/>
<table width="200">
	<tr>
		<td>
			<font size=11><b>{$labels.total_price}</b></font>
		</td>
		<td style="text-align:right;">
			<font size=11><b>{$total}.</b></font>
		</td>
	</tr>
	<tr>
		<td>
			<font size=11><b>{$labels.amount_paid}</b></font>
		</td>
		<td style="text-align:right;">
			<font size=11><b>$ 0.00.</b></font>
		</td>
	</tr>
	<tr>
		<td>
			<font size=11><b>{$labels.amount_due}</b></font>
		</td>
		<td style="text-align:right;">
			<font size=11><b>{$total}.</b></font>
		</td>
	</tr>
</table>
{if $total_word}
	<br/>
		<font size=7>{$labels.in_words} {$total_word}</font>
{/if}
<br/>
<br/>
<br/>
<br/>
<font size=6>
{$labels.legal_notice}
</font>
