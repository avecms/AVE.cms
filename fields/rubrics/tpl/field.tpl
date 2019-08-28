{if $rubrics && !isset($error)}

	<select id="feld_{$field_id}_{$doc_id}" name="feld[{$field_id}]">
		<option value="" {if $field_value == ''}selected="selected"{/if}>По умолчанию</option>
		{foreach from=$rubrics key=key item=rubric name=rubric}
		<option value="{$rubric.id}" {if $rubric.id == $field_value}selected="selected"{/if}>{$rubric.title}</option>
		{/foreach}
	</select>

{else}

	<ul class="messages">
		<li class="highlight yellow">{#error#}</li>
	</ul>

{/if}
