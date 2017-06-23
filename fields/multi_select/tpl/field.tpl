{if $items}
<select size="5" class="select" style="min-width: 300px; max-width: 600px;" multiple="multiple" name="feld[{$field_id}][]">
{foreach from=$items key=key item=item}
	<option value="{$item|trim|escape}" {if in_array($item|trim|escape, $field_value|trim|escape)} selected="selected"{/if}>{$item}</option>
{/foreach}
</select>
{else}
<ul class="messages">
	<li class="highlight yellow">{#no_items#}</li>
</ul>
{/if}