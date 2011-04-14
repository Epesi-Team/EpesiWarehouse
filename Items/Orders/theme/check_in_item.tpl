{$form_open}

<table class="Premium_Warehouse_Orders__check_in_header" border="0" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td class="icon" style="padding-left: 50px;padding-right:30px;"><img src="{$theme_dir}/{$item_icon}" width="32" height="32" border="0"></td>
			<td class="name">{$item_caption}</td>
			<td class="required_fav_info">
			</td>
		</tr>
	</tbody>
</table>

<!-- SHADOW BEGIN -->
	<div class="layer" style="padding: 9px; width: 98%;">
		<div class="content_shadow">
<!-- -->

<div style="padding: 2px 2px 2px 2px; background-color: #FFFFFF;">
<table id="Premium_Warehouse_Orders__check_in_table" cellpadding="0" cellspacing="0" border="0">
	<tbody>
		<tr>
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="label">{$form_data.brand_new.label}</td>
						<td class="data" id="_brand_new__data">{if $form_data.brand_new.error}{$form_data.brand_new.error}{/if}{$form_data.brand_new.html}</td>
					</tr>
				</table>
			</td>
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="empty" colspan="2">
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id="{$brand_new_section_id}">
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="label">{$form_data.item_type.label}</td>
						<td class="data" id="_item_type__data">{if $form_data.item_type.error}{$form_data.item_type.error}{/if}{$form_data.item_type.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.new_item_name.label}</td>
						<td class="data" id="_new_item_name__data">{if $form_data.new_item_name.error}{$form_data.new_item_name.error}{/if}{$form_data.new_item_name.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.product_code.label}</td>
						<td class="data" id="_product_code__data">{if $form_data.product_code.error}{$form_data.product_code.error}{/if}{$form_data.product_code.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.weight.label}</td>
						<td class="data" id="_weight__data">{if $form_data.weight.error}{$form_data.weight.error}{/if}{$form_data.weight.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.volume.label}</td>
						<td class="data" id="_volume__data">{if $form_data.volume.error}{$form_data.volume.error}{/if}{$form_data.volume.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.manufacturer_part_number.label}</td>
						<td class="data" id="_manufacturer_part_number__data">{if $form_data.manufacturer_part_number.error}{$form_data.manufacturer_part_number.error}{/if}{$form_data.manufacturer_part_number.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.manufacturer.label}</td>
						<td class="data" id="_manufacturer__data">{if $form_data.manufacturer.error}{$form_data.manufacturer.error}{/if}{$form_data.manufacturer.html}</td>
					</tr>
				</table>
			</td>
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="label">{$form_data.upc.label}</td>
						<td class="data" id="_upc__data">{if $form_data.upc.error}{$form_data.upc.error}{/if}{$form_data.upc.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.category.label}</td>
						<td class="data" id="_category__data">{if $form_data.category.error}{$form_data.category.error}{/if}{$form_data.category.html}</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id="{$brand_new_long_section_id}">
			<td colspan="2">
				<table cellpadding="0" cellspacing="0" border="0" class="edit" style="border-top: none;">
					<tr>
						<td class="label long_label">{$form_data.item_description.label}{if $form_data.item_description.required}*{/if}</td>
						<td class="data long_data" id="_{$form_data.item_description.element}__data">{if $form_data.item_description.error}{$form_data.item_description.error}{/if}{$form_data.item_description.html}</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id="{$existing_item_section_id}">
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit" style="border-top: none;">
					<tr>
						<td class="label">{$form_data.item_name.label}{if $form_data.item_name.required}*{/if}</td>
						<td class="data " id="_{$form_data.item_name.element}__data">{if $form_data.item_name.error}{$form_data.item_name.error}{/if}{$form_data.item_name.html}</td>
					</tr>
				</table>
			</td>
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="empty" colspan="2">
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>
</div>

<!-- SHADOW END -->
 		</div>
		<div class="shadow-top">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-middle">
			<div class="left"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-bottom">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
	</div>
<!-- -->


<table class="Premium_Warehouse_Orders__check_in_header" border="0" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td class="icon" style="padding-left: 50px;padding-right:30px;"><img src="{$theme_dir}/{$order_detail_icon}" width="32" height="32" border="0"></td>
			<td class="name">{$order_detail_caption}</td>
			<td class="required_fav_info">
			</td>
		</tr>
	</tbody>
</table>

<!-- SHADOW BEGIN -->
	<div class="layer" style="padding: 9px; width: 98%;">
		<div class="content_shadow">
<!-- -->

<div style="padding: 2px 2px 2px 2px; background-color: #FFFFFF;">
<table id="Premium_Warehouse_Orders__check_in_table" cellpadding="0" cellspacing="0" border="0">
	<tbody>
		<tr>
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="label">{$form_data.quantity.label}</td>
						<td class="data" id="_quantity__data">{if $form_data.quantity.error}{$form_data.quantity.error}{/if}{$form_data.quantity.html}</td>
					</tr>
				</table>
			</td>
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="empty" colspan="2">
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table cellpadding="0" cellspacing="0" border="0" class="edit" style="border-top: none;">
					<tr>
						<td class="label long_label">{$form_data.description.label}{if $form_data.description.required}*{/if}</td>
						<td class="data long_data" id="_{$form_data.description.element}__data">{if $form_data.description.error}{$form_data.description.error}{/if}{$form_data.description.html}</td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>
</div>

<!-- SHADOW END -->
 		</div>
		<div class="shadow-top">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-middle">
			<div class="left"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-bottom">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
	</div>
<!-- -->

<span id="{$serials_section_id}">
<table class="Premium_Warehouse_Orders__check_in_header" border="0" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td class="icon" style="padding-left: 50px;padding-right:30px;"><img src="{$theme_dir}/{$serials_icon}" width="32" height="32" border="0"></td>
			<td class="name">{$serials_caption}</td>
			<td class="required_fav_info">
			</td>
		</tr>
	</tbody>
</table>

<!-- SHADOW BEGIN -->
	<div class="layer" style="padding: 9px; width: 98%;">
		<div class="content_shadow">
<!-- -->

<div style="padding: 2px 2px 2px 2px; background-color: #FFFFFF;">
<table id="Premium_Warehouse_Orders__check_in_table" cellpadding="0" cellspacing="0" border="0">
	<tbody>
		<tr>
			<td class="column" style="width: 100%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="label">{$form_data.serial.label}</td>
						<td class="data" id="_serial__data">{if $form_data.serial.error}{$form_data.serial.error}{/if}{$form_data.serial.html}</td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>
</div>

<!-- SHADOW END -->
 		</div>
		<div class="shadow-top">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-middle">
			<div class="left"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-bottom">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
	</div>
<!-- -->
</span>

{$form_close}
