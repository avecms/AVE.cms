<div style="" id="feld_{$field_id}"><a name="{$field_id}"></a>
<div style="display:none" id="feld_{$field_id}">
<img style="display:none" id="_img_feld__{$field_id}" src="{$field_value}" alt="" border="0" /></div>
<div style="display:none" id="span_feld__{$field_id}"></div>
<input class="mousetrap" type="text" style="width: 400px;" name="feld[{$field_id}]" value="{$field_value|escape}" id="img_feld__{$field_id}" />&nbsp;
<input value="{#MAIN_OPEN_MEDIAPATH#}"" class="basicBtn" type="button" onclick="browse_uploads('img_feld__{$field_id}', '', '', '0');" />&nbsp;
<a class="button blackBtn topDir" title="{#DOC_FILE_TYPE_HELP#}" href="#">?</a>