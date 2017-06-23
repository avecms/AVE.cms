{*
Доступные параметры:
-----------------------------------------------------------
{$field_id}						ID поля
{$field_value}					Данные поля (массив)

$item.Id
$item.document_title
$item.document_alias
$item.document_breadcrum_title

{$field_count}					Кол-во элементов в массиве
{$rubric_id}					ID рубрики
{$default}						Значение по умолчанию

Пример вывода:
-----------------------------------------------------------
*}
<ul>
{foreach from=$field_value item=item}
	<li><a href="{$ABS_PATH}{$item.document_alias}">{$item.document_title}</a></li>
{/foreach}
</ul>