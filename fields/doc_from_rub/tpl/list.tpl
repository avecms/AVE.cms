{if $items}
	{assign var=field_level value=$field_level+1 scope="global"}
	{foreach from=$items key=key item=field}
		<option value="{$field.Id}" {if $field.Id == $field_value}selected="selected"{/if}>
			{if $field_level > 0}
				{section name=section start=0 loop=$field_level-1}&mdash;&nbsp;{/section}&mdash;&nbsp;
			{/if}
			{$field.document_title|escape|stripslashes}
		</option>
		{include file="$subtpl" items=$field.child}
	{/foreach}
{/if}
