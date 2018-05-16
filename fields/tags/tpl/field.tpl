{if $field_tags}
<div style="width:25%; float:left;">
{foreach from=$field_tags item=tag name=tags}
	<label style="clear:both; display:block; padding: 2px; width: 90%;">
		<input class="float checkbox" type="checkbox" value="{$tag}" name="feld[{$field_id}][tags][]" {if in_array($tag, $field_value)}checked="checked"{/if} />&nbsp;{$tag}
	</label>
{if in_array($smarty.foreach.tags.index, $field_points)}
	<div class="fix"></div>
</div>
<div style="width:25%; float:left;">
{/if}
{/foreach}
	<div class="fix"></div>
</div>
{else}
<ul class="messages">
	<li class="highlight yellow">{#notags#}</li>
</ul>
{/if}
<div class="fix"></div>
<div>
	<br/>
	<h6>{#new#}</h6>
	<input type="text" style="width: 100%;" name="feld[{$field_id}][tags][other]" value="" class="mousetrap" />
	<div class="fix"></div>
</div>