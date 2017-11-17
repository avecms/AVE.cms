<div class="title">
	<h5>{#NAVI_SUB_TITLE2#}</h5>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=navigation&cp={$sess}" title="">{#NAVI_SUB_TITLE#}</a></li>
			<li>{#NAVI_SUB_TITLE2#}</li>
			<li><strong class="code">{$navigation->title|escape|stripslashes}</strong></li>
		</ul>
	</div>
</div>

<div class="widget first">
	<div class="head">
		<h5 class="iFrames">Структура</h5>
		<div class="num">
			<a class="basicNum topDir" href="index.php?do=navigation&cp={$sess}">{#NAVI_RETURN_TO_LIST#}</a>
		</div>
		<div class="num">
			<a class="greenNum topDir" href="index.php?do=navigation&action=templates&navigation_id={$smarty.request.navigation_id}&cp={$sess}">{#NAVI_EDIT_TEMPLATE#}</a>
		</div>
	</div>

	<div class="body" id="navigation-menu">
		<a href="index.php?do=navigation&action=itemedit&sub=new&navigation_id={$smarty.request.navigation_id}&cp={$sess}&pop=1" data-width="420" data-modal="true" data-dialog="item-new" data-title="{#NAVI_ITEM_ADD#}" class="openDialog button greenBtn" id="addNewItem">{#NAVI_ITEM_ADD#}</a>
		&nbsp;
		<a href="javascript:void(0);" class="link" data-action="expand-all">{#NAVI_OPEN_ALL#}</a>
		&nbsp;
		<a href="javascript:void(0);" class="link" data-action="collapse-all">{#NAVI_CLOSE_ALL#}</a>
	</div>

	<div class="dd mainForm">
		{if $items}
		{include file="$nestable_tpl" items=$items}
		{else}
		<ol class="dd-list"></ol>
		{/if}
	</div>
</div>

<script>

	var sess = '{$sess}';
	var abs_path = '{$ABS_PATH}';
	var navigation_id = {$smarty.request.navigation_id};

{literal}
$(document).ready(function ()
{
	$('#navigation-menu').on('click', function(event)
	{
		var target = $(event.target),
			action = target.data('action');
		if (action === 'expand-all') {
			$('.dd').nestable('expandAll');
		}
		if (action === 'collapse-all') {
			$('.dd').nestable('collapseAll');
		}
	});

	// сохранение по Ctrl+S
	Mousetrap.bind(['ctrl+s', 'command+s'], function(event) {
		if (event.preventDefault)
		{
			event.preventDefault();
		}
		else
		{
			event.returnValue = false;
		}
		return false;
	});

	$('.dd').nestable({maxDepth: 3}).on('change', function() {

		parse = $(this).nestable('serialize');

		$.ajax ({
			url: 'index.php?do=navigation&action=sorting&cp=' + sess,
			type: 'POST',
			dataType: 'JSON',
			data: {
				'data': parse,
				'navigation_id': navigation_id
			},
			beforeSend: function() {
				$.alerts._overlay('show');
			},
			success: function(data) {
				$.jGrowl(data['message'], {
					header: data['header'],
					theme: data['theme']
				});
				$.alerts._overlay('hide');
			}
		});
	});

	$.fn.fromDocList = function set_value(target_id, doc_id) {
		$.ajax ({
			url: 'index.php?do=navigation&cp=' + sess,
			type: 'POST',
			dataType: 'JSON',
			data: {
				'action':'itemeditid',
				'doc_id': doc_id
			},
			success: function(data)
			{
				//console.log(data);
				$('#document_id').val(doc_id);
				$('#title').val(data.document_title);
				$('#alias').val(data.document_alias);
				$('#show_doc').html('<a href="'+abs_path+data.document_alias+'" class="topDir link" target="_blank" title="">'+data.document_title+'</a> (ID: '+doc_id+')<span class="remove_link_doc icon_sprite ico_delete" style="float: right; cursor: pointer;" title="Убрать связь с документом"></span>');
			}
		});
	};

	AveAdmin.navItemSaveBtn = function(item_id)
	{
		$(".SaveButton").on('click', function(event){
			event.preventDefault();

			var form = $('#SaveItem');

			form.ajaxSubmit({
				url: form.attr('action'),
				type: 'POST',
				dataType: 'json',
				beforeSubmit: function() {
					$.alerts._overlay('show');
				},
				success: function(data){
					$.jGrowl(data['message'], {
						header: data['header'],
						theme: data['theme']
					});
						if (data['item_id'])
						{
							AveAdmin.navItemChange(data['item_id']);
						}
						$.alerts._overlay('hide');
						$('#ajax-dialog-item-'+item_id).dialog('destroy').remove();
				}
			});
			return false;
		});
	};

	AveAdmin.navItemSaveNew = function()
	{
		$(".SaveButton").on('click', function(event) {
			event.preventDefault();

			var form = $('#SaveItem');

			form.ajaxSubmit({
				url: form.attr('action'),
				type: 'POST',
				dataType: 'json',
				beforeSubmit: function() {
					$.alerts._overlay('show');
				},
				success: function(data) {
					$.jGrowl(data['message'], {
						header: data['header'],
						theme: data['theme']
					});
						if (data['item_id'] != '')
						{
							AveAdmin.navItemNew(data['item_id'], data['after']);
						}
						$.alerts._overlay('hide');
						$('#ajax-dialog-item-new').dialog('destroy').remove();
				}
			});
			return false;
		});
	};

	AveAdmin.navItemNew = function(item_id, after)
	{
		$.ajax({
			url: 'index.php?do=navigation&action=getitem&sub=new&navigation_item_id='+item_id+'&onlycontent=1',
			type: 'POST',
			beforeSend: function () {
				$.alerts._overlay('show');
			},
			success: function (data) {
				if (after)
				{
					$("#item-" + after).after('<li class="dd-item dd3-item" data-id="' + item_id + '" id="item-' + item_id + '"><div class="dd-handle dd3-handle"></div>' + data + '</li>');
				}
				else
				{
					$("ol.dd-list").html('<li class="dd-item dd3-item" data-id="' + item_id + '" id="item-' + item_id + '"><div class="dd-handle dd3-handle"></div>' + data + '</li>');
				}
				$.alerts._overlay('hide');
				$('a.openDialog').off();
				AveAdmin.modalDialog();
			}
		});
	};

	AveAdmin.navItemChange = function(item_id)
	{
		$.ajax({
			url: 'index.php?do=navigation&action=getitem&navigation_item_id='+item_id+'&onlycontent=1',
			type: 'POST',
			beforeSend: function () {
				$.alerts._overlay('show');
			},
			success: function (data) {
				$("#item-"+item_id + ' > .dd3-content').before(data).remove();
				$.alerts._overlay('hide');
				$('a.openDialog').off();
				AveAdmin.modalDialog();
			}
		});
	};

	$(document).on('click', '.changeStatus', function(event) {

		event.preventDefault();

		var item = $(this);

		$.ajax({
			url: item.attr('href'),
			type: 'POST',
			data: ({
				status: item.attr('data-status')
			}),
			dataType: ('json'),
			beforeSend: function () {
				$.alerts._overlay('show');
			},
			success: function (data) {
				item.toggleClass('ico_ok_green ico_delete_no').attr('data-status', data['status']);
				item.parent().parent().toggleClass('red');
				$.alerts._overlay('hide');
			}
		});
		return false;
	});

});

{/literal}

function openLinkWindowSelect(target, doc) {ldelim}
	if (typeof width == 'undefined' || width == '') var width = screen.width * 0.8;
	if (typeof height == 'undefined' || height == '') var height = screen.height * 0.6;
	if (typeof doc == 'undefined') var doc = 'title';
	if (typeof scrollbar == 'undefined') var scrollbar = 1;
	var left = ( screen.width - width ) / 2;
	var top = ( screen.height - height ) / 2;
	window.open('index.php?doc=' + doc + '&target=' + target + '&do=docs&action=showsimple&function=1&pop=1&cp=' + sess, 'pop', 'left=' + left + ', top=' + top + ', width=' + width + ', height=' + height + ', scrollbars=' + scrollbar + ', resizable=1');
{rdelim}
</script>