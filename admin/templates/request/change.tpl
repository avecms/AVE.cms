{if $smarty.request.sub == ''}
	<div id="req_cond_{$cond_id}" stye="text-align: left;">
			<select class="mousetrap" name="conditions[{$cond_id}][condition_field_id]" style="width: 200px;" id="form_cond_{$cond_id}">
				{foreach from=$fields_list item=field_group}

					{if $groups_count > 1}
					<optgroup label="{if $field_group.group_title}{$field_group.group_title}{else}{#REQUEST_FIELD_G_UNKNOW#}{/if}">
					{/if}

					{foreach  from=$field_group.fields item=field}
						<option value="{$field.Id|escape}" {if $field_id ==$field.Id}selected{/if}>{$field.rubric_field_title|escape} (ID: {$field.Id|escape})</option>
					{/foreach}

					{if $groups_count > 1}
					</optgroup>
					{/if}

				{/foreach}
			</select>
			&nbsp;
			<input type="submit" class="basicBtn SaveChange" data-id="{$cond_id}" data-field="{$field_id}" value="Ok">
		<script>
			AveAdmin.ajax();
		</script>
	</div>
{else}
	{foreach from=$fields_list item=field_group}

		{foreach  from=$field_group.fields item=field}
			{if $field_id == $field.Id}
			<div id="req_cond_{$cond_id}">
				<a href="javascript:void(0);"  class="link change_field" data-id="{$field.Id|escape}" data-cond="{$cond_id}">{$field.rubric_field_title|escape} (ID: {$field.Id|escape})</a>
				<input type="hidden" name="conditions[{$cond_id}][condition_field_id]" value="{$field.Id|escape}" />
			</div>
			{/if}
		{/foreach}

	{/foreach}
{/if}
