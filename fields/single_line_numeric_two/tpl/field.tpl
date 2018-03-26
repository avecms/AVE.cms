{if $single_line_numeric != load}
	{assign var=single_line_numeric value='' scope="global"}
	<script language="JavaScript" type="text/javascript" src="{$ABS_PATH}fields/{$field_dir}/js/field.js"></script>
	{assign var=single_line_numeric value="load" scope="global"}
{/if}
<input type="text" style="width: 250px;" name="feld[{$field_id}][0]" value="{$field_value.0}" class="field_numeric mousetrap" data-num-dot="2" autocomplete="off" />
<input type="text" style="width: 250px;" name="feld[{$field_id}][1]" value="{$field_value.1}" class="field_numeric mousetrap" data-num-dot="2" autocomplete="off" />