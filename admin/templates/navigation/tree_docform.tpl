<select name="parent_id" id="parent_id" style="width: 300px;">
	<option value="0">{#DOC_TOP_MENU_ITEM#}</option>
	{foreach from=$navigations item=navigation}
		<optgroup label="({$navigation->navigation_id}) {$navigation->title|escape}">
		{include file="$select_tpl" items=$navigation->navigation_items}
		</optgroup>
	{/foreach}
</select>