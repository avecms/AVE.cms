<div class="title">
	<h5>{#DOC_ALIASES#}</h5>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB">
				&nbsp;
			</li>
			<li>
				{#DOC_ALIASES_BREAD_RUB#} <strong class="code">{$document->rubric_title}</strong>
			</li>
			<li>
				{#DOC_ALIASES_BREAD_DOC#} <strong class="code">{$document->document_title}</strong>
			</li>
			<li>
				{#DOC_ALIASES_BREAD_URL#} <strong class="code">{$document->document_alias}</strong>
			</li>
		</ul>
	</div>
</div>

<div class="widget first">

	<div class="head">
		<h5 class="iFrames">{#DOC_ALIASES_LIST#}</h5>
	</div>
	<form action="index.php?do=docs&action=aliases_save&cp={$sess}" method="post" class="mainForm" id="Aliases">
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
			<col width="20">
			<col>
			<col width="180">
			<col width="120">
			<col width="20">
			<col width="20">
			<thead>
				<tr class="noborder">
					<td><div align="center"><input type="checkbox" id="selall" value="1"></div></td>
					<td>{#DOC_ALIASES_TABL_H_URL#}</td>
					<td>{#DOC_ALIASES_TABL_H_ADD#}</td>
					<td>{#DOC_ALIASES_TABL_H_AUT#}</td>
					<td colspan="2">{#DOC_ACTIONS#}</td>
				</tr>
			</thead>
			<tbody>
			{if $aliases}
				{foreach from=$aliases item=alias}
				<tr>
					<td align="center">
						<input type="checkbox" class="checkbox topDir" name="alias_del[{$alias->id}]" value="1" title="{#DOC_ALIASES_TABL_CHECK#}">
					</td>
					<td>
						<div class="pr12">
							<a href="javascript:void(0);" class="link editable" id="document_alias_{$alias->id}" data-alias-id="{$alias->id}">{$alias->document_alias}</a>
						</div>
					</td>
					<td align="center">
						<span class="date_text dgrey">{$alias->document_alias_changed|date_format:$DATE_FORMAT|pretty_date}</span>
					</td>
					<td align="center">
						{$alias->document_alias_author_name}
					</td>
					<td align="center">
						<a href="/{$alias->document_alias}" class="icon_sprite ico_globus topleftDir" target="_blank" title="{#DOC_ALIASES_GO#}"></a>
					</td>
					<td align="center">
						<a href="javascript:void(0);" class="icon_sprite ico_delete topleftDir delAlias" data-title="{#DOC_ALIASES_DEL_T#}" data-confirm="{#DOC_ALIASES_DEL_C#}" data-alias-id="{$alias->id}" title="{#DOC_ALIASES_BUTT_DEL#}"></a>
					</td>
				</tr>
				{/foreach}
				<tr>
					<td colspan="6">
						<input type="submit" class="basicBtn Save" value="{#DOC_ALIASES_BUTT_SAV#}"/>
						&nbsp;
						<a href="javascript:void(0);" class="button redBtn Close">{#DOC_ALIASES_BUTT_CLO#}</a>
					</td>
				</tr>
			{else}
				<tr>
					<td colspan="6">
						<ul class="messages">
							<li class="highlight yellow">{#DOC_ALIASES_LIST_EMPT#}</li>
						</ul>
					</td>
				</tr>
				<tr>
					<td colspan="6">
						<a href="javascript:void(0);" class="button redBtn Close">{#DOC_ALIASES_BUTT_CLO#}</a>
					</td>
				</tr>
			{/if}
			</tbody>
		</table>
	</form>
</div>

<div class="widget first">
	<div class="head collapsible" id="opened">
		<h5>{#DOC_ALIASES_ADD#}</h5>
	</div>
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
		<col>
		<thead>
			<tr>
				<td>{#DOC_ALIASES_ADD_VAL#}</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<div class="pr12">
						<input style="float: left;" class="document_alias_field" name="document_alias" type="text" id="new_document_alias" value="" autocomplete="off" />
						<span class="span-form" style="padding-left: 10px;">
							<input class="basicBtn greenBtn AddNewAliasButt" type="submit" value="{#DOC_ALIASES_BUTT_ADD#}" data-field-id="new_document_alias" />
						</span>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>




<script language="javascript">
	$(function(){ldelim}

		AveAdmin.ajax();

		var document_id = '{$smarty.request.doc_id|escape}';
		var session = '{$sess}';
		var apply = '{#DOC_ALIASES_BUTT_APP#}';
		var cancel = '{#DOC_ALIASES_BUTT_CNL#}';

		setClickable();

		{literal}

		$(document).on('click', '.Close', function(event){
			event.preventDefault();
			$('#ajax-dialog-aliases-' + document_id).dialog('destroy').remove();
			return false;
		});

		$(document).on('click', '.Save', function(event){
			event.preventDefault();
			$("#Aliases").ajaxSubmit({
				url: 'index.php?do=docs&action=aliases_save&cp={$sess}',
				dataType: 'json',
				success: function(data){
					ajaxAliases();
				}
			});
			return false;
		});

		$(document).on('change', '#selall', function(event)
		{
			event.preventDefault();
			if ($('#selall').is(':checked')) {
				$('#Aliases .checkbox').attr('checked','checked');
				$('#Aliases .checkbox').addClass('jqTransformChecked');
			} else {
				$('#Aliases .checkbox').removeClass('jqTransformChecked');
				$('#Aliases .checkbox').removeAttr('checked');
			}
		});

		$(document).on('click', '.check', function(event)
		{
			event.preventDefault();
			var doc_al_field = $(this).attr('data-field');
			check(doc_al_field, null);
		});

		$(document).on('click', '.delAlias', function(event) {
			var title = $(this).attr('data-title');
			var confirm = $(this).attr('data-confirm');
			var alias_id = $(this).attr('data-alias-id');
			jConfirm(
				confirm,
				title,
				function(b) {
					if (b) {
						$.ajax({
							url: 'index.php',
							type: 'POST',
							dataType: "json",
							data: ({
								'action': 		'aliases_del',
								'do': 			'docs',
								'cp': 			session,
								'alias_id': 	alias_id
							}),
							success: function (data) {
								ajaxAliases();
							}
						});
					}
				}
			);
		});

		$('.AddNewAliasButt').on('click', function(event)
		{
			event.preventDefault();

			var button = $(this);
			var input_id = button.attr('data-field-id');

			check(input_id, null);

			if (show !== false) {
				$.ajax({
					beforeSend: function(){
						$('#'+input_id).removeClass('input-accept input-error');
					},
					async: false,
					url: 'index.php',
					data: ({
						'action': 		'aliases_new',
						'do': 			'docs',
						'cp': 			session,
						'doc_id': 		document_id,
						'alias': 		$('#'+input_id).val()
					}),
					timeout: 3000,
					dataType: "json",
					success:
						function(data)
						{
							$.jGrowl(data['message'], {
								header: data['header'],
								theme: data['theme']
							});

							if (data['theme'] != 'error') {
								ajaxAliases();
								$('#'+input_id).val('');
							} else {
								$.alerts._overlay('hide');
							}
						}
				});
				return false;
			}

			return false;
		});

		function setClickCancel(id_alias)
		{
			$('#cancel_id_'+id_alias).on('click', function(event){
				event.preventDefault();
				var button = $(this);
				var input_id = button.attr('data-field-id');
				var alias_id = button.attr('data-alias-id');
				var input_val = button.attr('data-backup');
				button.trigger("mouseout").parent().remove();
				$('#'+input_id).parent().html(
					'<a href="javascript:void(0);" class="link editable" id="document_alias_'+alias_id+'" data-alias-id="'+alias_id+'">'+input_val+'</a>'
				);
				setClickable();
			});
		}

		function setClickAccept(id_alias)
		{
			$('#accept_id_'+id_alias).on('click', function(event) {
				event.preventDefault();
				var button = $(this);
				var input_id = button.attr('data-field-id');
				var alias_id = button.attr('data-alias-id');
				var alias = $('#'+input_id).val();
				check(input_id, alias_id);
				if (show !== false) {
					button.trigger("mouseout").parent().remove();
					ajaxAliasEdit(alias_id, alias);
				}
			});
		}

		function setClickable()
		{
			$('.editable').click(function(event){
				event.preventDefault();
				var input = $(this);
				var id_input = input.attr("id");
				var id_alias = input.attr("data-alias-id");
				var alias = input.html();
				var control =
					'<input class="mousetrap editable" name="alias['+id_alias+']" type="text" id="document_alias_'+id_alias+'" value="'+alias+'" style="float: left;" autocomplete="off" data-alias-id="'+id_alias+'">'+
					'<span class="span-form" style="padding-left: 10px;">'+
						'<span title="'+apply+'" id="accept_id_'+id_alias+'" class="icon_sprite ico_ok_green topDir" data-alias-id="'+id_alias+'" data-field-id="'+id_input+'" style="display: inline-block; cursor: pointer;"></span>'+
						'<span title="'+cancel+'" id="cancel_id_'+id_alias+'" class="icon_sprite ico_delete topDir" data-alias-id="'+id_alias+'" data-field-id="'+id_input+'" data-backup="'+alias+'" style="display: inline-block; cursor: pointer;"></span>'+
					'</span>';
				input.prop("readonly", false);
				if (!input.hasClass('operation')){
					input.after(control).remove();
					input.addClass('operation');
					setClickCancel(id_alias);
					setClickAccept(id_alias);
				}
			});
		}

		function ajaxAliases(){
			$.ajax({
				url: 'index.php',
				type: 'POST',
				data: ({
					'action': 		'aliases_doc',
					'do': 			'docs',
					'sub': 			'list',
					'cp': 			session,
					'doc_id': 		document_id,
					'ajax' : 		1,
					'onlycontent': 	1
				}),
				success: function (data) {
					$('#Aliases').before(data).remove();
					$.alerts._overlay('hide');
					setClickable();
				}
			});
		}

		function ajaxAliasEdit(id, alias){
			$.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: "json",
				data: ({
					'action': 		'aliases_edit',
					'do': 			'docs',
					'cp': 			session,
					'id': 			id,
					'alias': 		alias
				}),
				success: function (data) {
					$.jGrowl(data['message'], {
						header: data['header'],
						theme: data['theme']
					});
					if (data['theme'] == 'accept') {
						$('#document_alias_'+id).parent().html(
							'<a href="javascript:void(0);" class="link editable" id="document_alias_'+id+'" data-alias-id="'+id+'">'+alias+'</a>'
						);
						setClickable();
					}
				}
			});
		}

		function check(field, alias_id)
		{
			$.ajax({
				beforeSend: function(){
					$('#'+field).removeClass('input-accept input-error');
				},
				async: false,
				url: 'index.php',
				data: ({
					'action': 		'checkurl',
					'do': 			'docs',
					'cp': 			session,
					'check': 		true,
					'id': 			document_id,
					'alias': 		$('#'+field).val(),
					'alias_id': 	alias_id
				}),
				timeout:3000,
				dataType: "json",
				success:
					function(data)
					{
						$.jGrowl(
							data[0],
							{theme: data[1]}
						);
						if (data[1] == 'accept') {
							$('#'+field).addClass('input-accept');
							show = true;
						} else {
							$('#'+field).addClass('input-error');
							show = false;
						}
					}
			});
			return false; // Default submit return false
		};

		{/literal}

	{rdelim}); // End
</script>