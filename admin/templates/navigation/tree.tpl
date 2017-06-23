{if $document->document_linked_navi_id}
	{assign var="navigation_item_selected" value=$document->document_linked_navi_id scope="global"}
{/if}

<select name="document_linked_navi_id" id="document_linked_navi_id" style="width: 450px;">
	<option value="0">&nbsp;</option>
	{foreach from=$navigations item=navigation}
		<optgroup label="({$navigation->navigation_id}) {$navigation->title|escape}">
		{include file="$select_tpl" items=$navigation->navigation_items}
		</optgroup>
	{/foreach}
</select>
