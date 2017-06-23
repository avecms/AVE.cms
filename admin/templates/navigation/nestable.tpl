	{if $items}
	<ol class="dd-list">
	{foreach from=$items key=key item=item}
		<li class="dd-item dd3-item" data-id="{$item.navigation_item_id}" id="item-{$item.navigation_item_id}">
			<div class="dd-handle dd3-handle"></div>
			<div class="dd3-content{if $item.status == 0} red{/if}">
				<div class="name">
					<a href="index.php?do=navigation&action=itemedit&sub=edit&navigation_item_id={$item.navigation_item_id}&cp={$sess}&pop=1" data-title="{#NAVI_ITEM_EDIT#}" data-width="400px" data-modal="true" data-dialog="item-{$item.navigation_item_id}" title="{#NAVI_ITEM_EDIT#}" class="openDialog topDir">{$item.title}</a>
				</div>

				<div class="url">
				{if $item.alias|escape == '/'}
					<a style="color: #ccc;" href="{$item.alias|escape}" class="topDir icon_sprite ico_globus" target="_blank" title="{$item.alias|escape}"></a>
                 {else}
                    <a style="color: #ccc;" href="{$ABS_PATH}{$item.alias|escape}" class="topDir icon_sprite ico_globus" target="_blank" title="{$item.alias|escape}"></a>
                 {/if}
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
		{include file="$nestable_tpl" items=$item.children level=$level+1}
		</li>
	{/foreach}
	</ol>
	{/if}
