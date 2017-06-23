{*
Доступные параметры:
-----------------------------------------------------------
{$ABS_PATH}						Абсолютный путь

{$field_id}						ID поля
{$field_value}					Данные поля (массив)
{$field_count}					Кол-во элементов в массиве
{$rubric_id}					ID рубрики
{$default}						Значение по умолчанию

FOREACH
$image.url						Адрес изображения
$image.title					Заголовок
$image.description				Описание
$image.link						Ссылка
$image.http						Есть ли в ссылке http или https (true/false)

Пример вывода:
<img src="{$image.url}" alt="{if isset($image.title) && $image.title != ''}{$image.title}{/if}" title="{if isset($image.title) && $image.title != ''}{$image.title}{/if}">

Можно использовать тег, для формирования миниатюры
[tag:X000x000:{$image.url}]
-----------------------------------------------------------
*}

{foreach from=$field_value key=key item=image}
	<div>
	{if isset($image.link) && $image.link != ''}
		{if $image.http}
		<a href="{$image.link}">
		{else}
		<a href="{$ABS_PATH}{$image.link}">
		{/if}
	{/if}
		<img src="{$image.url}" alt="{if isset($image.title) && $image.title != ''}{$image.title}{/if}" title="{if isset($image.title) && $image.title != ''}{$image.title}{/if}">
	{if isset($image.link)}
		</a>
	{/if}
	{if isset($image.description) && $image.description != ''}
		<p>
			{$image.description}
		</p>
	{/if}
	</div>
{/foreach}