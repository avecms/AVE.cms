{if $items}
<select name="feld[{$field_id}]" style="width: 400px;">
	{foreach from=$items key=key item=item}
	<option value="{$item|trim|escape}" {if $item|trim|escape == $field_value}selected="selected"{/if}>{$item|trim|escape}</option>
	{/foreach}
</select>
{else}
<ul class="messages">
	<li class="highlight yellow">{#no_items#}</li>
</ul>
{/if}