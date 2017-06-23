<div class="title"><h5>{#DOC_AFTER_CREATE_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#DOC_AFTER_CREATE_INFO#}
	</div>
</div>

<ul id="doclinks" style="padding-left:70px">
	<li><span class="icon_sprite ico_edit floatleft"></span>&nbsp;<a href="index.php?do=docs&action=edit&Id={$document_id}&cp={$sess}">{#DOC_EDIT_THIS_DOCUMENT#}</a></li>
	<li><span class="icon_sprite ico_look floatleft"></span>&nbsp;<a href="../{if $document_id!=1}index.php?id={$document_id}&cp={$sess}{/if}" target="_blank">{#DOC_DISPLAY_NEW_WINDOW#}</a><br /><br /></li>
	{if $innavi}
		<li class="navig"><span class="icon_sprite ico_navigation floatleft"></span><a href="javascript:void(0);">{#DOC_INCLUDE_NAVIGATION#}</a><br /><br /></li>
	{/if}
	<li><span class="icon_sprite ico_add floatleft"></span>&nbsp;<a href="index.php?do=docs&action=copy&rubric_id={$rubric_id}&Id={$document_id}&cp={$sess}">{#DOC_ADD_COPY_DOCUMENT#}</a><br /></li>
	<li><span class="icon_sprite ico_add floatleft"></span>&nbsp;<a href="index.php?do=docs&action=new&rubric_id={$rubric_id}&cp={$sess}">{#DOC_ADD_NEW_DOCUMENT#}</a><br /><br /></li>
	<li><span class="icon_sprite ico_copy floatleft"></span>&nbsp;<a href="index.php?do=docs&rubric_id={$rubric_id}&cp={$sess}">{#DOC_CLOSE_WINDOW_RUBRIC#}</a></li>
	<li><span class="icon_sprite ico_copy floatleft"></span>&nbsp;<a href="index.php?do=docs&cp={$sess}">{#DOC_CLOSE_WINDOW#}</a></li>
</ul>

{if $innavi}
<form method="post" action="index.php" class="mainForm">
	<div id="addInNav" class="first" style="display: none;">

		<input type="hidden" name="do" value="docs">
		<input type="hidden" name="action" value="innavi">
		<input type="hidden" name="document_id" value="{$document_id}">
		<input type="hidden" name="rubric_id" value="{$rubric_id}">
		<input type="hidden" name="cp" value="{$sess}">

		<div class="widget first">
			<div class="head">
				<h5 class="iFrames">{#DOC_TO_NAVI_TITLE#}</h5>
			</div>

			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic" id="Fields">
				<col width"200"></col>
				<col></col>
				<tr>
					<td>{#DOC_ADD_IN_NAVIGATION#}</td>
					<td nowrap>
						{include file='navigation/tree_docform.tpl'}
						<span style="margin: 0 10px">{#DOC_IN_MENU#} -></span>
						<select name="navi_id" style="width: 250px">
							{foreach from=$navigations item=menu}
								<option value="{$menu->navigation_id}">{$menu->title|escape}</option>
							{/foreach}
						</select>
					</td>
				</tr>

				<tr>
					<td>{#DOC_NAVIGATION_POSITION#}</td>
					<td><input style="width:45px" name="navi_item_position" type="text" value="10" maxlength="4"></td>
				</tr>

				<tr>
					<td>{#DOC_NAVIGATION_TITLE#}</td>
					<td><div class="pr12"><input name="navi_title" type="text" value="{$document_title|escape}"></div></td>
				</tr>

				<tr>
					<td>{#DOC_TARGET#}</td>
					<td>
						<select style="width: 150px" name="navi_item_target">
							<option value="_self">{#DOC_TARGET_SELF#}</option>
							<option value="_blank">{#DOC_TARGET_BLANK#}</option>
						</select>
					</td>
				</tr>

				<tr>
					<td colspan="2"><input type="submit" class="basicBtn" value="{#DOC_BUTTON_ADD_MENU#}"></td>
				</tr>
			</table>
		</div>

	</div>
</form>
{/if}

{literal}
<script language="Javascript" type="text/javascript">
$(document).ready(function(){

	$('.navig a').on('click', function(){
		$('#addInNav').toggle();
		$(".mainForm select").trigger('refresh');
	});

});
</script>
{/literal}