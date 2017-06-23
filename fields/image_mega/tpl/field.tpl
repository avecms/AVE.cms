{if $mega_new != load}
	{assign var=mega_new value='' scope="global"}
	{if $smarty.request.outside}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/outside.js" type="text/javascript"></script>
	{else}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/field.js" type="text/javascript"></script>
	{/if}
	<link href="{$ABS_PATH}fields/{$field_dir}/css/field.css" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript">
		var mega_blank			= '{$tpl_dir}/images/blanc.gif';
		var mega_title			= '{#img_title#}';
		var mega_description	= '{#img_description#}';
		var mega_link			= '{#img_link#}';
		var mega_look			= '{#look#}';
		var mega_del			= '{#delete#}';
		var mega_del_conf		= '{#del_conf#}';
		var mega_del_head		= '{#del_head#}';
		var mega_del_all_c		= '{#del_all_c#}';
		var mega_del_all_h		= '{#del_all_h#}';
		var mega_max_f_t		= '{#max_f_t#}';
		var mega_max_f_h		= '{#max_f_h#}';
		var mega_from_file		= '{#from_file#}';
		var mega_from_docs		= '{#from_docs#}';
	</script>
	{assign var=mega_new value="load" scope="global"}
{/if}

<div class="mega" id="mega_{$doc_id}_{$field_id}" data-id="{$field_id}" data-doc="{$doc_id}" data-rubric="{$smarty.request.rubric_id}">
	<input type="hidden" value="" id="cascad__{$field_id}_{$doc_id}">
	{if $show_upload}
	<input type="file" class="mega_upload mega_upload_field_{$field_id}_{$doc_id}" multiple="multiple" name="mega_files_{$field_id}_{$doc_id}[]" style="visibility: hidden; display: none;" data-max-files="{$max_files}" />
	{/if}
	<ul class="messages">
		<li class="highlight grey">
			{#add_n_e#}
			<a href="javascript:void(0);" class="mega_add_single link">{#add_f#}</a>
			&nbsp;|&nbsp;
			<a href="javascript:void(0);" class="mega_add_folder link">{#add_d#}</a>
			&nbsp;|&nbsp;
			<a href="javascript:void(0);" class="mega_del_all link">{#del_all#}</a>
			{if $show_upload}
			&nbsp;|&nbsp;
			<a href="javascript:void(0);" class="mega_upload_local link">{#add_l#}</a>&nbsp;<a href="javascript:void(0);" class="topDir" title="{$max_files}<br>{$dir_upload}">[?]</a>
			{else}
			&nbsp;|&nbsp;
			<a href="javascript:void(0);" class="topDir" title="{#add_upl_e#}<br><br>{$max_files}<br>{$dir_upload}">[?]</a>
			{/if}
		</li>
	</ul>

	<div class="mega_sortable">
		{foreach from=$images key=key item=image}

			<div class="mega_item ui-state-default" id="mega_image_{$field_id}_{$doc_id}_{$key}" data-id="{$key}" data-doc="{$doc_id}">
				<div class="header grey_bg"></div>
				<a class="topDir icon_sprite ico_photo view fancy preview__{$field_id}_{$doc_id}_{$key}" href="{$image.url}" title="{#look#}"></a>
				<a class="topDir icon_sprite ico_delete delete" href="javascript:void(0);" title="{#delete#}" data-id="{$field_id}_{$doc_id}_{$key}"></a>
				<div class="mega_block">
					<div class="mega_left_block">
						<span class="topDir icon_sprite ico_info info" title="{$image.url}"></span>
						<input type="hidden" value="{$image.url}" name="feld[{$field_id}][{$key}][url]" id="image__{$field_id}_{$doc_id}_{$key}">
						<img id="preview__{$field_id}_{$doc_id}_{$key}" src="{$image.thumb}" onclick="browse_uploads('image__{$field_id}_{$doc_id}_{$key}');" class="image" alt="" width="128" height="128" />
					</div>
					<div class="mega_left_block">
						<textarea class="mousetrap" name="feld[{$field_id}][{$key}][title]" placeholder="{#img_title#}">{$image.title}</textarea>
						<textarea class="mousetrap" name="feld[{$field_id}][{$key}][description]" placeholder="{#img_description#}">{$image.description}</textarea>
					</div>
				</div>
				<div class="mega_link">
					<input class="mega_link_input mousetrap" id="link_{$field_id}_{$doc_id}_{$key}" name="feld[{$field_id}][{$key}][link]" placeholder="{#img_link#}" value="{$image.link}" />
					<a class="btn greyishBtn" onclick="openFileWindow('link_{$field_id}_{$doc_id}_{$key}','link_{$field_id}_{$doc_id}_{$key}','link_{$field_id}_{$doc_id}_{$key}');">{#from_file#}</a>
					<a class="btn greyishBtn" onclick="openLinkWin('link_{$field_id}_{$doc_id}_{$key}', 'link_{$field_id}_{$doc_id}_{$key}', 'alias');">{#from_docs#}</a>
				</div>
			</div>

		{/foreach}
	</div>

</div>