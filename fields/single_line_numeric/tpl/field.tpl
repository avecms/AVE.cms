{if $single_line_numeric != load}
	{assign var=single_line_numeric value='' scope="global"}
	<script language="JavaScript" type="text/javascript" src="{$ABS_PATH}fields/{$field_dir}/js/field.js"></script>
	{assign var=single_line_numeric value="load" scope="global"}
{/if}
<input type="text" style="width: 400px;" name="feld[{$field_id}]" value="{$field_value}" class="field_numeric mousetrap" data-num-dot="2" />