{if $items}
<input id="feld_{$field_id}_{$doc_id}" name="feld[{$field_id}]" value="{$field_value}" type="hidden" />

{foreach from=$items key=key item=item}
	<input class="float field_multi_checkbox_{$field_id}_{$doc_id}" type="checkbox" value="{$key+1}" {if in_array($key+1, $used)} checked="checked"{/if}
	onchange="
		$('#feld_{$field_id}_{$doc_id}').val('');
				$('#feld_{$field_id}_{$doc_id}').val('');
				var n = $('.field_multi_checkbox_{$field_id}_{$doc_id}:checked').each(
				function() {ldelim}
				$('#feld_{$field_id}_{$doc_id}').val($('#feld_{$field_id}_{$doc_id}').val() > '' ?  $('#feld_{$field_id}_{$doc_id}').val() +'|'+ $(this).val()+'|' : '|'+$(this).val()+'|')
				{rdelim});
	"> <label>{$item}</label>
	<div style="clear: both;"></div>
{/foreach}
{else}
<ul class="messages">
	<li class="highlight yellow">{#no_items#}</li>
</ul>
{/if}
