<script type="text/javascript" language="JavaScript">
	$nid = parseInt('{$nid}');
	$sess = '{$sess}';

	$(document).ready(function(){ldelim}

		{if check_permission('navigation_edit')}
			$(".AddNavigation").click( function(event) {ldelim}
				event.preventDefault();
				var user_group = $('#add_navigation #navigation_title_new').fieldValue();
				var title = '{#NAVI_NEW_MENU#}';
				var text = '{#NAVI_ENTER_NAME#}';
				if (user_group == ""){ldelim}
					jAlert(text,title);
				{rdelim}else{ldelim}
					$.alerts._overlay('show');
					$("#add_navigation").submit();
				{rdelim}
			{rdelim});
		{/if}

		$(".CopyNavi").click( function(event) {ldelim}
			event.preventDefault();
			var href = $(this).attr('href');
			var title = '{#NAVI_COPY_TEMPLATE#}';
			var text = '{#NAVI_ENTER_NAME#}';
			jPrompt(text, '', title, function(b){ldelim}
						if (b){ldelim}
							$.alerts._overlay('show');
							window.location = href + '&title=' + b;
						{rdelim}
					{rdelim}
				);
		{rdelim});

		var clipboard = new Clipboard('.copyBtn');

	{rdelim});
</script>


<div class="title">
	<h5>{#NAVI_SUB_TITLE#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#NAVI_TIP_TEMPLATE#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li>{#NAVI_SUB_TITLE#}</li>
		</ul>
	</div>
</div>


<div class="widget first">
	<ul class="tabs">
		<li class="activeTab"><a href="#tab1">{#NAVI_ALL#}</a></li>
		{if check_permission('navigation_edit')}<li class=""><a href="#tab2">{#NAVI_NEW_MENU#}</a></li>{/if}
	</ul>

	<div class="tab_container">
		<div id="tab1" class="tab_content" style="display: block;">
		<form class="mainForm">
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<thead>
					<tr>
						<td width="40">{#NAVI_ID#}</td>
						<td>{#NAVI_NAME#}</td>
						<td width="200">{#NAVI_SYSTEM_TAG#}</td>
						<td width="80" colspan="4">{#NAVI_ACTIONS#}</td>
					</tr>
				</thead>
				<tbody>
				{foreach from=$navigations item=item}
				<tr>
					<td align="center">{$item->navigation_id}</td>
					<td>
						<strong>
							{if check_permission('navigation_edit')}
								<a title="{#NAVI_EDIT_ITEMS#}" href="index.php?do=navigation&action=entries&navigation_id={$item->navigation_id}&cp={$sess}" class="topDir link">{$item->title|escape:html|stripslashes}</a>
							{else}
								{$item->navigation_title|escape:html|stripslashes}
							{/if}
						</strong>
					</td>
					<td>
						<div class="pr12" style="display: table">
							<input style="display: table-cell" readonly type="text" id="shot_{$item->navigation_id}" value="[tag:navigation:{if $item->alias}{$item->alias}{else}{$item->navigation_id}{/if}]">
							<a style="display: table-cell; text-align: center" class="whiteBtn copyBtn topDir" href="javascript:void(0);" data-clipboard-action="copy" data-clipboard-target="#shot_{$item->navigation_id}" title="Copy to clipboard">
								<img style="margin-top: -3px; position: relative; top: 4px; padding: 0 3px;" class="clippy" src="{$ABS_PATH}admin/templates/images/clippy.svg" width="13">
							</a>
						</div>
					</td>
					<td width="1%" align="center">
						{if check_permission('navigation_edit')}
							<a title="{#NAVI_EDIT_TEMPLATE#}" href="index.php?do=navigation&action=templates&navigation_id={$item->navigation_id}&cp={$sess}" class="topleftDir icon_sprite ico_template"></a>
						{else}
							<span title="" class="topleftDir icon_sprite ico_template_no"></span>
						{/if}
					</td>
					<td width="1%" align="center">
						{if check_permission('navigation_edit')}
							<a title="{#NAVI_EDIT_ITEMS#}" href="index.php?do=navigation&action=entries&navigation_id={$item->navigation_id}&cp={$sess}" class="topleftDir icon_sprite ico_navigation"></a>
						{else}
							<span title="" class="topleftDir icon_sprite ico_navigation_no"></span>
						{/if}
					</td>
					<td width="1%" align="center">
						{if check_permission('navigation_edit')}
							<a title="{#NAVI_COPY_TEMPLATE#}" href="index.php?do=navigation&action=copy&navigation_id={$item->navigation_id}&cp={$sess}" class="topleftDir CopyNavi icon_sprite ico_copy"></a>
						{else}
							<span title="" class="topleftDir icon_sprite ico_copy_no"></span>
						{/if}
					</td>
					<td width="1%" align="center">
						{if $item->navigation_id == 1}
								<span href="javascript:void(0);" class="topleftDir icon_sprite ico_delete_no"></span>
						{else}
							{if check_permission('navigation_edit')}
								<a title="{#NAVI_DELETE#}" dir="{#NAVI_DELETE#}" name="{#NAVI_DELETE_CONFIRM#}" href="index.php?do=navigation&action=delete&navigation_id={$item->navigation_id}&cp={$sess}" class="topleftDir ConfirmDelete icon_sprite ico_delete"></a>
							{else}
								<span title="" class="topleftDir icon_sprite ico_delete_no"></span>
							{/if}
						{/if}
					</td>
				</tr>
				{/foreach}
				</tbody>
			</table>
		</form>

		</div>
		{if check_permission('navigation_edit')}
		<div id="tab2" class="tab_content" style="display:none;">
			<form id="add_navigation" method="post" action="index.php?do=navigation&action=new&cp={$sess}" class="mainForm">
				<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
					<col width="300">
					<col>
					<tr>
						<td>{#NAVI_TITLE2#}</td>
						<td><input name="navigation_title_new" type="text" id="navigation_title_new" value="" placeholder="{#NAVI_NAME#}" style="width: 400px"></td>
					</tr>
					<tr>
						<td>
							<div class="nowrap">
								<strong><a class="toprightDir" title="{#NAVI_I#}">[?]</a></strong> {#NAVI_ALIAS#}:
							</div>
						</td>
						<td>
							<div class="pr12">
								<input type="text" name="alias" id="alias" value="" class="mousetrap" data-accept="{#NAVI_ACCEPT#}" data-error-syn="{#NAVI_ER_SYN#}" data-error-exists="{#NAVI_ER_EXISTS#}" placeholder="{#NAVI_ALIAS#}" maxlength="20" style="width: 200px;" autocomplete="off" />&nbsp;
								<input type="text" id="alias_tag" value="[tag:navigation:]" readonly size="40" class="mousetrap" style="width: 200px;" />
								<a style="text-align: center; padding: 5px 3px 4px 3px;" class="whiteBtn copyBtn" href="javascript:void(0);" data-clipboard-action="copy" data-clipboard-target="#alias_tag">
									<img style="margin-top: -3px; position: relative; top: 4px; padding: 0 3px;" class="clippy" src="{$ABS_PATH}admin/templates/images/clippy.svg" width="13">
								</a>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2"><input type="button" class="basicBtn AddNavigation" value="{#NAVI_BUTTON_ADD_MENU#}" /></td>
					</tr>
				</table>
			</form>
		</div>
		{/if}
	</div>
	<div class="fix"></div>
</div>

{literal}
<script>
	$(document).on('change', '#alias', function (event) {

		var input = $(this);
		var alias = input.val();

		if (alias > '') {
			$.ajax({
				url: 'index.php?do=navigation&action=alias&cp=' + $sess,
				data: {
					alias: alias
				},
				success: function (data) {
					if (data === '1') {
						$.jGrowl(input.attr('data-accept'), {theme: 'accept'});
					}
					else if (data === 'syn') {
						$.jGrowl(input.attr('data-error-syn'), {theme: 'error'});
						alias = $nid ? $nid : '';
					}
					else {
						$.jGrowl(input.attr('data-error-exists'), {theme: 'error'});
						alias = $nid ? $nid : '';
					}
					$('#alias_tag').val('[tag:navigation:' + alias + ']');
				}
			});
		}
		else {
			alias = $nid ? $nid : '';
			$('#alias_tag').val('[tag:navigation:' + alias + ']');
		}

		return false;
	});
</script>
{/literal}