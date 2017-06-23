{if $smarty.request.action == 'changegroup'}

	<div id="rub_field_group_{$rf.rubric_id}_{$rf.Id}">
		<form name="field_save_{$rf.rubric_id}_{$rf.Id}" id="field_save_{$rf.rubric_id}_{$rf.Id}" method="post" action="index.php?do=rubs&action=changegroupsave&field_id={$rf.Id|escape}&rubric_id={$rf.rubric_id|escape}&pop=1&onlycontent=1&cp={$sess}" class="mainForm">
			<select class="mousetrap" name="rubric_field_group" id="rubric_field_group_{$field_id|escape}" style="width: 200px;">
					<option value="">{#RUBRIK_FIELD_GROUP_SEL#}</option>
					{foreach from=$groups item=group}
					<option value="{$group->Id}" {if $rf.rubric_field_group == $group->Id}selected{/if}>{$group->group_title}</option>
					{/foreach}
			</select>
			&nbsp;
			<input type="submit" class="basicBtn SaveChangeFieldGroup" data-id="{$rf.Id|escape}" data-rubric="{$rf.rubric_id|escape}" value="Ok">
		</form>

		<script>
			AveAdmin.ajax();
		</script>

	</div>
{else}

	<script>
		AveAdmin.ajaxSave();
	</script>

{/if}
