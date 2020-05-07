{*

Доступные параметры:
-----------------------------------------------------------
Id											ID Документа
rubric_id									ID Рубрики
document_parent								ID Документа «родителя»
document_alias								Алиас документа
document_title								Заголовок документа
document_breadcrum_title					Заголовок документа для «хлебных крошек»
document_published							Начало публикации
document_expire								Окончание публикации
document_changed							Дата последнего изменения
document_author_id							ID автора документа
document_in_search							Учавствует в поиске (0|1)
document_meta_keywords						Ключевые слова
document_meta_description					Описание документа
document_meta_robots						Индексация
document_status								Документ опубликован (Статус) (0|1)
document_deleted							Документ удален (0|1)
document_count_view							Кол-во просмотров
document_linked_navi_id						ID пункта меню,с которым связан документ
document_lang								Язык документа
document_lang_group							Языковая группа документа

Пример вывода:
-----------------------------------------------------------
<a href="{$ABS_PATH}{$document.document_alias}">{$document.document_title}</a> Просмотров: {$document.document_count_view}

*}
{$document.document_title|stripcslashes}