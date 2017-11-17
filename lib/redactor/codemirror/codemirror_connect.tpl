{assign var=codemirror value="load" scope="global"}
<!-- CodeMirror Files -->
<link rel="stylesheet" href="{$ABS_PATH}lib/redactor/codemirror/lib/codemirror.css">
<link rel="stylesheet" href="{$ABS_PATH}lib/redactor/codemirror/addon/hint/show-hint.css">
<link rel="stylesheet" href="{$ABS_PATH}lib/redactor/codemirror/addon/dialog/dialog.css">
<link rel="stylesheet" href="{$ABS_PATH}lib/redactor/ckeditor/plugins/codemirror/css/codemirror.min.css">

{if $smarty.const.CODEMIRROR_THEME != '' && $smarty.const.CODEMIRROR_THEME != 'default'}
<link rel="stylesheet" href="{$ABS_PATH}lib/redactor/codemirror/theme/{$smarty.const.CODEMIRROR_THEME}.css">
{/if}

<script src="{$ABS_PATH}lib/redactor/codemirror/lib/codemirror.js" type="text/javascript"></script>
<script src="{$ABS_PATH}lib/redactor/codemirror/mode/xml/xml.js"></script>
<script src="{$ABS_PATH}lib/redactor/codemirror/mode/javascript/javascript.js"></script>
<script src="{$ABS_PATH}lib/redactor/codemirror/mode/css/css.js"></script>
<script src="{$ABS_PATH}lib/redactor/codemirror/mode/clike/clike.js"></script>
<script src="{$ABS_PATH}lib/redactor/codemirror/mode/php/php.js"></script>
<script src="{$ABS_PATH}lib/redactor/codemirror/mode/smarty/smarty.js"></script>
<script src="{$ABS_PATH}lib/redactor/codemirror/mode/htmlmixed/htmlmixed.js"></script>

<script src="{$ABS_PATH}lib/redactor/codemirror/addon/edit/closetag.js"></script>
<script src="{$ABS_PATH}lib/redactor/codemirror/addon/edit/matchbrackets.js"></script>
<script src="{$ABS_PATH}lib/redactor/codemirror/addon/selection/active-line.js"></script>
<script src="{$ABS_PATH}lib/redactor/codemirror/addon/dialog/dialog.js"></script>
<script src="{$ABS_PATH}lib/redactor/codemirror/addon/search/searchcursor.js"></script>
<script src="{$ABS_PATH}lib/redactor/codemirror/addon/search/search.js"></script>

<script src="{$ABS_PATH}lib/redactor/codemirror/functions.js"></script>
<!-- / CodeMirror Files -->
