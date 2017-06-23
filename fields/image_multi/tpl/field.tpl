{if $cascad_new != load}
	{assign var=cascad_new value='' scope="global"}
	{if $smarty.request.outside}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/outside.js" type="text/javascript"></script>
	{else}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/field.js" type="text/javascript"></script>
	{/if}
	<link href="{$ABS_PATH}fields/{$field_dir}/css/field.css" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript">
		var blank = '{$tpl_dir}/images/blanc.gif';
		var place = '{#place#}';
		var look = '{#look#}';
		var del = '{#delete#}';
		var del_conf = '{#del_conf#}';
		var del_head = '{#del_head#}';
		var del_all_c = '{#del_all_c#}';
		var del_all_h = '{#del_all_h#}';
		var max_f_t = '{#max_f_t#}';
		var max_f_h = '{#max_f_h#}';
	</script>
	{assign var=cascad_new value="load" scope="global"}
{/if}

<div class="cascad" id="cascad_{$doc_id}_{$field_id}" data-id="{$field_id}" data-doc="{$doc_id}" data-rubric="{$smarty.request.rubric_id}">
	<input type="hidden" value="" id="cascad__{$field_id}_{$doc_id}">
	{if $show_upload}
	<input type="file" class="cascade_upload cascade_upload_field_{$field_id}_{$doc_id}" multiple="multiple" name="cascade_files_{$field_id}_{$doc_id}[]" style="visibility: hidden; display: none;" data-max-files="{$max_files}" />
	{/if}
	<ul class="messages">
		<li class="highlight grey">
			{#add_n_e#}
			<a href="javascript:void(0);" class="add_single link">{#add_f#}</a>
			&nbsp;|&nbsp;
			<a href="javascript:void(0);" class="add_folder link">{#add_d#}</a>
			&nbsp;|&nbsp;
			<a href="javascript:void(0);" class="del_all link">{#del_all#}</a>
			{if $show_upload}
			&nbsp;|&nbsp;
			<a href="javascript:void(0);" class="upload_local link">{#add_l#}</a>&nbsp;<a href="javascript:void(0);" class="topDir" title="{$max_files}<br>{$dir_upload}">[?]</a>
			{else}
			&nbsp;|&nbsp;
			<a href="javascript:void(0);" class="topDir" title="{#add_upl_e#}<br><br>{$max_files}<br>{$dir_upload}">[?]</a>
			{/if}
		</li>
	</ul>

	<div class="cascad_sortable">
		{foreach from=$images key=key item=image}

			<div class="cascad_item ui-state-default" id="cascad_image_{$field_id}_{$doc_id}_{$key}" data-id="{$key}" data-doc="{$doc_id}">
				<div class="header grey_bg"></div>
				<a class="topDir icon_sprite ico_photo view fancy preview__{$field_id}_{$doc_id}_{$key}" href="{$image.url}" title="{#look#}"></a>
				<a class="topDir icon_sprite ico_delete delete" href="javascript:void(0);" title="{#delete#}" data-id="{$field_id}_{$doc_id}_{$key}"></a>
				<span class="topDir icon_sprite ico_info info" title="{$image.url}"></span>
				<input type="hidden" value="{$image.url}" name="feld[{$field_id}][{$key}][url]" id="image__{$field_id}_{$doc_id}_{$key}">
				<img id="preview__{$field_id}_{$doc_id}_{$key}" src="{$image.thumb}" onclick="browse_uploads('image__{$field_id}_{$doc_id}_{$key}');" class="image" alt="" width="100" height="100" />
				<textarea class="mousetrap" name="feld[{$field_id}][{$key}][descr]" placeholder="{#place#}">{$image.desc}</textarea>
			</div>

		{/foreach}
	</div>

</div>