{if $param.4 == 'embed'}

<object width="{$param.1}" height="{$param.2}">
	<param name="movie" value="{$video_url}" />
	<param name="allowFullScreen" value="{$param.3}" />
	<param name="allowscriptaccess" value="always" />
	<embed type="application/x-shockwave-flash" width="{$param.1}" height="{$param.2}" src="{$video_url}" allowfullscreen="{$param.3}" allowscriptaccess="always"></embed>
</object>

{else}

<iframe width="{$param.1}" height="{$param.2}" src="{$video_url}" frameborder="0" {if $param.3 == 'true'}allowfullscreen{/if}></iframe>

{/if}