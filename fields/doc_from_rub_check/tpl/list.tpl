{if $items}
<ul style="padding-left: 20px" class="ulbox">
	{foreach from=$items key=key item=field}
	<li class="fix" style="padding: 3px 0">
		<input {if $field.checked == '1'}checked=checked{/if} class="float field_docfromrubcheck_{$field_id}_{$doc_id}" value="{$field.Id}" type="checkbox"
		onchange="
			$('#feld_{$field_id}_{$doc_id}').val('');
					$('#feld_{$field_id}_{$doc_id}').val('');
					var n = $('.field_docfromrubcheck_{$field_id}_{$doc_id}:checked').each(
					function() {ldelim}
					$('#feld_{$field_id}_{$doc_id}').val($('#feld_{$field_id}_{$doc_id}').val() > '' ?  $('#feld_{$field_id}_{$doc_id}').val() +'|'+ $(this).val()+'|' : '|'+$(this).val()+'|')
					{rdelim});
		">
		<label>{$field.document_title}</label>
	</li>
		<div class="fix"></div>
		{include file="$subtpl" items=$field.child}
	{/foreach}
</ul>
{/if}
