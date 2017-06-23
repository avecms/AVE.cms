<script language="Javascript" type="text/javascript">

var sess = '{$sess}';

</script>

<div class="title {if $smarty.request.pop}first{/if}">
	<h5>{#REQUEST_CONDITIONS#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#REQUEST_CONDITION_TIP#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=request&amp;cp={$sess}">{#REQUEST_ALL#}</a></li>
	        <li>{#REQUEST_CONDITIONS#}</li>
			<li><strong class="code">{$request_title|escape|stripslashes}</strong></li>
		</ul>
	</div>
</div>

<form class="mainForm" action="index.php?do=request&action=conditions&sub=new&rubric_id={$smarty.request.rubric_id|escape}&Id={$smarty.request.Id|escape}&cp={$sess}" method="post" id="NewCond">

<div class="widget first">

	<div class="head" id="opened">
		<h5 class="iFrames">{#REQUEST_NEW_CONDITION#}</h5>
	</div>

	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<col width="150">
		<col width="300">
		<col width="80">
		<col width="">
		<thead>
		<tr>
			<td>{#REQUEST_FROM_FILED#}</td>
			<td>{#REQUEST_OPERATOR#}</td>
			<td>{#REQUEST_CONDITION_JOIN#}</td>
			<td>{#REQUEST_VALUE#}</td>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td>
				<select name="field_new" id="field_new" style="width:300px; max-height:80px;">

					<option>&nbsp;</option>
						{foreach from=$fields_list item=field_group}

							{if $groups_count > 1}
							<optgroup label="{if $field_group.group_title}{$field_group.group_title}{else}{#REQUEST_FIELD_G_UNKNOW#}{/if}">
							{/if}

							{foreach  from=$field_group.fields item=field}
								<option value="{$field.Id|escape}">{$field.rubric_field_title|escape} (ID: {$field.Id|escape})</option>
							{/foreach}

							{if $groups_count > 1}
							</optgroup>
							{/if}

						{/foreach}
				</select>
			</td>

			<td>
				<select style="max-height: 80px;" name="new_operator" id="new_operator">
					<option value="==" selected>{#REQUEST_COND_SELF#}</option>
					<option value="!=">{#REQUEST_COND_NOSELF#}</option>
					<option value="%%">{#REQUEST_COND_USE#}</option>
					<option value="--">{#REQUEST_COND_NOTUSE#}</option>
					<option value="%">{#REQUEST_COND_START#}</option>
					<option value="<=">{#REQUEST_SMALL1#}</option>
					<option value=">=">{#REQUEST_BIG1#}</option>
					<option value="<">{#REQUEST_SMALL2#}</option>
					<option value=">">{#REQUEST_BIG2#}</option>

					<option value="N==">{#REQUEST_N_COND_SELF#}</option>
					<option value="N<=">{#REQUEST_N_SMALL1#}</option>
					<option value="N>=">{#REQUEST_N_BIG1#}</option>
					<option value="N<">{#REQUEST_N_SMALL2#}</option>
					<option value="N>">{#REQUEST_N_BIG2#}</option>

					<option value="SEGMENT">{#REQUEST_SEGMENT#}</option>
					<option value="INTERVAL">{#REQUEST_INTERVAL#}</option>

					<option value="IN=">{#REQUEST_IN#}</option>
					<option value="NOTIN=">{#REQUEST_NOTIN#}</option>

					<option value="ANY">{#REQUEST_ANY_NUM#}</option>
					<option value="FRE">{#REQUEST_FREE#}</option>
				</select>
			</td>

			<td style="width:60px; max-height: 100px;">
				<select style="width:60px" name="oper_new" id="oper_new">
					<option value="AND" {if $condition->condition_join=='AND'}selected{/if}>{#REQUEST_CONR_AND#}</option>
					<option value="OR" {if $condition->condition_join=='OR'}selected{/if}>{#REQUEST_CONR_OR#}</option>
				</select>
			</td>
			<td>
				<div class="pr12"><input name="new_value" type="text" id="new_value" value="" /></div>
			</td>
		</tr>

		<tr>
			<td colspan="4">
				<input type="submit" value="{#REQUEST_CONDITION_ADD#}" class="basicBtn AddNewCond" />
			</td>
		</tr>
		</tbody>
	</table>
	<div class="fix"></div>
</div>
</form>

<form class="mainForm" action="index.php?do=request&action=conditions&sub=save&rubric_id={$smarty.request.rubric_id|escape}&Id={$smarty.request.Id|escape}&cp={$sess}" method="post" id="CondList">
<div class="widget first">
	<div class="head"><h5 class="iFrames">{#REQUEST_CONDITION#}</h5>
	{if !$smarty.request.pop}
	<div class="num">
		<a class="basicNum" href="index.php?do=request&action=edit&Id={$smarty.request.Id|escape}&rubric_id={$smarty.request.rubric_id|escape}&cp={$sess}">{#REQUEST_EDIT#}</a>
	</div>
	{/if}
</div>

<div id="conditions">
<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<col width="30">
		<col width="30">
		<col width="300">
		<col width="300">
		<col width="80">
		<col width="auto">
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
							<a href="javascript:void(0);"  class="link change_field" data-id="{$field.Id|escape}" data-cond="{$condition->Id}">{$field.rubric_field_title|escape} (ID: {$field.Id|escape})</a>
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
	<div class="rowElem"{if !$smarty.request.pop} id="saveBtn"{/if}>
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

	<div class="fix"></div>
</div>

</form>

<script type="text/javascript" language="JavaScript">
$(document).ready(function(){ldelim}

{if check_permission('request_edit')}

	{if $smarty.request.onlycontent}
		AveAdmin.ajax();
	{/if}

	// сортировка
	$('#conditions').tableSortable({ldelim}
		items: '.cond_tr',
		url: 'index.php?do=request&action=conditions&sub=sort&rubric_id={$smarty.request.rubric_id|escape}&Id={$smarty.request.Id|escape}&cp={$sess}',
		success: true
	{rdelim});

	Mousetrap.bind(['ctrl+s', 'command+s'], function(event) {ldelim}
		event.preventDefault();
		$("#CondList").ajaxSubmit({ldelim}
			url: 'index.php?do=request&action=conditions&sub=save&rubric_id={$smarty.request.rubric_id|escape}&Id={$smarty.request.Id|escape}&cp={$sess}&ajax=1{if $smarty.request.pop}&pop=1{/if}',
			dataType: 'json',
			beforeSubmit: function(){ldelim}
				$.alerts._overlay('show');
			{rdelim},
			success: function(data){ldelim}
				$.jGrowl(data['message'], {ldelim}
					header: data['header'],
					theme: data['theme']
				{rdelim});
				ajaxConditions();
			{rdelim}
		{rdelim});
	return false;
	{rdelim});

	$(".AddNewCond").on('click', function(event){ldelim}
		event.preventDefault();
		$("#NewCond").ajaxSubmit({ldelim}
			url: 'index.php?do=request&action=conditions&sub=new&rubric_id={$smarty.request.rubric_id|escape}&Id={$smarty.request.Id|escape}&cp={$sess}&ajax=1{if $smarty.request.pop}&pop=1{/if}',
			dataType: 'json',
			beforeSubmit: function(){ldelim}
				$.alerts._overlay('show');
			{rdelim},
			success: function(data){ldelim}
				if (data['theme'] == 'accept'){ldelim}
					resetForms();
					ajaxConditions();
				{rdelim}
				else
				{ldelim}
					$.alerts._overlay('hide');
				{rdelim}
				$.jGrowl(data['message'], {ldelim}
					header: data['header'],
					theme: data['theme']
				{rdelim});
			{rdelim}
		{rdelim});
	return false;
	{rdelim});

	function ajaxConditions(){ldelim}
		$.ajax({ldelim}
			url: 'index.php?do=request&action=conditions&rubric_id={$smarty.request.rubric_id|escape}&Id={$smarty.request.Id|escape}&cp={$sess}&ajax=1&onlycontent=1{if $smarty.request.pop}&pop=1{/if}',
			type: 'POST',
			beforeSend: function () {ldelim}
			{rdelim},
			success: function (data) {ldelim}
				$("#conditions").before(data).remove();
				SaveEditCond();
			{rdelim}
		{rdelim});
	{rdelim}

	function SaveEditCond(){ldelim}
		$(".SaveEditCond").on('click', function(event){ldelim}
			event.preventDefault();
			$("#CondList").ajaxSubmit({ldelim}
				url: 'index.php?do=request&action=conditions&sub=save&rubric_id={$smarty.request.rubric_id|escape}&Id={$smarty.request.Id|escape}&cp={$sess}&ajax=1{if $smarty.request.pop}&pop=1{/if}',
				dataType: 'json',
				beforeSubmit: function(){ldelim}
					$.alerts._overlay('show');
				{rdelim},
				success: function(data){ldelim}
					$.jGrowl(data['message'], {ldelim}
						header: data['header'],
						theme: data['theme']
					{rdelim});
					ajaxConditions();
				{rdelim}
			{rdelim});
		return false;
		{rdelim});
	{rdelim};

	$(document).on('click', '.Close', function(event){ldelim}
		event.preventDefault();
		$('#ajax-dialog-conditions-{$smarty.request.Id|escape}').dialog('destroy').remove();
		return false;
	{rdelim});

	function resetForms(){ldelim}
		$('#NewCond select').prop('selectedIndex',0);
		$('#NewCond select').trigger('refresh');
		$('#NewCond input[type=text]').val('');
	{rdelim}

	$(document).on('click', '.change_field', function(event)
	{ldelim}
		event.preventDefault();

		var field_id = $(this).attr('data-id');
		var cond_id = $(this).attr('data-cond');

		$.ajax({ldelim}
			url: 'index.php?do=request&action=change&sub=&cp=' + sess + '&onlycontent=1',
			data: {ldelim}
				req_id: {$smarty.request.Id},
				cond_id: cond_id,
				field_id: field_id,
				rubric_id: {$smarty.request.rubric_id}
			{rdelim},
			type: 'POST',
			beforeSend: function(){ldelim}
				$.alerts._overlay('show');
			{rdelim},
			success: function (data)
			{ldelim}
				$("#req_cond_" + cond_id).before(data).remove();
				$.alerts._overlay('hide');
			{rdelim}
		{rdelim});

		return false;
	{rdelim});


	$(document).on('click', '.SaveChange', function(event)
	{ldelim}
		event.preventDefault();

		var cond_id = $(this).attr('data-id');
		var data = $('#form_cond_' + cond_id).val();

		$.ajax({ldelim}
			url: 'index.php?do=request&action=change&sub=save&cp=' + sess + '&onlycontent=1',
			data: {ldelim}
				req_id: {$smarty.request.Id},
				cond_id: cond_id,
				field_id: data,
				rubric_id: {$smarty.request.rubric_id}
			{rdelim},
			type: 'POST',
			beforeSend: function(){ldelim}
				$.alerts._overlay('show');
			{rdelim},
			success: function (data)
			{ldelim}
				$("#req_cond_" + cond_id).before(data).remove();
				$.alerts._overlay('hide');
			{rdelim}
		{rdelim});

		return false;
	{rdelim});


	SaveEditCond();

	{/if}

{rdelim});
</script>
