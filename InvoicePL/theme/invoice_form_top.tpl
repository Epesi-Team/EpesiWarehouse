<table>
	<tr>
		<td>
			<font size=8>
				{$company.company_name}<br/>
				{$warehouse.address_1}<br/>
				{$warehouse.postal_code} {$warehouse.city}<br/>
				{if $warehouse.phone}tel. {$warehouse.phone}<br/>{/if}
				{if $warehouse.fax}fax. {$warehouse.fax}<br/>{/if}
				{$company.web_address}
			</font>
		</td>
		<td align="right">
			{$warehouse.city}, {$date}<br/>
			Data sprzedazy: {$order.transaction_date}
		</td>
	</tr>
</table>
<div width="100%" align="center">
	<font size=12><b>Faktura VAT nr. {$order.invoice_id}</b></font><br>
	ORYGINAŁ | KOPIA | DUPLIKAT
</div>
<table>
	<tr>
		<td align="right" width="80px">
			<font size=10><b>
				Sprzedawca:
			</b></font>
		</td>
		<td width="10px">
		</td>
		<td align="left">
			{$company.company_name}
		</td>
	</tr>
	<tr>
		<td align="right" width="80px">
			Adres:
		</td>
		<td width="10px">
		</td>
		<td align="left">
			{$warehouse.postal_code} {$warehouse.city}, {$warehouse.address_1}
		</td>
	</tr>
	<tr>
		<td align="right" width="80px">
			Numer indentyf.:
		</td>
		<td width="10px">
		</td>
		<td align="left">
			<b>{$warehouse.tax_id}</b>
		</td>
	</tr>
</table>

<table>
	<tr>
		<td align="right" width="80px">
			<font size=10><b>
				Nabywca:
			</b></font>
		</td>
		<td width="10px">
		</td>
		<td align="left">
			{$order.company_name}
		</td>
	</tr>
	<tr>
		<td align="right" width="80px">
			Adres:
		</td>
		<td width="10px">
		</td>
		<td align="left">
			{$order.postal_code} {$order.city}, {$order.address_1}
		</td>
	</tr>
	<tr>
		<td align="right" width="80px">
			Numer indentyf.:
		</td>
		<td width="10px">
		</td>
		<td align="left">
			<b>{if isset($order.ssn)}{$order.tax_id}{/if}</b>
		</td>
	</tr>
</table>
<br>

<table>
	<tr>
		<td align="right" width="80px">
			<b>Sposób zapłaty:</b>
		</td>
		<td width="10px">
		</td>
		<td width="80px" align="left">
			{$order.payment_type_label}
		</td>
		<td width="80px" align="right">
			termin zapłaty:
		</td>
		<td width="5px">
		</td>
		<td align="left">
			{$order.terms_label}
		</td>
	</tr>
	<tr>
		<td align="right" width="80px">
			BANK:
		</td>
		<td width="10px">
		</td>
		<td align="left">
			{$warehouse.bank_account}
		</td>
	</tr>
</table>

<br>
<center>
	<table border="1">
		<tr>
			<td width="20px">
				<font size="7"><b>
					L.p.
				</b></font>
			</td>
			<td width="192px">
				<font size="7"><b>
					Nazwa towaru/usługi
				</b></font>
			</td>
			<td width="45px">
				<font size="7"><b>
					SWW
				</b></font>
			</td>
			<td width="35px">
				<font size="7"><b>
					Ilość
				</b></font>
			</td>
			<td width="20px">
				<font size="7"><b>
					jm
				</b></font>
			</td>
			<td width="45px">
				<font size="7"><b>
					&nbsp;&nbsp;Cena&nbsp;&nbsp; brutto
				</b></font>
			</td>
			<td width="32px">
				<font size="7"><b>
					Stawka VAT&nbsp;%
				</b></font>
			</td>
			<td width="45px">
				<font size="7"><b>
					Wartość brutto
				</b></font>
			</td>
			<td width="45px">
				<font size="7"><b>
					Wartość netto
				</b></font>
			</td>
			<td width="45px">
				<font size="7"><b>
					Wartość &nbsp;VAT&nbsp;
				</b></font>
			</td>
		</tr>
	</table>
</center>
