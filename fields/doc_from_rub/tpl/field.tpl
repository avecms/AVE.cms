{if $fields && !isset($error)}

	<select id="feld_{$field_id}_{$doc_id}" name="feld[{$field_id}]">
		{foreach from=$fields key=key item=field name=field}
			<option value="{$field.Id}" {if $field.Id == $field_value}selected="selected"{/if}>{$field.document_title}</option>
			{assign var=field_level value="0" scope="global"}
			{include file="$subtpl" items=$field.child}
		{/foreach}
	</select>

{else}

	<ul class="messages">
		<li class="highlight yellow">{#error#}</li>
	</ul>

{/if}
