{if $items}
	{foreach from=$items item=item}
	{$item.level}
		<option value="{$item.navigation_item_id}" {if $navigation_item_selected == $item.navigation_item_id}selected{/if}>
			{if $item.level > 0}
				{section name=section start=0 loop=$item.level-1}&mdash;&nbsp;{/section}
			{/if}
			{$item.title|escape}
		</option>
		{include file="$select_tpl" items=$item.children}
	{/foreach}
{/if}