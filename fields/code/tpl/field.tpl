{if $codemirror != load}
	{assign var = codemirror value='' scope="global"}
	{include file = "$codemirror_connect"}
	{assign var = codemirror value="load" scope="global"}
{/if}

<textarea id="field_code_{$f_id}" name="feld[{$field_id}]">{$field_value|escape}</textarea>
{include file="$codemirror_editor" conn_id=$f_id textarea_id="field_code_$f_id" ctrls='SaveAjax();' height=300}