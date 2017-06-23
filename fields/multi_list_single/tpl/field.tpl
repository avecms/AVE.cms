{if $multi_list_single != load}
	{assign var=multi_list_single value='' scope="global"}
	{if $smarty.request.outside}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/outside.js" type="text/javascript"></script>
	{else}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/field.js" type="text/javascript"></script>
	{/if}
	<link href="{$ABS_PATH}fields/{$field_dir}/css/field.css" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript">
		var s_list_value = '{#value#}';
		var s_list_add = '{#add#}';
		var s_list_del = '{#delete#}';
		var s_list_del_conf = '{#del_conf#}';
		var s_list_del_head = '{#del_head#}';
	</script>
	{assign var=multi_list_single value="load" scope="global"}
{/if}

<div class="multi_lists_single mt10" id="multi_lists_single_{$field_id}" data-id="{$field_id}">
{foreach from=$items key=key item=item}

	<div class="multi_list_single fix mb10" id="list_{$field_id}_{$key}" data-id="{$key}">
		<input type="text" class="mousetrap" value="{$item|escape}" name="feld[{$field_id}][{$key}]" placeholder="{#value#}" style="width: 400px;"/>&nbsp;&nbsp;{if $key == 0}<a href="javascript:void(0);" class="button basicBtn topDir AddSingleButton" title="{#add#}">+</a>{else}<a href="javascript:void(0);" data-id="{$field_id}_{$key}" class="button redBtn topDir DelSingleButton" title="{#delete#}">&times;</a>{/if}
		<div class="handle" style="float: left; display: inline-block; margin: 4px 7px; cursor: move;"><span class="icon_sprite ico_move"></span></div>
	</div>

{/foreach}
</div>