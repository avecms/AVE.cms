<div class="mb10">
	{#f_url#}:
	<input type="text" style="width: 100%;" name="feld[{$field_id}][url]" value="{$video.0}" class="mousetrap" />
</div>

<div class="mb10">
	{#f_width#}: <input type="text" style="width: 50px;" name="feld[{$field_id}][width]" value="{$video.1}" class="mousetrap" /> px
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	{#f_height#}: <input type="text" style="width: 50px;" name="feld[{$field_id}][height]" value="{$video.2}" class="mousetrap" /> px
</div>

<div class="mb10">
	{#f_fullscreen#}:
	<select name="feld[{$field_id}][fullscreen]">
		<option value="true" {if $video.3 == 'true'}selected{/if}>{#f_allow#}</option>
		<option value="false" {if $video.3 == 'false'}selected{/if}>{#f_forbidden#}</option>
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	{#f_metod#}:
	<select name="feld[{$field_id}][source]">
		<option value="embed" {if $video.4 == 'embed'}selected{/if}>Embed</option>
		<option value="iframe" {if $video.4 == 'iframe'}selected{/if}>Iframe</option>
	</select>
</div>