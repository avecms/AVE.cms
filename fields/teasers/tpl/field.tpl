{if $analoque != load}
	{assign var=analoque value='' scope="global"}
	{if $smarty.request.outside}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/outside.js" type="text/javascript"></script>
	{else}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/field.js" type="text/javascript"></script>
	{/if}
	<link href="{$ABS_PATH}fields/{$field_dir}/css/field.css" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript">
		var analoque_param = '{#param#}';
		var analoque_value = '{#value#}';
		var analoque_add = '{#add#}';
		var analoque_del = '{#delete#}';
		var analoque_del_conf = '{#del_conf#}';
		var analoque_del_head = '{#del_head#}';
	</script>
	{assign var=analoque value="load" scope="global"}
{/if}

<div class="analoque_lists mt10" id="analoque_lists_{$doc_id}_{$field_id}" data-docid="{$doc_id}" data-id="{$field_id}">
{foreach from=$items key=key item=item}

	<div class="analoque_list fix mb10" id="analoque_list_{$doc_id}_{$field_id}_{$key}" data-docid="{$doc_id}" data-fieldid="{$field_id}" data-id="{$key}">
		<input type="text" class="mousetrap search_analoque" value="{$item.param}" name="feld[{$field_id}][{$key}][param]" placeholder="{#param#}" data-docid="{$doc_id}" data-fieldid="{$field_id}" data-id="{$key}" style="width: 450px;"/>&nbsp;&nbsp;Id:&nbsp;<input type="text" class="mousetrap field_{$doc_id}_{$field_id}_{$key}" value="{$item.value}" name="feld[{$field_id}][{$key}][value]" placeholder="{#value#}" style="width: 50px;" readonly />&nbsp;&nbsp;{if $key == 0}<a href="javascript:void(0);" class="button basicBtn topDir AddButton" title="{#add#}">+</a>{else}<a href="javascript:void(0);" data-id="{$doc_id}_{$field_id}_{$key}" class="button redBtn topDir DelButton" title="{#delete#}">&times;</a>{/if}
		<div class="handle" style="float: left; display: inline-block; margin: 4px 7px; cursor: move;"><span class="icon_sprite ico_move"></span></div>
	</div>

{/foreach}
</div>