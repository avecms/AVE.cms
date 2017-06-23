<div id="AliasCheck">

{if $errors}
<ul class="messages">
	{foreach from=$errors item=error}<li class="highlight red"><strong>{#RUBRIK_ALIAS_ERROR#}</strong> {$error}</li>{/foreach}
</ul>
{/if}

<div class="widget first" style="margin-top: 0;">
	<div class="body">{#RUBRIK_ALIAS_HEAD_T#}</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="javascript:void(0);">{#MAIN_PAGE#}</a></li>
			<li>{#RUBRIK_ALIAS_HEAD#}</li>
			<li>{#RUBRIK_ALIAS_HEAD_R#} <strong class="code">{$rubric_title|escape}</strong></li>
			<li>{#RUBRIK_ALIAS_HEAD_F#} <strong class="code">{$rubric_field_title|escape}</strong></li>
		</ul>
	</div>
</div>


<form name="alias_check" id="alias_check" method="post" action="?do=rubs&action=alias_check&target={$smarty.request.target|escape}&field_id={$smarty.request.field_id|escape}&rubric_id={$smarty.request.rubric_id|escape}&pop=1&onlycontent=1&cp={$sess}" class="mainForm">
	<div class="widget first">
		<div class="head">
			<h5 class="iFrames">{#RUBRIK_ALIAS_ALIAS#}</h5>
		</div>

		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
			<colgroup>
			<col width="200">
			<col>
			</colgroup>
			<tr class="noborder">
				<td>{#RUBRIK_ALIAS_NAME#}</td>
				<td>
					<div class="pr12">
						<input type="text" name="rubric_field_alias" value="{if $smarty.request.rubric_field_alias == ""}{$rubric_field_alias|escape|stripslashes}{else}{$smarty.request.rubric_field_alias}{/if}">
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input class="basicBtn" type="submit" value="{#RUBRIK_ALIAS_BUTT#}" />
				</td>
			</tr>
			</table>

	</div>
</form>

<script language="javascript">

	success = {if $success}true{else}false{/if};

	{literal}
	$('#alias_check').submit(function(event)
	{
		event.preventDefault();

		var form = $(this);

		form.ajaxSubmit({
			url: form.attr('action'),
			type: 'POST',
			beforeSubmit: function()
			{
				$.alerts._overlay('show');
			},
			success: function(data)
			{
				$("#AliasCheck").before(data).remove();
				$.alerts._overlay('hide');
			}
		});

		return false;
	});

	{/literal}

	if (success)
	{ldelim}
		$('#alias_' + {$smarty.request.field_id|escape}).val("{$smarty.request.rubric_field_alias|escape}");
		$('#ajax-dialog-rft-alias-' + {$smarty.request.field_id|escape}).dialog('destroy').remove();
	{rdelim}

</script>
</div>

