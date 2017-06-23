{if check_permission('modules_admin')}
<script type="text/javascript" language="JavaScript">
$(document).ready(function(){ldelim}

	$(".ConfirmReInstall").click(function(e){ldelim}
		e.preventDefault();
		var href = $(this).attr('href');
		var title = '{#MODULES_REINSTALL#}';
		var confirm = '{#MODULES_REINSTALL_CONF#}';
		jConfirm(
				confirm,
				title,
				function(b){ldelim}
					if (b){ldelim}
						$.alerts._overlay('show');
						window.location = href;
					{rdelim}
				{rdelim}
			);
	{rdelim});

{rdelim});
</script>
{/if}

<div class="title"><h5>{#MODULES_SUB_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#MODULES_TIP#}
	</div>
</div>

{if isset($errors)}
	<ul class="messages first">
		{foreach from=$errors item=message}
			<li class="highlight red minmarg">{$message}</li>
		{/foreach}
	</ul>
{/if}

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		 <ul>
			 <li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			 <li>{#MODULES_SUB_TITLE#}</li>
		 </ul>
	</div>
</div>

<div class="widget first">
	<ul class="tabs">
		<li class="activeTab"><a href="#tab1">{#MODULES_INSTALLED#}</a></li>
		<li class=""><a href="#tab2">{#MODULES_NOT_INSTALLED#}</a></li>
	</ul>

	<div class="tab_container">
		<div id="tab1" class="tab_content" style="display: block; ">

{if check_permission('modules_system')}
	<form method="post" action="index.php?do=modules&action=quicksave&cp={$sess}">
{/if}

{if $installed_modules}
<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
	<thead>
		<tr>
			<td width="30">?</td>
			<td>{#MODULES_NAME#}</td>
			<td width="200">{#MODULES_TEMPLATE#}</td>
			<td width="150">{#MODULES_SYSTEM_TAG#}</td>
			<td width="50">{#MODULES_VERSION#}</td>
			{if check_permission('modules_system')}
				<td colspan="3" width="60">{#MODULES_ACTIONS#}</td>
			{/if}
		</tr>
	</thead>
	<tbody>
{foreach from=$installed_modules item=module}
		{if check_permission('modules_view')}
			<tr>
				<td align="center">
					<a title="<strong>{$module.ModuleName}</strong><br /><br />{$module.info|escape|default:''}" href="javascript:void(0);" style="cursor:help;" class="rightDir icon_sprite ico_info"></a>
				</td>

				<td nowrap="nowrap">
				{if check_permission('modules_admin')}
					{if $module.ModuleAdminEdit && $module.status && $module.permission}
						<strong><a href="index.php?do=modules&action=modedit&mod={$module.ModuleSysName}&moduleaction=1&cp={$sess}" title="{#MODULES_SETUP#}" class="toprightDir link">{$module.ModuleName}</a></strong>
						{if (isset($module.ModuleTagLink) && $module.ModuleTagLink  != "")}
							<br /><span class="dgrey doclink">{$module.ModuleTagLink}</span>
						{/if}
					{else}
						<strong>{$module.ModuleName}</strong>
					{/if}
				{else if check_permission('modules_view')}
						<strong>{$module.ModuleName}</strong>
				{/if}
				</td>

				<td>
					{if $module.template}
						{assign var=module_id value=$module.id}
						{if $module.status && $module.permission}
							{html_options name=Template[$module_id] options=$all_templates selected=$module.template style="width: 200px"}
						{else}
							{html_options name=Template[$module_id] options=$all_templates selected=$module.template style="width: 200px" disabled="disabled"}
						{/if}
					{else}
						&nbsp;
					{/if}
				</td>

				<td>{if $module.ModuleTag != ""}<input readonly type="text" value="{$module.ModuleTag|stripslashes|default:'&nbsp;'}" style="width: 150px;" />{/if}</td>

				<td align="center"><span class="cmsStats">{$module.ModuleVersion|escape|default:''}</span></td>

				{if check_permission('modules_system')}
					<td align="center" width="20">
						{if $module.status}
							<a title="{#MODULES_STOP#}" href="index.php?do=modules&action=onoff&module={$module.ModuleSysName}&cp={$sess}" class="topDir icon_sprite ico_stop"></a>
						{else}
							<a title="{#MODULES_START#}" href="index.php?do=modules&action=onoff&module={$module.ModuleSysName}&cp={$sess}" class="topDir icon_sprite ico_start"></a>
						{/if}
					</td>

					<td align="center" width="20">
						{if $module.status}
							<a title="{#MODULES_REINSTALL#}" href="index.php?do=modules&action=reinstall&module={$module.ModuleSysName}&cp={$sess}" class="topleftDir ConfirmReInstall icon_sprite ico_reinstall"></a>
						{else}
							<a title="{#MODULES_DELETE#}" dir="{#MODULES_DELETE#}" name="{#MODULES_DELETE_CONFIRM#}" href="index.php?do=modules&action=delete&module={$module.ModuleSysName}&cp={$sess}" class="topleftDir ConfirmDelete icon_sprite ico_delete"></a>
						{/if}
					</td>

					<td align="center" width="20">
						{if $module.need_update}
							<a title="{#MODULES_UPDATE#}" href="index.php?do=modules&action=update&module={$module.ModuleSysName}&cp={$sess}" class="topleftDir icon_sprite ico_globus"></a>
						{else}
							<span title="" class="topleftDir icon_sprite ico_blanc"></span>
						{/if}
					</td>
				{/if}
			</tr>
		{/if}
	{/foreach}

	{if check_permission('modules_system')}
		<tr>
			<td colspan="8"><input type="submit" class="basicBtn" value="{#MODULES_BUTTON_SAVE#}" /></td>
		</tr>
	{/if}

	</tbody>
</table>
	{if check_permission('modules_system')}
		</form>
	{/if}

{else}

<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
	<tbody>
		<tr class="noborder">
		<td colspan="6">
		<ul class="messages">
			<li class="highlight yellow">{#MODULES_NO_INSTALL#}</li>
		</ul>
		</td>
		</tr>
	</tbody>
</table>
{/if}

</div>

<div id="tab2" style="display: none;" class="tab_content">

{if $not_installed_modules}
<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
	<thead>
		<tr>
			<td width="30">?</td>
			<td>{#MODULES_NAME#}</td>
			<td width="150">{#MODULES_SYSTEM_TAG#}</td>
			<td width="50">{#MODULES_VERSION#}</td>
			{if check_permission('modules_system')}<td colspan="2" width="40">{#MODULES_ACTIONS#}</td>{/if}
		</tr>
	</thead>
	<tbody>
	{foreach from=$not_installed_modules item=module}
			<tr>
				<td align="center">
					<a title="<strong>{$module.ModuleName}</strong><br />{$module.info|escape|default:''}" href="javascript:void(0);" style="cursor: help;" class="rightDir icon_sprite ico_info_no"></a>
				</td>

				<td>
					{if $module.ModuleAdminEdit}<strong>{$module.ModuleName}</strong>{else}{$module.ModuleName}{/if}{if $module.ModuleTagLink != ""}<br /><span class="dgrey dotted">{$module.ModuleTagLink}</span>{/if}
				</td>

				<td>{if $module.ModuleTag != ""}<input readonly type="text" value="{$module.ModuleTag|stripslashes|default:'&nbsp;'}" style="width: 150px;" />{/if}</td>

				<td align="center" class="Version">{$module.ModuleVersion|escape|default:''}</td>

				{if check_permission('modules_system')}
					<td align="center" width="20">
						{if check_permission('modules_admin')}<a title="{#MODULES_INSTALL#}" href="index.php?do=modules&action=install&module={$module.ModuleSysName}&cp={$sess}" class="topDir icon_sprite ico_install"></a>{/if}
					</td>
					<td align="center" width="20">
						{if check_permission('modules_admin')}<a title="{#MODULES_REMOVE#}" href="index.php?do=modules&action=remove&module={$module.ModuleSysName}&cp={$sess}" class="topleftDir icon_sprite ico_delete"></a>{/if}
					</td>
				{/if}
			</tr>
	{/foreach}
	</tbody>
</table>
{else}
<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
	<tbody>
		<tr class="noborder">
		<td colspan="6">
		<ul class="messages">
			<li class="highlight yellow">{#MODULES_NOT_INSTALL#}</li>
		</ul>
		</td>
		</tr>
	</tbody>
</table>
{/if}


</div>
			</div>
			<div class="fix"></div>
		</div>