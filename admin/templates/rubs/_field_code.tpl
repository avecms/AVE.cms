<div class="title">
	<h5>{#RUBRIK_FIELDS_TEMPLATES_H2#}</h5>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=modules&amp;cp={$sess}">{#MODULES_SUB_TITLE#}</a></li>
			<li><a href="index.php?do=modules&action=modedit&mod=fieldsmanager&moduleaction=1&amp;cp={$sess}">Управление полями</a></li>
			{if $params.id}
			<li><strong>{#RUBRIK_FIELDS_EDIT_RUBRIC#}</strong> > {$params.field.rubric_title}</li>
			<li><strong>{#RUBRIK_FIELDS_EDIT_FIELD#}</strong> > {$params.field.rubric_field_title}</li>
			{/if}
			<li><strong>{#RUBRIK_FIELDS_EDIT_TYPE#}</strong> > {$main.name} {if $params.id}(id: {$params.id}){/if}</li>
			<li>
				<strong class="code">
				{if $params.type == 'adm'}
				{#RUBRIK_FIELDS_EDIT_TPL_ADM#}
				{elseif $params.type == 'doc'}
				{#RUBRIK_FIELDS_EDIT_TPL_DOC#}{
				elseif $params.type == 'req'}
				{#RUBRIK_FIELDS_EDIT_TPL_REQ#}
				{/if}
				</strong>
			</li>
		</ul>
	</div>
</div>

{if $code_text}
<form id="code_templ" method="post" action="index.php?do=rubs&action=ftsave&cp={$sess}" class="mainForm">

	<div class="widget first">
		<div class="head">
			<h5 class="iFrames">{if $params.func == 'new'}{#RUBRIK_FIELDS_EDIT_TPL_CREAT#}{else}{#RUBRIK_FIELDS_EDIT_TPL_EDIT#}{/if}</h5>
		</div>

		<div class="rowElem" style="padding: 0">
			<textarea id="code_text" name="code_text">{$code_text|escape}</textarea>
			<div class="fix"></div>
		</div>

		<div class="rowElem">
			<button class="basicBtn SaveButton">{#RUBRIK_ALIAS_BUTT#}</button>
			&nbsp;
			<a href="javascript:void(0);" class="button redBtn Close">{#RUBRIK_BUTTON_TPL_CLOSE#}</a>
			<div class="fix"></div>
		</div>

	</div>

	<input type="hidden" name="rubric_id" value="{$smarty.request.rubric_id}" />
	<input type="hidden" name="func" value="{$params.func}" />
	{if $params.id}
	<input type="hidden" name="field_id" value="{$params.id}" />
	{/if}
	<input type="hidden" name="field_name" value="{$params.fld}" />
	<input type="hidden" name="field_type" value="{$params.type}" />

</form>

{include file="$codemirror_editor" conn_id="ftpl" textarea_id='code_text' ctrls='$("#code_templ").ajaxSubmit(sett_options);' height=400 mode='smartymixed'}

<script language="javascript">
	$(document).ready(function(){ldelim}

		$(".SaveButton").on('click', function(event){ldelim}
			event.preventDefault();
			$("#code_templ").ajaxSubmit({ldelim}
				url: 'index.php?do=rubs&action=ftsave&cp={$sess}',
				dataType: 'json',
				beforeSubmit: function(){ldelim}
					$.alerts._overlay('show');
				{rdelim},
				success: function(data){ldelim}
					$.jGrowl(data['message'], {ldelim}
						header: data['header'],
						theme: data['theme']
					{rdelim});
						$.alerts._overlay('hide');

						{if $params.func == 'new'}
							var html =
							'<a data-dialog="rft-{$params.id}" href="index.php?do=rubs&action=ftedit&rubric_id={$smarty.request.rubric_id}&id={$params.id}&fld={$params.fld}&type={$params.type}&cp={$sess}&pop=1&onlycontent=1" data-height="650" data-modal="true" class="openDialog">{#RUBRIC_TMPLS_EDIT#}</a>'
							+ '<br />'
							+ '<a href="index.php?do=rubs&action=ftdelete&rubric_id={$smarty.request.rubric_id}&id={$params.id}&fld={$params.fld}&type={$params.type}&cp={$sess}" class="link">{#RUBRIC_TMPLS_DELETE#}</a>'
							;

							$('#{$params.type}_{$params.fld}_{$params.id}').html(html);
							$('a.openDialog').off();
							AveAdmin.modalDialog();
						{/if}

						$('#ajax-dialog-rft-{if $params.id}{$params.id}{else}{$params.fld}{/if}').dialog('destroy').remove();
				{rdelim}
			{rdelim});
			return false;
		{rdelim});

		$(".Close").on('click', function(event){ldelim}
			event.preventDefault();
			$('#ajax-dialog-rft-{if $params.id}{$params.id}{else}{$params.fld}{/if}').dialog('destroy').remove();
			return false;
		{rdelim});

		{literal}
		setTimeout(function(){editorftpl.refresh();}, 20);
		{/literal}

		{rdelim});
</script>

{else}

	<div class="widget first">
		<div class="head">
			<h5 class="iFrames">
				{$main.name}&nbsp;-&nbsp;
				{if $params.type == 'adm'}
				{#RUBRIK_FIELDS_EDIT_TPL_ADM#}
				{elseif $params.type == 'doc'}
				{#RUBRIK_FIELDS_EDIT_TPL_DOC#}{
				elseif $params.type == 'req'}
				{#RUBRIK_FIELDS_EDIT_TPL_REQ#}
				{/if}
			</h5>
		</div>
	</div>

	<ul class="messages first">
		<li class="highlight red">
			<strong>{#RUBRIK_FIELDS_EDIT_NO_TPL#}</strong>
		</li>
	</ul>

{/if}