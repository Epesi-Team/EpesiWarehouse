<br/>
<br/>
<table>
	<tr>
		<td width="66%">
		</td>
		<td width="15%">
			<font size=11><b>{$labels.total_price}</b></font>
		</td>
		<td width="18%" style="text-align:right;">
			<font size=11><b>{$total}.</b></font>
		</td>
	</tr>
	<tr>
		<td width="66%">
		</td>
		<td width="15%">
			<font size=11><b>{$labels.amount_paid}</b></font>
		</td>
		<td width="18%" style="text-align:right;">
			<font size=11><b>{$paid}.</b></font>
		</td>
	</tr>
	<tr>
		<td width="66%">
		</td>
		<td width="15%">
			<font size=11><b>{$labels.amount_due}</b></font>
		</td>
		<td width="18%" style="text-align:right;">
			<font size=11><b>{$amount_due}.</b></font>
		</td>
	</tr>
</table>
{if $total_word}
	<br/>
		<font size=7>{$labels.in_words} {$total_word_en}</font>
{/if}
<br/>
<br/>
<br/>
<br/>
<font size=6>
{$labels.legal_notice}
</font>
