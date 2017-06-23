{*
Доступные параметры:
-----------------------------------------------------------
{$field_id}						ID поля
{$field_value}					Данные поля (массив)
{$field_count}					Кол-во элементов в массиве
{$rubric_id}					ID рубрики
{$default}						Значение по умолчанию

Пример вывода:
-----------------------------------------------------------
*}

{foreach from=$field_value item=image}
	<img src="{$image[0]}" alt="{if isset($image[1])}{$image[1]}{/if}" title="{if isset($image[1])}{$image[1]}{/if}">
{/foreach}