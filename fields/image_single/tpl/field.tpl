{if $single_image != load}
	{assign var=single_image value='' scope="global"}
	<script src="{$ABS_PATH}fields/{$field_dir}/js/field.js" type="text/javascript"></script>
	<link href="{$ABS_PATH}fields/{$field_dir}/css/field.css" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript">
	var thumbdir = "{$smarty.const.THUMBNAIL_DIR}";
	</script>
	{assign var=single_image value="load" scope="global"}
{/if}

<div class="single_image_images">
	<div class="img single_image" data-id="{$field_id}" data-doc="{$doc_id}">
		<div class="header grey_bg"></div>
		<a class="topDir icon_sprite ico_photo view fancy preview__{$field_id}_{$doc_id}" href="{$image.0|htmlspecialchars}" title="{#look#}"></a>
		<a class="topDir icon_sprite ico_edit lnk" href="javascript:void(0);" title="{#link#}"></a>
		<input style="display: none;" type="text" name="feld[{$field_id}][img]" value="{$image.0|htmlspecialchars}" id="image__{$field_id}_{$doc_id}">
		<img id="preview__{$field_id}_{$doc_id}" src="{$field}" alt="" border="0" onclick="browse_uploads('image__{$field_id}_{$doc_id}');" style="max-width: 128px;" class="topDir" title="{#select#}" />
		<textarea class="descr mousetrap" name="feld[{$field_id}][descr]" placeholder="{#place#}">{$image.1|htmlspecialchars}</textarea>
	</div>
</div>