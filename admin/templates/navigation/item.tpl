			<div class="dd3-content {if $smarty.request.sub == 'new'}green{/if}">
				<div class="name">
					<a href="index.php?do=navigation&action=itemedit&sub=edit&navigation_item_id={$item.navigation_item_id}&cp={$sess}&pop=1" data-width="400px" data-modal="true" data-dialog="item-{$item.navigation_item_id}" data-title="Редактирование пункта меню" class="openDialog">{$item.title}</a>
				</div>

				<div class="url">
					<a style="color: #ccc;" href="{$ABS_PATH}{$item.alias|escape}" class="topDir icon_sprite ico_globus" target="_blank" title="{$item.alias|escape}"></a>
				</div>

				<div class="document">
					{if $item.document_title}
					<a href="index.php?do=docs&action=edit&Id={$item.document_id|escape}&cp={$sess}" class="topDir link" original-title="">{$item.document_title|escape}</a> (ID: {$item.document_id|escape})
					{else}
					<span class="date_text dgrey">{#NAVI_NOLINK_DOC#}</span>
					{/if}
				</div>

				<div class="status">
				{if $item.alias}
					{if $item.status == 1}
					<a href="index.php?do=navigation&action=itemestatus&navigation_item_id={$item.navigation_item_id}&cp={$sess}" data-status="0" class="topleftDir icon_sprite ico_ok_green changeStatus" title="{#NAVI_ITEM_ON_OFF#}"></a>
					{else}
					<a href="index.php?do=navigation&action=itemestatus&navigation_item_id={$item.navigation_item_id}&cp={$sess}" data-status="1" class="topleftDir icon_sprite ico_delete_no changeStatus" title="{#NAVI_ITEM_ON_OFF#}"></a>
					{/if}
				{else}
					{if $item.status == 1}
					<span class="topleftDir icon_sprite ico_ok_green" title="{#NAVI_ITEM_ON_OFF#}"></span>
					{else}
					<span class="topleftDir icon_sprite ico_delete_no" title="{#NAVI_ITEM_ON_OFF#}"></span>
					{/if}
				{/if}
				</div>

				<div class="action">
					<a href="index.php?do=navigation&action=itemedit&sub=edit&navigation_item_id={$item.navigation_item_id}&cp={$sess}&pop=1" data-width="420px" data-modal="true" data-dialog="item-{$item.navigation_item_id}" data-title="{#NAVI_ITEM_EDIT#}" title="{#NAVI_ITEM_EDIT#}" class="openDialog topleftDir icon_sprite ico_edit"></a>
					<a href="index.php?do=navigation&action=itemdelete&navigation_item_id={$item.navigation_item_id}&cp={$sess}" class="topleftDir ConfirmDelete icon_sprite ico_delete" title="{#NAVI_ITEM_DELETE#}" dir="{#NAVI_ITEM_DELETE#}" name="{#NAVI_ITEM_DELETE_CONFIRM#}"></a>
				</div>
			</div>

			{if $smarty.request.sub == 'new'}
				<script language="javascript">
				$(document).ready(function(){ldelim}
					setTimeout(function () {ldelim}
						$('.dd3-content.green').removeClass('green');
						{rdelim}, 2500);
				{rdelim});
				</script>
			{/if}