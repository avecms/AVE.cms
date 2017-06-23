<div class="mb10">
	Ссылка:
	<input type="text" style="width: 100%;" name="feld[{$field_id}][url]" value="{$video.0}" class="mousetrap" />
</div>

<div class="mb10">
	Ширина: <input type="text" style="width: 50px;" name="feld[{$field_id}][width]" value="{$video.1}" class="mousetrap" /> px
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	Высота: <input type="text" style="width: 50px;" name="feld[{$field_id}][height]" value="{$video.2}" class="mousetrap" /> px
</div>

<div class="mb10">
	Полноэкранный режим:
	<select name="feld[{$field_id}][fullscreen]">
		<option value="true" {if $video.3 == 'true'}selected{/if}>Разрешить</option>
		<option value="false" {if $video.3 == 'false'}selected{/if}>Запретить</option>
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	Метод вставки:
	<select name="feld[{$field_id}][source]">
		<option value="embed" {if $video.4 == 'embed'}selected{/if}>Embed</option>
		<option value="iframe" {if $video.4 == 'iframe'}selected{/if}>Iframe</option>
	</select>
</div>
