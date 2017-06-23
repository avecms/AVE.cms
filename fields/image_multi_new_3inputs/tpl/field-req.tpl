{foreach from=$field_value item=image}
	<img src="{$image[0]}" alt="{if isset($image[1])}{$image[1]}{/if}" title="{if isset($image[1])}{$image[1]}{/if}">
{/foreach}