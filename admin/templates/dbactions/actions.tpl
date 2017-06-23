<div class="title"><h5>{#DB_SUB_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#DB_TIPS#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li>{#DB_SUB_TITLE#}</li>
		</ul>
	</div>
</div>

<div class="widget first">
<form action="index.php?do=dbsettings&cp={$sess}" method="post" name="dbop" id="dbop" class="mainForm">
			<div class="head"><h5 class="iFrames">{#DB_OPTION_LIST#}</h5></div>

<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
	<tbody>
		<tr>
			<td rowspan="3" style="width: 20%;">
				<select style="width:300px" class="select" size="7" name="ta[]" multiple="multiple">
					{$tables}
				</select>
			</td>
			<td align="center">
				<input style="border:0px" type="radio" name="action" checked="checked" class="radio float" value="optimize" />
			</td>
			<td>
				<h4>{#DB_OPTIMIZE_DATABASE#}</h4>
				<p>{#DB_OPTIMIZE_INFO#}</p>
			</td>
		</tr>
		<tr>
			<td align="center">
				<input style="border:0px" type="radio" name="action" class="radio float" value="repair" />
			</td>
			<td>
				<h4>{#DB_REPAIR_DATABASE#}</h4>
				<p>{#DB_REPAIR_INFO#}</p>
			</td>
		</tr>
		<tr>
			<td align="center">
				<input style="border:0px" type="radio" name="action" class="radio float" value="dump" />
			</td>
			<td>
				<h4>{#DB_BACKUP_DATABASE#}</h4>
				<div class="fix mt10"><input type="checkbox" name="server" value="1" class="float" /><label>{#DB_BACKUP_SERVER#}</label></div>
			</td>
		</tr>
	</tbody>
</table>
<div class="rowElem">
	{#MAIN_STAT_MYSQL#} <strong><span class="cmsStats">{$db_size}</span></strong>
	<div class="fix"></div>
</div>
<div class="rowElem">
	<input type="submit" id="rest" class="basicBtn ConfirmDB" value="{#DB_BUTTON_ACTION#}" />
	<div class="fix"></div>
</div>
</form>

<div class="fix"></div>
</div>

{if $msg}
<ul class="messages first">
	{$msg}
</ul>
{/if}

<div class="widget first">

	<ul class="tabs">
		<li class="activeTab"><a href="#tab1">{#DB_RESTORE_TITLE#}</a></li>
		<li class=""><a href="#tab2">{#DB_RESTORE_FILE#}</a></li>
	</ul>

	<div class="tab_container">
		<div id="tab1" class="tab_content" style="display: block;">

			<form action="index.php?do=dbsettings&cp={$sess}" method="post" enctype="multipart/form-data" class="mainForm" id="DBreset">
				<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<tr>
					<td>
						<input type="file" name="file" class="nicefileinput nice input_file" />
					</td>
				</tr>
				<tr>
					<td>
					<input type="submit" id="rest" class="basicBtn ConfirmDBreset" value="{#DB_BUTTON_RESTORE#}" />
					<input type="hidden" name="action" value="restore" />
					</td>
				</tr>
				</table>
			</form>
		</div>

		<div id="tab2" class="tab_content" style="display: none;">
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col>
				<col width="200">
				<col width="200">
				<col width="30">
				<col width="30">
				<col width="30">
				<col width="30">
				<thead>
					<tr>
						<td>{#DB_FILE_NAME#}</td>
						<td>{#DB_FILE_SIZE#}</td>
						<td>{#DB_FILE_DATA#}</td>
						<td nowrap="nowrap" colspan="4" align="center">{#DB_ACTIONS#}</td>
					</tr>
				</thead>

		{if $files}
		{foreach item=item from=$files}
					<tr>
						<td>
							<strong>{$item.name}</strong>
						</td>
						<td class="aligncenter">
							<strong class="code">{$item.size|format_size}</strong>
						</td>
						<td class="aligncenter">
							<span class="date_text dgrey">{$item.data|date_format:$TIME_FORMAT|pretty_date}</span>
						</td>
						<td nowrap="nowrap" width="1%" align="center">
							<span title="{#DB_ACTIONS_EDIT#}" href="#" class="topDir icon_sprite ico_edit_no"></span>
						</td>
						<td nowrap="nowrap" width="1%" align="center">
							<a title="{#DB_ACTIONS_RESTORE#}" dir="{#DB_ACTIONS_RESTORE_H#}" name="{#DB_ACTIONS_RESTORE_T#}" href="index.php?do=dbsettings&action=restorefile&file={$item.name}&cp={$sess}" class="topDir icon_sprite ico_copy ConfirmDelete"></a>
						</td>
						<td nowrap="nowrap" width="1%" align="center">
							<a title="{#DB_ACTIONS_SAVE#}" href="index.php?do=dbsettings&action=download&file={$item.name}&cp={$sess}" class="topDir icon_sprite ico_install"></a>
						</td>
						<td nowrap="nowrap" width="1%" align="center">
							<a title="{#DB_ACTIONS_DEL#}" dir="{#DB_ACTIONS_DELETE_H#}" name="{#DB_ACTIONS_DELETE_T#}" href="index.php?do=dbsettings&action=deletefile&file={$item.name}&cp={$sess}" class="topleftDir ConfirmDelete icon_sprite ico_delete"></a>
						</td>
					</tr>
		{/foreach}

		{else}
					<tr>
						<td colspan="8">
							<ul class="messages">
								<li class="highlight yellow">{#DB_NO_FILES_MESS#}</li>
							</ul>
						</td>
					</tr>
		{/if}

			</table>
		</div>
	</div>
	<div class="fix"></div>
</div>


<script language="javascript">

$(document).ready(function(){ldelim}


	$(".ConfirmDB").click(function(event){ldelim}
		event.preventDefault();
		var title = '{#DB_BUTTON_ACTION#}';
		var confirm = '{#DB_ACTION_WARNING#}';
		jConfirm(
				confirm,
				title,
				function(b){ldelim}
					if (b){ldelim}
						$.alerts._overlay('show');
						$("#dbop").submit();
						$.alerts._overlay('hide');
					{rdelim}
				{rdelim}
			);
	{rdelim});

	$(".ConfirmDBreset").click(function(event){ldelim}
		event.preventDefault();
		var title = '{#DB_BUTTON_ACTION#}';
		var confirm = '{#DB_ACTION_RESET#}';
		jConfirm(
				confirm,
				title,
				function(b){ldelim}
					if (b){ldelim}
						$("#DBreset").submit();
						$.alerts._overlay('show');
					{rdelim}
				{rdelim}
			);
	{rdelim});

	{literal}
	$('.radio').on('change', function(event) {
		event.preventDefault();
		if	($(this).is(':checked')) {
			$('tr').removeClass('yellow');
			$(this).parent().parent().parent('tr').addClass('green');
		} else {
			$(this).parent().parent().parent('tr').removeClass('green');
		}
	});
	{/literal}

{rdelim});
</script>