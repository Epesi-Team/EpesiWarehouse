<table>
	<tr>
		{if isset($logo)}
			<td width="10%">
				{$logo}
			</td>
		{/if}
		<td width="1%">
		</td>
		<td width="44%">
			<font size=8>
				{if isset($warehouse.invoice_display_name) && $warehouse.invoice_display_name}{$warehouse.invoice_display_name}{else}{$company.company_name}{/if}<br/>
				{$warehouse.address_1}<br/>
				{$warehouse.postal_code} {$warehouse.city}<br/>
				{if $warehouse.phone}{$labels.tel} {$warehouse.phone}<br/>{/if}
				{if $warehouse.fax}{$labels.fax} {$warehouse.fax}<br/>{/if}
				{$company.web_address}
			</font>
		</td>
		{if !isset($logo)}
			<td width="10%">
				&nbsp;
			</td>
		{/if}
		<td align="right" width="45%">
			{if $warehouse.city}{$warehouse.city}, {/if}{$date}<br/>
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
			{if $order.first_name}
				{$order.last_name} {$order.first_name}
				{if $order.company_name}
					, 
				{/if}
			{/if}
			{if $order.company_name}
				{$order.company_name}
			{/if}
		</td>
	</tr>
	<tr>
		<td align="right" width="90px">
			{$labels.buyer_address}
		</td>
		<td width="10px">
		</td>
		<td align="left" width="400">
			{$order.address_1}
		</td>
	</tr>
	{if $order.address_2}
	<tr>
		<td align="right" width="90px">
		</td>
		<td width="10px">
		</td>
		<td align="left" width="400">
			{$order.address_2}
		</td>
	</tr>
	{/if}
	<tr>
		<td align="right" width="90px">
		</td>
		<td width="10px">
		</td>
		<td align="left" width="400">
			{$order.postal_code} {$order.city}
		</td>
	</tr>
	{if $order.shipping_address_1}
		<tr>
			<td>
			</td>
		</tr>
		<tr>
			<td align="right" width="90px">
				<font size=10><b>
					{$labels.shipping_to}
				</b></font>
			</td>
			<td width="10px">
			</td>
			<td align="left" width="400">
				{if $order.shipping_first_name}
					{$order.shipping_last_name} {$order.shipping_first_name}
					{if $order.shipping_company_name}
						, 
					{/if}
				{/if}
				{if $order.shipping_company_name}
					{$order.shipping_company_name}
				{/if}
			</td>
		</tr>
		<tr>
			<td align="right" width="90px">
				{$labels.shipping_address}
			</td>
			<td width="10px">
			</td>
			<td align="left" width="400">
				{$order.shipping_address_1}
			</td>
		</tr>
		{if $order.shipping_address_2}
			<tr>
				<td align="right" width="90px">
				</td>
				<td width="10px">
				</td>
				<td align="left" width="400">
					{$order.shipping_address_2}
				</td>
		</tr>
		{/if}
		<tr>
			<td align="right" width="90px">
			</td>
			<td width="10px">
			</td>
			<td align="left" width="400">
				{$order.shipping_postal_code} {$order.shipping_city}
			</td>
		</tr>
	{/if}
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
		{if $order.terms_label}
            {assign var="payment_colspan" value="1"}
			<td width="90px" align="right">
				{$labels.due_date}
			</td>
			<td width="5px">
			</td>
			<td align="left" width="160px">
				{$order.terms_label}
			</td>
		{/if}
	</tr>
	<tr>
		<td align="right" width="90px">
			{$labels.cc_info}
		</td>
		{foreach from=$payments item=payment}
			<td width="10px">
			</td>
			<td align="left"{if $payment_colspan} colspan="4"{/if}>
				{$payment.card_number}, {$payment.expiration_date}, {$payment.cvc_cvv}, {$payment.amount_label}
			</td>
		</tr>
		<tr>
			<td width="90px">
			</td>
		{/foreach}
	</tr>
</table>
<br>
<table>
	<tr>
		<td align="right" width="90px">
			<b>{$labels.shipment_type}</b>
		</td>
		<td width="10px">
		</td>
		<td width="250px" align="left">
			{$order.shipment_type}
		</td>
	</tr>
</table>

{if isset($order.comments) && !empty($order.comments)}
<br>
<table>
	{assign var=label value=true}
	{foreach item=com from=$order.comments}
	<tr>
		<td align="right" width="90px">
			{if $label}<b>{$labels.comments}</b>
{/if}
		</td>
		<td width="10px">
		</td>
		<td width="420px" align="left">
			{$com}
		</td>
	</tr>
	{assign var=label value=false}
	{/foreach}
</table>
{/if}

<br>
<center>
	<table border="1">
		<tr>
			<td width="6%" style="text-align:center;">
				<font size="7"><b>
					{$labels.no}
				</b></font>
			</td>
			<td width="22%" style="text-align:center;">
				<font size="7"><b>
					{$labels.item_name}
				</b></font>
			</td>
			<td width="8%" style="text-align:center;">
				<font size="7"><b>
					{$labels.sku}
				</b></font>
			</td>
			<td width="6%" style="text-align:center;">
				<font size="7"><b>
					{$labels.quantity}
				</b></font>
			</td>
			<td width="4%" style="text-align:center;">
				<font size="7"><b>
					{$labels.units}
				</b></font>
			</td>
            <td width="8%" style="text-align:center;">
                <font size="7"><b>
                        {$labels.unit_price}
                    </b></font>
            </td>
            <td width="8%" style="text-align:center;">
                <font size="7"><b>
                        {$labels.markup_discount_rate}
                    </b></font>
            </td>
			<td width="8%" style="text-align:center;">
				<font size="7"><b>
					{$labels.net_price}
				</b></font>
			</td>
			<td width="8%" style="text-align:center;">
				<font size="7"><b>
					{$labels.net_value}
				</b></font>
			</td>
			<td width="6%" style="text-align:center;">
				<font size="7"><b>
					{$labels.tax_rate}
				</b></font>
			</td>
			<td width="8%" style="text-align:center;">
				<font size="7"><b>
					{$labels.tax_value}
				</b></font>
			</td>
			<td width="8%" style="text-align:center;">
				<font size="7"><b>
					{$labels.gross_value}
				</b></font>
			</td>
		</tr>
	</table>
</center>
