{if $docfromrubcheck != load}
	{assign var=docfromrubcheck value='' scope="global"}
	{literal}
	<style>
		.ulbox { border-left: 2px solid rgba(0,0,0, 0.1);}
		.ulbox:hover {background: rgba(0,0,0,0.1);}
	</style>
	{/literal}
	{assign var=docfromrubcheck value="load" scope="global"}
{/if}

{if $fields && !isset($error)}
<input id="feld_{$field_id}_{$doc_id}" name="feld[{$field_id}]" value="{$field_value}" type="hidden" />
<ul style="padding-left: 10px">
	{foreach from=$fields key=key item=field}

	<li style="padding: 3px 0">
		<input {if $field.checked == '1'}checked=checked{/if} class="float field_docfromrubcheck_{$field_id}_{$doc_id}" value="{$field.Id}" type="checkbox"
		onchange="
			$('#feld_{$field_id}_{$doc_id}').val('');
					$('#feld_{$field_id}_{$doc_id}').val('');
					var n = $('.field_docfromrubcheck_{$field_id}_{$doc_id}:checked').each(
					function() {ldelim}
					$('#feld_{$field_id}_{$doc_id}').val($('#feld_{$field_id}_{$doc_id}').val() > '' ?  $('#feld_{$field_id}_{$doc_id}').val() +'|'+ $(this).val()+'|' : '|'+$(this).val()+'|')
					{rdelim});
		">
		<label><strong>{$field.document_title}</strong></label>
	</li>

	<div class="fix"></div>
		{include file="$subtpl" items=$field.child}
	{/foreach}
</ul>
{else}
<ul class="messages">
	<li class="highlight yellow">{#error#}</li>
</ul>
{/if}
