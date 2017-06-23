<script type="text/javascript" language="JavaScript">
$(document).ready(function(){ldelim}

{if check_permission('group_edit')}
	$(".preAddGroup").click( function(e) {ldelim}
		e.preventDefault();
		var href = $(this).attr('href');
		var title = '{#UGROUP_NEW_GROUP#}';
		var text = '{#UGROUP_NEW_NAME#}';
		jPrompt(text, '', title, function(b){ldelim}
					if (b){ldelim}
						window.location = href + '&user_group_name=' + b;
					{rdelim}
				{rdelim}
			);
	{rdelim});

	$(".AddGroup").click( function(e) {ldelim}
		e.preventDefault();
		var user_group = $('#add_user_group #user_group_name').fieldValue();
		var title = '{#UGROUP_NEW_GROUP#}';
		var text = '{#UGROUP_ENTER_NAME#}';
		if (user_group == ""){ldelim}
			jAlert(text,title);
		{rdelim}else{ldelim}
			$.alerts._overlay('show');
			$("#add_user_group").submit();
		{rdelim}
	{rdelim});
{/if}

{rdelim});
</script>

<div class="title"><h5>{#UGROUP_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#UGROUP_INFO#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li>{#UGROUP_TITLE#}</li>
		</ul>
	</div>
</div>

<div class="widget first">

	<ul class="tabs">
		<li class="activeTab"><a href="#tab1">{#UGROUP_TITLE_MENU#}</a></li>
		{if check_permission('group_edit')}<li class=""><a href="#tab2">{#UGROUP_NEW_GROUP#}</a></li>{/if}
	</ul>

	<div class="tab_container">
		<div id="tab1" class="tab_content" style="display: block;">

			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<thead>
					<tr>
						<td width="40">{#UGROUP_ID#}</td>
						<td>{#UGROUP_NAME#}</td>
						<td width="200">{#UGROUP_COUNT#}</td>
						<td width="50" colspan="2">{#UGROUP_ACTIONS#}</td>
					</tr>
				</thead>
				<tbody>
	{foreach from=$ugroups item=g}
		<tr>
			<td align="center">{$g->user_group}</td>

			<td>
				{if check_permission('group_edit')}
					{if $g->user_group > 2}
						<a title="{#UGROUP_EDIT#}" href="index.php?do=groups&action=grouprights&Id={$g->user_group}&cp={$sess}" class="topDir link"><strong>{$g->user_group_name|escape}</strong></a>
					{else}
						<a title="{#UGROUP_NAME_EDIT#}" href="index.php?do=groups&action=grouprights&Id={$g->user_group}&cp={$sess}" class="topDir link"><strong>{$g->user_group_name|escape}</strong></a>
					{/if}
				{else}
					<strong>{$g->user_group_name|escape}</strong>
				{/if}
			</td>

			<td align="center">{if check_permission('user_view')}{if $g->user_group==2 || $g->UserCount < 1} - {else}<strong class="code"><a title="{#UGROUP_IN_GROUP#}" href="index.php?do=user&cp={$sess}&user_group={$g->user_group}" class="topDir">{$g->UserCount}</a></strong>{/if}{else}<strong>{$g->UserCount}</strong>{/if}</td>

			<td align="center" width="25">
				{if check_permission('group_edit')}
					{if $g->user_group > 2}
						<a title="{#UGROUP_EDIT#}" href="index.php?do=groups&action=grouprights&Id={$g->user_group}&cp={$sess}" class="topleftDir icon_sprite ico_edit"></a>
					{else}
						<a title="{#UGROUP_NAME_EDIT#}" href="index.php?do=groups&action=grouprights&Id={$g->user_group}&cp={$sess}" class="topleftDir icon_sprite ico_edit"></a>
					{/if}
				{else}
					<a title="{#UGROUP_NO_PERMISSION#}" href="javascript:void(0);" class="topleftDir icon_sprite ico_edit_no"></a>
				{/if}
			</td>

			<td align="center" width="25">
				{if check_permission('group_edit')}
					{if $g->user_group > 2}
						{if $g->UserCount > 0}
							<a title="{#UGROUP_USERS_IN_GROUP#}" href="javascript:void(0);" class="topleftDir icon_sprite ico_delete_no"></a>
						{else}
							<a title="{#UGROUP_DELETE#}" dir="{#UGROUP_DELETE#}" name="{#UGROUP_DELETE_CONFIRM#}" href="index.php?do=groups&action=delete&Id={$g->user_group}&cp={$sess}" class="topleftDir ConfirmDelete icon_sprite ico_delete"></a>
						{/if}
					{else}
						<a title="{#UGROUP_NO_DELETABLE#}" href="javascript:void(0);" class="topleftDir icon_sprite ico_delete_no"></a>
					{/if}
				{else}
					<a title="{#UGROUP_NO_PERM_DELETE#}" href="javascript:void(0);" class="topleftDir icon_sprite ico_delete_no"></a>
				{/if}
			</td>
		</tr>
	{/foreach}
		</tbody>
	</table>
		</div>

		{if check_permission('group_edit')}
		<div id="tab2" class="tab_content" style="display: none;">
			<form id="add_user_group" method="post" action="index.php?do=groups&action=new&cp={$sess}" class="mainForm">
			<div class="rowElem">
				<label>{#UGROUP_NEW_NAME#}</label>
				<div class="formRight"><input placeholder="{#UGROUP_NAME#}" name="user_group_name" type="text" id="user_group_name" value="{$g_name|escape}" style="width: 400px">
				&nbsp;<input type="button" class="basicBtn AddGroup" value="{#UGROUP_BUTTON_ADD#}" />
				</div>
				<div class="fix"></div>
			</div>
			</form>
		</div>
		{/if}
	</div>

	<div class="fix"></div>
</div>
