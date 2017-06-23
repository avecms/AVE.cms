	<div id="conditions">
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic" id="conditions">
		<col width="30">
		<col width="30">
		<col width="300">
		<col width="300">
		<col width="80">
		<col width="">
		<col width="30">
		<col>
		<thead>
			<tr>
				<td width="1"><div align="center"><span class="icon_sprite ico_move"></span></div></td>
				<td width="1"><div align="center"><span class="icon_sprite ico_ok"></span></div></td>
				<td>{#REQUEST_FROM_FILED#}</td>
				<td>{#REQUEST_OPERATOR#}</td>
				<td>{#REQUEST_CONDITION_JOIN#}</td>
				<td>{#REQUEST_VALUE#}</td>
				<td width="1"><div align="center"><span class="icon_sprite ico_delete"></span></div></td>
			</tr>
		</thead>

	{if $conditions}
		<tbody>
	{foreach name=cond from=$conditions item=condition}
		<tr class="cond_tr" data-id="cond_{$condition->Id}">
			<td><span class="icon_sprite ico_move" style="cursor: move;"></span></td>
			<td><input name="conditions[{$condition->Id}][condition_status]" type="checkbox" id="condition_status{$condition->Id}" value="1" {if $condition->condition_status ==1}checked{/if} class="toprightDir float" /></td>

			<td align="center">
				{foreach from=$fields_list item=field_group}

					{foreach  from=$field_group.fields item=field}
						{if $condition->condition_field_id == $field.Id}
						<div id="req_cond_{$condition->Id}">
							<a href="javascript:void(0);" class="link change_field" data-id="{$field.Id|escape}" data-cond="{$condition->Id}">{$field.rubric_field_title|escape} (ID: {$field.Id|escape})</a>
							<input type="hidden" name="conditions[{$condition->Id}][condition_field_id]" value="{$field.Id|escape}" />
						</div>
						{/if}
					{/foreach}

				{/foreach}
			</td>

			<td>
				<select style="max-height: 100px;" name="conditions[{$condition->Id}][condition_compare]" id="operator_{$condition->Id}">
					<option value="==" {if $condition->condition_compare=='=='}selected{/if}>{#REQUEST_COND_SELF#}</option>
					<option value="!=" {if $condition->condition_compare=='!='}selected{/if}>{#REQUEST_COND_NOSELF#}</option>
					<option value="%%" {if $condition->condition_compare=='%%'}selected{/if}>{#REQUEST_COND_USE#}</option>
					<option value="--" {if $condition->condition_compare=='--'}selected{/if}>{#REQUEST_COND_NOTUSE#}</option>
					<option value="%" {if $condition->condition_compare=='%'}selected{/if}>{#REQUEST_COND_START#}</option>
					<option value="<=" {if $condition->condition_compare=='<='}selected{/if}>{#REQUEST_SMALL1#}</option>
					<option value=">=" {if $condition->condition_compare=='>='}selected{/if}>{#REQUEST_BIG1#}</option>
					<option value="<" {if $condition->condition_compare=='<'}selected{/if}>{#REQUEST_SMALL2#}</option>
					<option value=">" {if $condition->condition_compare=='>'}selected{/if}>{#REQUEST_BIG2#}</option>

					<option value="N==" {if $condition->condition_compare=='N=='}selected{/if}>{#REQUEST_N_COND_SELF#}</option>
					<option value="N<=" {if $condition->condition_compare=='N<='}selected{/if}>{#REQUEST_N_SMALL1#}</option>
					<option value="N>=" {if $condition->condition_compare=='N>='}selected{/if}>{#REQUEST_N_BIG1#}</option>
					<option value="N<" {if $condition->condition_compare=='N<'}selected{/if}>{#REQUEST_N_SMALL2#}</option>
					<option value="N>" {if $condition->condition_compare=='N>'}selected{/if}>{#REQUEST_N_BIG2#}</option>

					<option value="SEGMENT" {if $condition->condition_compare=='SEGMENT'}selected{/if}>{#REQUEST_SEGMENT#}</option>
					<option value="INTERVAL" {if $condition->condition_compare=='INTERVAL'}selected{/if}>{#REQUEST_INTERVAL#}</option>

					<option value="IN=" {if $condition->condition_compare=='IN='}selected{/if}>{#REQUEST_IN#}</option>
					<option value="NOTIN=" {if $condition->condition_compare=='NOTIN='}selected{/if}>{#REQUEST_NOTIN#}</option>

					<option value="ANY" {if $condition->condition_compare=='ANY'}selected{/if}>{#REQUEST_ANY_NUM#}</option>
					<option value="FRE" {if $condition->condition_compare=='FRE'}selected{/if}>{#REQUEST_FREE#}</option>
				</select>
			</td>
			<td>
				<select style="width:60px" name="conditions[{$condition->Id}][condition_join]">
					<option value="AND" {if $condition->condition_join=='AND'}selected{/if}>{#REQUEST_CONR_AND#}</option>
					<option value="OR" {if $condition->condition_join=='OR'}selected{/if}>{#REQUEST_CONR_OR#}</option>
				</select>
			</td>

			<td><div class="pr12"><input name="conditions[{$condition->Id}][condition_value]" type="text" id="Wert_{$condition->Id}" value="{$condition->condition_value|escape}" class="mousetrap" /> </div></td>
			<td><input title="{#REQUEST_MARK_DELETE#}" name="del[{$condition->Id}]" type="checkbox" id="del_{$condition->Id}" value="1" class="topleftDir float" /></td>
		</tr>

	{/foreach}
	</tbody>
	{else}
		<tr class="noborder">
			<td colspan="7">
				<ul class="messages">
					<li class="highlight yellow">{#REQUEST_COND_MESSAGE#}</li>
				</ul>
			</td>
		</tr>
	{/if}

	</table>

	{if $conditions}
	<div class="rowElem"<div class="rowElem"{if !$smarty.request.pop} id="saveBtn"{/if}>
		<div{if !$smarty.request.pop} class="saveBtn"{/if}>
			<input type="submit" value="{#REQUEST_BUTTON_SAVE#}" class="basicBtn" />
			{#REQUEST_OR#}
			<input type="submit" value="{#REQUEST_BUTTON_SAVE_NEXT#}" class="button blackBtn SaveEditCond" />&nbsp;
			{if $smarty.request.pop}
			<input onclick="javascript:void(0);" type="button" class="redBtn Close" value="{#REQUEST_BUTTON_CLOSE#}" />
			{/if}
		</div>
	</div>
	{/if}
	</div>

	{if $conditions}
		<script language="javascript">
			$(document).ready(function(){ldelim}
				AveAdmin.ajax();

				$('#conditions').tableSortable({ldelim}
					items: '.cond_tr',
					url: 'index.php?do=request&action=conditions&sub=sort&rubric_id={$smarty.request.rubric_id|escape}&Id={$smarty.request.Id|escape}&cp={$sess}',
					success: true
				{rdelim});

				$.alerts._overlay('hide');

			{rdelim});
		</script>
	{/if}
