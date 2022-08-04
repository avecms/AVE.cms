{foreach from=$edit item=group key=title}
	<strong>{$title}</strong>
	<ul>
	{foreach from=$group item=item key=id}
		<li>ID: {$id} - <a href="{$item}" target="_blank">Редактировать</a></li>
	{/foreach}
	</ul>
{/foreach}