{$form_open}

<table class="Premium_Warehouse_Wholesale__sync_item" border="0" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td class="icon" style="padding-left: 50px;padding-right:30px;"><img src="{$theme_dir}/{$dist_item_icon}" width="32" height="32" border="0"></td>
			<td class="name">{$dist_item_caption}</td>
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
<table id="Premium_Warehouse_Wholesale__sync_item_table" cellpadding="0" cellspacing="0" border="0">
	<tbody>
		<tr>
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="label">{$form_data.dist_item_name.label}</td>
						<td class="data" id="_dist_item_name__data">{if $form_data.dist_item_name.error}{$form_data.dist_item_name.error}{/if}{$form_data.dist_item_name.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.dist_category.label}</td>
						<td class="data" id="_dist_category__data">{if $form_data.dist_category.error}{$form_data.dist_category.error}{/if}{$form_data.dist_category.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.dist_price.label}</td>
						<td class="data" id="_dist_price__data">{if $form_data.dist_price.error}{$form_data.dist_price.error}{/if}{$form_data.dist_price.html}</td>
					</tr>
				</table>
			</td>
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="label">{$form_data.dist_manufacturer.label}</td>
						<td class="data" id="_dist_manufacturer__data">{if $form_data.dist_manufacturer.error}{$form_data.dist_manufacturer.error}{/if}{$form_data.dist_manufacturer.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.dist_mpn.label}</td>
						<td class="data" id="_dist_mpn__data">{if $form_data.dist_mpn.error}{$form_data.dist_mpn.error}{/if}{$form_data.dist_mpn.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.dist_upc.label}</td>
						<td class="data" id="_dist_upc__data">{if $form_data.dist_upc.error}{$form_data.dist_upc.error}{/if}{$form_data.dist_upc.html}</td>
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

<table class="Premium_Warehouse_Wholesale__sync_item" border="0" cellpadding="0" cellspacing="0">
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
<table id="Premium_Warehouse_Wholesale__sync_item_table" cellpadding="0" cellspacing="0" border="0">
	<tbody>
		<tr>
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="label">{$form_data.create_new_item.label}</td>
						<td class="data" id="_create_new_item__data">{if $form_data.create_new_item.error}{$form_data.create_new_item.error}{/if}{$form_data.create_new_item.html}</td>
					</tr>
				</table>
			</td>
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr id="existing_item_search_candicates_section">
						<td class="label">{$form_data.add_candidates.label}</td>
						<td class="data" id="_add_candidates__data">{if $form_data.add_candidates.error}{$form_data.add_candidates.error}{/if}{$form_data.add_candidates.html}</td>
					</tr>
					<tr id="existing_item_change_candidate_section">
						<td class="label">{$form_data.change_candidate.label}</td>
						<td class="data" id="_change_candidate__data">{if $form_data.change_candidate.error}{$form_data.change_candidate.error}{/if}{$form_data.change_candidate.html}</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id="existing_item_display_section" style="display:none;">
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="label">{$form_data.e_item_name.label}</td>
						<td class="data" id="_e_item_name__data"><span id="_e_item_name__display"></span>{if $form_data.e_item_name.error}{$form_data.e_item_name.error}{/if}{$form_data.e_item_name.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.e_category.label}</td>
						<td class="data" id="_e_category__data"><span id="_e_category__display"></span>{if $form_data.e_category.error}{$form_data.e_category.error}{/if}{$form_data.e_category.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.e_price.label}</td>
						<td class="data" id="_e_price__data"><span id="_e_price__display"></span>{if $form_data.e_price.error}{$form_data.e_price.error}{/if}{$form_data.e_price.html}</td>
					</tr>
				</table>
			</td>
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="label">{$form_data.e_manufacturer.label}</td>
						<td class="data" id="_e_manufacturer__data"><span id="_e_manufacturer__display"></span>{if $form_data.e_manufacturer.error}{$form_data.e_manufacturer.error}{/if}{$form_data.e_manufacturer.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.e_mpn.label}</td>
						<td class="data" id="_e_mpn__data"><span id="_e_mpn__display"></span>{if $form_data.e_mpn.error}{$form_data.e_mpn.error}{/if}{$form_data.e_mpn.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.e_upc.label}</td>
						<td class="data" id="_e_upc__data"><span id="_e_upc__display"></span>{if $form_data.e_upc.error}{$form_data.e_upc.error}{/if}{$form_data.e_upc.html}</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id="create_new_item_section" style="display:none;">
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="label">{$form_data.n_item_name.label}</td>
						<td class="data" id="_n_item_name__data"><span id="_n_item_name__display"></span>{if $form_data.n_item_name.error}{$form_data.n_item_name.error}{/if}{$form_data.n_item_name.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.n_item_type.label}</td>
						<td class="data" id="_n_item_type__data"><span id="_n_item_type__display"></span>{if $form_data.n_item_type.error}{$form_data.n_item_type.error}{/if}{$form_data.n_item_type.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.n_tax_rate.label}</td>
						<td class="data" id="_n_tax_rate__data"><span id="_n_tax_rate__display"></span>{if $form_data.n_tax_rate.error}{$form_data.n_tax_rate.error}{/if}{$form_data.n_tax_rate.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.n_category.label}</td>
						<td class="data" id="_n_category__data"><span id="_n_category__display"></span>{if $form_data.n_category.error}{$form_data.n_category.error}{/if}{$form_data.n_category.html}</td>
					</tr>
				</table>
			</td>
			<td class="column" style="width: 50%;">
				<table cellpadding="0" cellspacing="0" border="0" class="edit">
					<tr>
						<td class="label">{$form_data.n_manufacturer.label}</td>
						<td class="data" id="_n_manufacturer__data">
							<span id="_n_manufacturer__display">
							</span>
							{if $form_data.n_manufacturer.error}
								{$form_data.n_manufacturer.error}
							{/if}
							{$form_data.n_manufacturer.html}
							{$form_data.n_create_manufacturer.html}
						</td>
						<td class="data" id="_n_manufacturer__data" style="width:1px;">
							{$form_data.n_enable_create_manufacturer.html}
						</td>
					</tr>
					<tr>
						<td class="label">{$form_data.n_mpn.label}</td>
						<td class="data" id="_n_mpn__data" colspan="2"><span id="_n_mpn__display"></span>{if $form_data.n_mpn.error}{$form_data.n_mpn.error}{/if}{$form_data.n_mpn.html}</td>
					</tr>
					<tr>
						<td class="label">{$form_data.n_upc.label}</td>
						<td class="data" id="_n_upc__data" colspan="2"><span id="_n_upc__display"></span>{if $form_data.n_upc.error}{$form_data.n_upc.error}{/if}{$form_data.n_upc.html}</td>
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

<span id="existing_item_section">
{$item_suggestions_table}
</span>

{if $ecommerce_on} 	{******************************** eCommerce *************************************}

<table class="Premium_Warehouse_Wholesale__sync_item" border="0" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td class="icon" style="padding-left: 50px;padding-right:30px;"><img src="{$theme_dir}/{$ecommerce_icon}" width="32" height="32" border="0"></td>
			<td class="name">{$ecommerce_caption}</td>
			<td class="required_fav_info">
			</td>
		</tr>
	</tbody>
</table>

{/if} 				{******************************** eCommerce *************************************}

{$form_close}
