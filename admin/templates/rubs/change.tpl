{if $smarty.request.action == 'change'}
	<div id="rub_field_{$rf.rubric_id}_{$rf.Id}">
		<form name="field_save_{$rf.rubric_id}_{$rf.Id}" id="field_save_{$rf.rubric_id}_{$rf.Id}" method="post" action="index.php?do=rubs&action=changesave&field_id={$rf.Id|escape}&rubric_id={$rf.rubric_id|escape}&pop=1&onlycontent=1&cp={$sess}" class="mainForm">
			<select class="mousetrap" name="rubric_field_type" id="rubric_field_type_{$field_id|escape}" style="width: 200px;">
				{section name=field loop=$fields}
					<option value="{$fields[field].id}" {if $rf.rubric_field_type == $fields[field].id}selected{/if}>{$fields[field].name}</option>
				{/section}
			</select>
			&nbsp;
			<input type="submit" class="basicBtn SaveChangeField" data-id="{$rf.Id|escape}" data-rubric="{$rf.rubric_id|escape}" value="Ok">
		</form>

		<script>
			AveAdmin.ajax();
		</script>
	</div>
{else}
	<div id="rub_field_{$rf.rubric_id}_{$rf.Id}">
		{section name=field loop=$fields}
			{if $rf.rubric_field_type == $fields[field].id}<a href="javascript:void(0);" class="link change_type" data-rubric="{$rf.rubric_id}" data-id="{$rf.Id}">{$fields[field].name}</a>{/if}
		{/section}
	</div>
{/if}
