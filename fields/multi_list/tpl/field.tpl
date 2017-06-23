{if $multi_list != load}
	{assign var=multi_list value='' scope="global"}
	{if $smarty.request.outside}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/outside.js" type="text/javascript"></script>
	{else}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/field.js" type="text/javascript"></script>
	{/if}
	<link href="{$ABS_PATH}fields/{$field_dir}/css/field.css" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript">
		var list_param = '{#param#}';
		var list_value = '{#value#}';
		var list_add = '{#add#}';
		var list_del = '{#delete#}';
		var list_del_conf = '{#del_conf#}';
		var list_del_head = '{#del_head#}';
	</script>
	{assign var=multi_list value="load" scope="global"}
{/if}

<div class="multi_lists mt10" id="multi_lists_{$field_id}" data-id="{$field_id}">
{foreach from=$items key=key item=item}

	<div class="multi_list fix mb10" id="list_{$field_id}_{$key}" data-id="{$key}">
		<input type="text" class="mousetrap" value="{$item.param|escape}" name="feld[{$field_id}][{$key}][param]" placeholder="{#param#}" style="width: 200px;"/>&nbsp;&nbsp;<input type="text" class="mousetrap" value="{$item.value|escape}" name="feld[{$field_id}][{$key}][value]" placeholder="{#value#}" style="width: 300px;" />&nbsp;&nbsp;{if $key == 0}<a href="javascript:void(0);" class="button basicBtn topDir AddButton" title="{#add#}">+</a>{else}<a href="javascript:void(0);" data-id="{$field_id}_{$key}" class="button redBtn topDir DelButton" title="{#delete#}">&times;</a>{/if}
		<div class="handle" style="float: left; display: inline-block; margin: 4px 7px; cursor: move;"><span class="icon_sprite ico_move"></span></div>
	</div>

{/foreach}
</div>