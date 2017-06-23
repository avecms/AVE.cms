{if $docsearch != load}
	{assign var=docsearch value='' scope="global"}
	{if $smarty.request.outside}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/outside.js" type="text/javascript"></script>
	{else}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/field.js" type="text/javascript"></script>
	{/if}
	<link href="{$ABS_PATH}fields/{$field_dir}/css/field.css" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript">
		var docsearch_param = '{#param#}';
		var docsearch_value = '{#value#}';
		var docsearch_add = '{#add#}';
		var docsearch_del = '{#delete#}';
		var docsearch_del_conf = '{#del_conf#}';
		var docsearch_del_head = '{#del_head#}';
	</script>
	{assign var=docsearch value="load" scope="global"}
{/if}

<div class="docsearch_lists mt10" id="docsearch_lists_{$doc_id}_{$field_id}" data-docid="{$doc_id}" data-id="{$field_id}">
{foreach from=$items key=key item=item}

	<div class="docsearch_list fix mb10" id="docsearch_list_{$doc_id}_{$field_id}_{$key}" data-docid="{$doc_id}" data-fieldid="{$field_id}" data-id="{$key}">
		<input type="text" class="mousetrap search_docsearch" value="{$item.param}" name="feld[{$field_id}][{$key}][param]" placeholder="{#param#}" data-docid="{$doc_id}" data-fieldid="{$field_id}" data-id="{$key}" style="width: 450px;"/>&nbsp;&nbsp;Id:&nbsp;<input type="text" class="mousetrap field_{$doc_id}_{$field_id}_{$key}" value="{$item.value}" name="feld[{$field_id}][{$key}][value]" placeholder="{#value#}" style="width: 50px;" readonly />&nbsp;&nbsp;{if $key == 0}<a href="javascript:void(0);" class="button basicBtn topDir AddButton" title="{#add#}">+</a>{else}<a href="javascript:void(0);" data-id="{$doc_id}_{$field_id}_{$key}" class="button redBtn topDir DelButton" title="{#delete#}">&times;</a>{/if}
		<div class="handle" style="float: left; display: inline-block; margin: 4px 7px; cursor: move;"><span class="icon_sprite ico_move"></span></div>
	</div>

{/foreach}
</div>