<script language="Javascript" type="text/javascript">
var sess = '{$sess}';
</script>

<div class="title">
	<h5>{#RUBRIK_FIELDS_GROUPS#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#RUBRIK_FIELDS_GROUPS_TIP#}
	</div>
</div>

<table class="first tableButtons" cellpadding="0" cellspacing="0" width="100%" id="rubricButtons">
	<col width="20%">
	<col width="20%">
	<col width="20%">
	<col width="20%">
	<col width="20%">
	<tr>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=edit&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_FIELDS#}</a>
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=ftlist&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_FTEMPLATES#}</a>
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=template&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_TEMPLATES#}</a>
		</td>
		<td>
			{if check_permission('rubric_code')}
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=code&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_CODE#}</a>
			{/if}
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=rules&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_RULES#}</a>
		</td>
	</tr>
</table>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB">
				<a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a>
			</li>
			<li>
				<a href="index.php?do=rubs&cp={$sess}">{#RUBRIK_SUB_TITLE#}</a>
			</li>
			<li>{#RUBRIK_HEADER_GROUP#}</li>
			<li><strong class="code">{$rubric->rubric_title}</strong></li>
		</ul>
	</div>
</div>

{if $groups}

<form action="index.php?do=rubs&action=savefieldsgroup&Id={$smarty.request.Id|escape}&cp={$sess}" method="post" class="mainForm" id="RubricFieldsGroups">

	<div class="widget first">

		<div class="head">
			<h5 class="iFrames">{#RUBRIK_FIELDS_GROUPS#}</h5>
		</div>

		<div id="groups_list">

		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic" id="GroupsList">
			<col width="10">
			<col width="10">
			<col>
			<col width="10">
			<thead>
				<tr>
					<td align="center">ID</td>
					<td align="center"><a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_F_SORT_TIP#}">[?]</a></td>
					<td align="center">{#RUBRIC_F_GROUP_TITLE#}</td>
					<td align="center"></td>
				</tr>
			</thead>

			<tbody>
				{foreach from=$groups item=group}
				<tr {if $groups|@count >= 2} data-id="group_{$group->Id}" class="group_tbody"{/if}>
					<td align="center">
						{$group->Id}
					</td>
					<td align="center">
						<a class="icon_sprite ico_move{if $groups|@count < 2}_no{/if}" style="cursor:move"></a>
					</td>
					<td align="center">
						<input type="text" name="group_title[{$group->Id}]" value="{$group->group_title}" style="width: 100%;" />
					</td>
					<td align="center">
						<a href="index.php?do=rubs&action=delfieldsgroup&Id={$group->Id}&rubric_id={$smarty.request.Id}&cp={$sess}" class="ConfirmDelete topleftDir icon_sprite ico_delete" title="{#RUBRIC_F_GROUP_DELETE#}" dir="{#RUBRIC_F_GROUP_DELETE#}" name="{#RUBRIC_F_GROUP_DELETE_H#}"></a>
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
		<div class="rowElem" id="saveBtn">
			<div class="saveBtn">
				<input class="basicBtn" type="submit" value="{#RUBRIK_BUTTON_SAVE#}" />
			</div>
		</div>
		</div>
	</div>
</form>

{else}

	<div class="widget first">

		<div class="head">
			<h5 class="iFrames">{#RUBRIK_FIELDS_GROUPS#}</h5>
		</div>

		<div id="groups_list">

		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic" id="GroupsList">
			<col width="36">
			<col>
			<col width="36">
			<thead>
				<tr>
					<td align="center"></td>
					<td align="center"></td>
					<td align="center"></td>
				</tr>
			</thead>
			<tbody class="field_tbody">
				<tr>
					<td align="center" colspan="10">
						<ul class="messages">
							<li class="highlight red">{#RUBRIC_NO_GROUPS#}</li>
						</ul>
					</td>
				</tr>
			</tbody>

		</table>

		</div>
	</div>

{/if}

{* Новое Группа *}
<div class="widget first">

	<div class="head collapsible" id="opened">
		<h5>{#RUBRIK_NEW_GROUP#}</h5>
	</div>

	<div style="display: block;">
		<form id="newfld" action="index.php?do=rubs&action=newfieldsgroup&Id={$smarty.request.Id|escape}&cp={$sess}" method="post" class="mainForm">
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col>
				<thead>
					<tr>
						<td>{#RUBRIC_F_GROUP_TITLE#}</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="pr12">
								<input name="group_title" type="text" id="group_title" value="" style="width:100%;" />
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="rowElem">
				<input class="basicBtn AddNewField" type="submit" value="{#RUBRIC_GROUP_ADD#}" />
			</div>
		</form>
	</div>
</div>


<script language="javascript">

$(document).ready(function(){ldelim}

	// сортировка
	$('#GroupsList tbody').tableSortable({ldelim}
		items: '.group_tbody',
		url: 'index.php?do=rubs&action=fieldsgroupssort&cp={$sess}',
		success: true
	{rdelim});

{rdelim});
</script>
