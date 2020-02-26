<script language="Javascript" type="text/javascript">
	var sess = '{$sess}';
</script>

<div class="title">
	<h5>{#SYS_GROUPS#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
        {#SYS_GROUPS_TIP#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li>
				<a href="index.php?do=sysblocks&cp={$sess}" title="">{#SYSBLOCK_LIST_LINK#}</a>
			</li>
			<li>{#SYS_GROUPS#}</li>
		</ul>
	</div>
</div>

<div class="widgets">
	<table class="first tableButtons" cellpadding="0" cellspacing="0" width="100%" id="sysblocksButtons">
		<colgroup>
			<col width="25%">
			<col width="25%">
			<col width="25%">
			<col width="25%">
		</colgroup>
		<tbody>
		<tr>
			<td>
				<a class="button greyishBtn topBtn" href="index.php?do=sysblocks&cp={$sess}">{#SYSBLOCK_LIST_LINK#}</a>
			</td>
			<td>
				<a class="button greenBtn topBtn" href="index.php?do=sysblocks&action=new&cp={$sess}">{#SYSBLOCK_BUTTON_ADD#}</a>
			</td>
			<td>
				<a class="button basicBtn topBtn" href="index.php?do=sysblocks&action=groups&cp={$sess}">{#SYS_GROUPS#}</a>
			</td>
		</tr>
		</tbody>
	</table>
</div>

{if $groups}
	<div class="widget first">

		<div class="head">
			<h5 class="iFrames">{#SYS_GROUPS#}</h5>
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
					<td align="center"><a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#SYS_GROUPS_SORT_TIP#}">[?]</a></td>
					<td align="center">{#SYS_GROUPS_GROUP_TITLE#}</td>
					<td align="center"></td>
				</tr>
				</thead>

				<tbody>
	            {foreach from=$groups item=group}
					<tr {if $groups|@count >= 2} data-id="group_{$group.id}" class="group_tbody"{/if}>
						<td align="center">
	                        {$group.id}
						</td>
						<td align="center">
							<a class="icon_sprite ico_move{if $groups|@count < 2}_no{/if}" style="cursor:move"></a>
						</td>
						<td>
							{$group.title}
						</td>
						<td align="center">
							<a href="index.php?do=sysblocks&action=delgroup&id={$group.id}&cp={$sess}" class="ConfirmDelete topleftDir icon_sprite ico_delete" title="{#SYS_GROUPS_DELETE#}" dir="{#SYS_GROUPS_DELETE#}" name="{#SYS_GROUPS_DELETE_H#}"></a>
						</td>
					</tr>
	            {/foreach}
				</tbody>
			</table>
		</div>
	</div>

{else}

	<div class="widget first">

		<div class="head">
			<h5 class="iFrames">{#SYS_GROUPS#}</h5>
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
							<li class="highlight red">{#SYS_NO_GROUPS#}</li>
						</ul>
					</td>
				</tr>
				</tbody>

			</table>

		</div>
	</div>

{/if}

<div class="widget first">

	<div class="head collapsible" id="opened">
		<h5>{#SYS_GROUPS_NEW#}</h5>
	</div>

	<div style="display: block;">
		<form id="newfld" action="index.php?do=sysblocks&action=newgroup&cp={$sess}" method="post" class="mainForm">
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col width="250">
				<col>
				<thead>
				<tr>
					<td></td>
					<td></td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>
                        {#SYS_GROUP_TITLE#}
					</td>
					<td>
						<div class="pr12">
							<input name="title" type="text" id="title" value="" autocomplete="off" />
						</div>
					</td>
				</tr>
				<tr>
					<td>
						{#SYS_GROUP_DESCRIPTION#}
					</td>
					<td>
						<div class="pr12">
							<textarea name="description" id="description" rows="8" cols="" class="mousetrap"></textarea>
						</div>
					</td>
				</tr>
				</tbody>
			</table>
			<div class="rowElem">
				<input class="basicBtn AddNewField" type="submit" value="{#SYS_GROUP_BTN#}" />
			</div>
		</form>
	</div>
</div>


<script language="javascript">

	$(document).ready(function(){ldelim}

		// сортировка
		$('#GroupsList tbody').tableSortable({ldelim}
			items: '.group_tbody',
			url: 'index.php?do=sysblocks&action=groupssort&cp={$sess}',
			success: true
            {rdelim});

        {rdelim});
</script>
