<script language="Javascript" type="text/javascript">
var sess = '{$sess}';
</script>

<div class="title">
	<h5>{#RUBRIK_EDIT_CODE_T#}</h5>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB">
				<a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a>
			</li>
			<li>
				<a href="index.php?do=rubs&cp={$sess}">{#RUBRIK_SUB_TITLE#}</a>
			</li>
			<li>{#RUBRIK_CODE#}</li>
			<li><strong class="code">{$rubric_title}</strong></li>
		</ul>
	</div>
</div>

{if check_permission('rubric_edit') && check_permission('rubric_code')}
<form id="code" action="{$formaction}" method="post" class="mainForm">
	<div class="widget first">
		<div class="head closed active">
			<h5>{#RUBRIK_START_CODE#}</h5>
		</div>
	<div style="display: block">
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<col width="100%">
			<thead>
			<tr>
				<td>{#RUBRIK_START_CODE#}</td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>
					<div class="pr12">
						<textarea name="rubric_start_code" type="text" id="rubric_start_code" value="" style="height:300px;" />{$code->rubric_start_code}</textarea>
					</div>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
<div class="widget first">
	<div class="head">
		<h5>{#RUBRIK_CODE#}</h5>
		<div class="num">
			<a class="basicNum" href="index.php?do=rubs&action=edit&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIK_EDIT#}</a>
			&nbsp;
			<a class="basicNum" href="index.php?do=rubs&action=template&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIK_EDIT_TEMPLATE#}</a>
		</div>
	</div>

	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<col width="50%">
		<col width="50%">
		<thead>
			<tr>
				<td>{#RUBRIK_CODE_START#}</td>
				<td>{#RUBRIK_CODE_END#}</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<div class="pr12">
						<textarea name="rubric_code_start" type="text" id="rubric_code_start" value="" style="height:300px;" />{$code->rubric_code_start}</textarea>
					</div>
				</td>
				<td>
					<div class="pr12">
						<textarea name="rubric_code_end" type="text" id="rubric_code_end" value="" style="height:300px;" />{$code->rubric_code_end}</textarea>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="rowElem" id="saveBtn">
		<div class="saveBtn">
			<input class="basicBtn" type="submit" value="{#RUBRIK_BUTTON_SAVE#}" />
			&nbsp;
			<input type="submit" class="blackBtn SaveEdit" value="{#RUBRIK_BUTTON_TPL_NEXT#}" />
		</div>
	</div>
</div>
</form>
{/if}


{include file="$codemirror_connect"}
{include file="$codemirror_editor" conn_id="rsc" textarea_id='rubric_start_code' ctrls='$("#code").ajaxSubmit(sett_options);' height=300}
{include file="$codemirror_editor" conn_id="rcs" textarea_id='rubric_code_start' ctrls='$("#code").ajaxSubmit(sett_options);' height=300}
{include file="$codemirror_editor" conn_id="rce" textarea_id='rubric_code_end' ctrls='$("#code").ajaxSubmit(sett_options);' height=300}

<script language="javascript">

	var sett_options = {ldelim}
		url: 'index.php?do=rubs&action=code&sub=save&Id={$smarty.request.Id|escape}&cp={$sess}',
		dataType: 'json',
		beforeSubmit: function(){ldelim}
			$.alerts._overlay('show');
			{rdelim},
		success: function(data){ldelim}
			$.jGrowl(data['message'], {ldelim}
				header: data['header'],
				theme: data['theme']
				{rdelim});
			$.alerts._overlay('hide');
			{rdelim}
		{rdelim}

$(document).ready(function(){ldelim}



	Mousetrap.bind(['ctrl+s', 'command+s'], function(event) {ldelim}
		event.preventDefault();
		$("#code").ajaxSubmit(sett_options)
		return false;
	{rdelim});

	$(".SaveEdit").click(function(event){ldelim}
		event.preventDefault();
		$("#code").ajaxSubmit(sett_options);
		return false;
	{rdelim});

{rdelim});
</script>