{if $doc_files != load}
	{assign var=doc_files value='' scope="global"}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/field.js" type="text/javascript"></script>
	<link href="{$ABS_PATH}fields/{$field_dir}/css/field.css" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript">
		var links_name = '{#param_name#}';
		var links_desc = '{#param_desc#}';
		var links_url = '{#param_url#}';
		var links_add = '{#add#}';
		var links_del = '{#delete#}';
		var links_del_conf = '{#del_conf#}';
		var links_del_head = '{#del_head#}';
	</script>
	{assign var=doc_files value="load" scope="global"}
{/if}

<div class="doc_files mt10" id="doc_files_{$field_id}" data-id="{$field_id}">
{foreach from=$items key=key item=item}

	<div class="doc_file fix mb10" id="link_{$field_id}_{$key}" data-id="{$key}">
		<div class="handle">
			<span class="icon_sprite ico_move"></span>
		</div>
		<div class="file_block">
			<input type="text" class="mousetrap docs_name" value="{$item.name|escape}" name="feld[{$field_id}][{$key}][name]" placeholder="{#param_name#}"/>
			<textarea class="mousetrap docs_desc" name="feld[{$field_id}][{$key}][descr]" placeholder="{#param_desc#}">{$item.descr|escape}</textarea>
			<input type="text" class="mousetrap docs_url" value="{$item.url|escape}" name="feld[{$field_id}][{$key}][url]" id="links_{$field_id}_{$key}" placeholder="{#param_url#}" />
			<a class="btn greyishBtn" onclick="openFileWindow('links_{$field_id}_{$key}','links_{$field_id}_{$key}','links_{$field_id}_{$key}');">FILE</a>&nbsp;&nbsp;{if $key == 0}<a href="javascript:void(0);" class="button basicBtn topDir AddButton" title="{#add#}">+</a>{else}<a href="javascript:void(0);" data-id="{$field_id}_{$key}" class="button redBtn topDir DelButton" title="{#delete#}">&times;</a>{/if}
		</div>
	</div>

{/foreach}
</div>