{*
	$field_id
	$field_value
*}
{if $field_value}
{foreach from=$field_value item=tag}
	<li>{$tag}</li>
{/foreach}
{/if}