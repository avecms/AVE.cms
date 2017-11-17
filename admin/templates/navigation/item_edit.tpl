<form name="saveitem" id="SaveItem" method="post" action="index.php?do=navigation&action=itemedit&cp={$sess}" class="mainForm">
	<div class="widget" style="margin-top: 0px;">
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">

			<col width="50%"/>
			<col width="50%"/>

			<input type="hidden" name="document_id" id="document_id" value="{$item->document_id}" />

			<thead>
			<tr class="noborder">
				<td colspan="2">{#NAVI_LINK_TITLE#}</td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td colspan="2">
					<div class="pr12">
						<input name="title" type="text" id="title" value="{$item->title}" autocomplete="off" />
					</div>
				</td>
			</tr>
			</tbody>

			<thead>
			<tr class="noborder">
				<td colspan="2">{#NAVI_LINK_TO_DOCUMENT#}</td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td colspan="2">
					<div class="pr12">
						<input name="alias" type="text" id="alias" value="{$item->alias}" autocomplete="off" />
					</div>
				</td>
			</tr>
			</tbody>

			<thead>
				<tr class="noborder">
					<td colspan="2">{#NAVI_LINKED_DOC#}</td>
				</tr>
			</thead>
			<tbody>
				<tr class="yellow">
					<td colspan="2" id="show_doc" style="text-align: center;">
						{if $item->document_id}
						<a href="{$ABS_PATH}{$item->document_alias}" class="topDir link" title="" target="_blank">{$item->document_title|escape}</a> (ID: {$item->document_id|escape})<span class="remove_link_doc topDir icon_sprite ico_delete" style="float: right; cursor: pointer;" title="Убрать связь с документом"></span>
						{else}
						{#NAVI_NO_LINK#}
						{/if}
					</td>
				</tr>
			</tbody>

			<thead>
			<tr class="noborder">
				<td colspan="2">{#NAVI_LINK_FILEDOC#}</td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td colspan="2">
					<div class="pr12" style="text-align: center;">
					<input title="{#NAVI_BROWSE_DOCUMENTS#}" onclick="openLinkWindowSelect('');" type="button" class="basicBtn greenBtn topDir" value="{#NAVI_LINK_DOC#}" />
					&nbsp;
					<input title="{#NAVI_BROWSE_MEDIAPOOL#}" onclick="openFileWindow('alias','alias','alias');" type="button" class="basicBtn topDir" value="{#NAVI_LINK_FILE#}" />
					</div>
				</td>
			</tr>
			</tbody>

			<thead>
			<tr class="noborder">
				<td colspan="2">{#NAVI_LINK_SOLUT#}</td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td colspan="2">
					<div class="pr12">
						<textarea rows="3" cols="10" name="description">{$item->description}</textarea>
					</div>
				</td>
			</tr>
			</tbody>

			<thead>
			<tr class="noborder">
				<td colspan="2">{#NAVI_LINK_IMAGE#}</td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td colspan="2">
					<div class="pr12">
						<input name="image" type="text" id="image" value="{$item->image}" style="width: 280px;" autocomplete="off" />&nbsp;<input value="{#NAVI_BUTTON_CHANGE#}" title="{#NAVI_LINK_IMGTL#}" class="basicBtn topDir" onclick="openFileWindow('image','image');" type="button">
					</div>
				</td>
			</tr>
			</tbody>

			<thead>
			<tr class="noborder">
				<td colspan="2">STYLE</td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td colspan="2">
					<div class="pr12">
						<input name="css_style" type="text" id="css_style" value="{$item->css_style|escape}" autocomplete="off" />
					</div>
				</td>
			</tr>
			</tbody>

			<thead>
			<tr class="noborder">
				<td>CLASS</td>
				<td>ID</td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>
					<div class="pr12">
						<input name="css_class" type="text" id="class" value="{$item->css_class}" />
					</div>
				</td>
				<td>
					<div class="pr12">
						<input name="css_id" type="text" id="css_id" value="{$item->css_id}" />
					</div>
				</td>
			</tr>
			</tbody>

			<thead>
			<tr class="noborder">
				<td colspan="2">{#NAVI_TARGET_WINDOW#}</td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td colspan="2">
					<select name="target" id="target" style="width: 100%;">
						<option value="_self" {if $item->target == '_self'}selected="selected"{/if}>{#NAVI_OPEN_IN_THIS#}</option>
						<option value="_blank" {if $item->target == '_blank'}selected="selected"{/if}>{#NAVI_OPEN_IN_NEW#}</option>
					</select>
				</td>
			</tr>
			</tbody>

		</table>
	</div>

	<div class="widget first" style="margin-bottom:10px">
		<div class="rowElem" id="saveBtn">
			<div class="saveBtn">
				<input type="submit" class="basicBtn SaveButton" value="{#NAVI_BUTTON_SAVE#}" />
				{if $smarty.request.sub == 'edit'}
					<input type="hidden" name="navigation_item_id" value="{$item->navigation_item_id}" />
					<input type="hidden" name="sub" value="save" />
				{/if}
				{if $smarty.request.pop}
				    <input onclick="javascript:void(0);" type="button" class="redBtn Close" value="{#NAVI_CLOSE#}" />
				{/if}
			</div>
		</div>
	</div>
</form>
<script language="javascript">
$(document).ready(function(){ldelim}
AveAdmin.navItemSaveBtn({$item->navigation_item_id});
	$(document).on('click', '.remove_link_doc', function(event)
		{ldelim}
			event.preventDefault();
			$('#document_id').val('');
			$('#show_doc').html('{#NAVI_NOLINK_DOC#}');
			$(".tipsy").remove();
		{rdelim}
	);
	$(document).on('click', '.Close', function(event){ldelim}
		    event.preventDefault();
		    $('#ajax-dialog-item-{$smarty.request.navigation_item_id|escape}').dialog('destroy').remove();
		    return false;
	{rdelim});
{rdelim});
</script>