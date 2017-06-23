<script type="text/javascript">
$(document).ready(function(){ldelim}
	$('#field{$field_id}_{$doc_id}').datetimepicker({ldelim}
		dateFormat: "dd-mm-yy",
	{rdelim});
{rdelim});
</script>

<input id="feld_{$field_id}_{$doc_id}" name="feld[{$field_id}]" value="{$field_value}" type="hidden">

<input id="field{$field_id}_{$doc_id}" type="text" name="field[{$field_id}]" value="{$field_value|date_format:'%d-%m-%Y %H:%M'}" style="width: 250px;" onchange="
		$('#feld_{$field_id}_{$doc_id}').val('');
		$('#feld_{$field_id}_{$doc_id}').val($('#field{$field_id}_{$doc_id}').datetimepicker('getDate')/1000);
">