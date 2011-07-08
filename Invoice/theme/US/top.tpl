<table>
	<tr>
		<td>
			<font size=8>
				{if isset($warehouse.invoice_display_name)}{$warehouse.invoice_display_name}{else}{$company.company_name}{/if}<br/>
				{$warehouse.address_1}<br/>
				{$warehouse.postal_code} {$warehouse.city}<br/>
				{if $warehouse.phone}{$labels.tel} {$warehouse.phone}<br/>{/if}
				{if $warehouse.fax}{$labels.fax} {$warehouse.fax}<br/>{/if}
				{$company.web_address}
			</font>
		</td>
		<td align="right">
			{$warehouse.city}, {$date}<br/>
		</td>
	</tr>
</table>
<div width="100%" align="center">
	{if $order.status==1 || $order.status==""}
		<font size=12><b>{$labels.po} {$order.proforma_id}</b></font><br>
	{else}
		{if $order.receipt}
			<font size=12><b>{$labels.receipt} {$order.invoice_id}</b></font><br>
		{else}
			{if isset($order.invoice_id) && $order.invoice_id}
				<font size=12><b>{$labels.invoice} {$order.invoice_id}</b></font><br>
			{else}
				{if isset($order.po_id)}<font size=11><b>{$labels.order} {$order.po_id}</b></font><br>{/if}
			{/if}
		{/if}
	{/if}
</div>
<br>
{if !$order.receipt}
<table>
	<tr>
		<td align="right" width="90px">
			<font size=10><b>
				{$labels.buyer}
			</b></font>
		</td>
		<td width="10px">
		</td>
		<td align="left" width="400">
			{$order.company_name}
		</td>
	</tr>
	<tr>
		<td align="right" width="90px">
			{$labels.buyer_address}
		</td>
		<td width="10px">
		</td>
		<td align="left" width="400">
			{$order.postal_code} {$order.city}, {$order.address_1}
		</td>
	</tr>
</table>
<br>
{/if}
<table>
	<tr>
		<td align="right" width="90px">
			<b>{$labels.payment_method}</b>
		</td>
		<td width="10px">
		</td>
		<td width="90px" align="left">
			{$order.payment_type_label}
		</td>
		<td width="90px" align="right">
			{$labels.due_date}
		</td>
		<td width="5px">
		</td>
		<td align="left" width="160px">
			{$order.terms_label}
		</td>
	</tr>
	<tr>
		<td align="right" width="90px">
			{$labels.bank}
		</td>
		<td width="10px">
		</td>
		<td align="left" colspan="4">
			{$warehouse.bank_account}
		</td>
	</tr>
</table>

<br>
<center>
	<table border="1">
		<tr>
			<td width="40px">
				<font size="7"><b>
					{$labels.no}
				</b></font>
			</td>
			<td width="172px">
				<font size="7"><b>
					{$labels.item_name}
				</b></font>
			</td>
			<td width="45px">
				<font size="7"><b>
					{$labels.sku}
				</b></font>
			</td>
			<td width="35px">
				<font size="7"><b>
					{$labels.quantity}
				</b></font>
			</td>
			<td width="20px">
				<font size="7"><b>
					{$labels.units}
				</b></font>
			</td>
			<td width="45px">
				<font size="7"><b>
					{$labels.net_price}
				</b></font>
			</td>
			<td width="32px">
				<font size="7"><b>
					{$labels.tax_rate}
				</b></font>
			</td>
			<td width="45px">
				<font size="7"><b>
					{$labels.gorss_value}
				</b></font>
			</td>
			<td width="45px">
				<font size="7"><b>
					{$labels.net_value}
				</b></font>
			</td>
			<td width="45px">
				<font size="7"><b>
					{$labels.tax_value}
				</b></font>
			</td>
		</tr>
	</table>
</center>
