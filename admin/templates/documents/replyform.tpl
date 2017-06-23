<div class="widget first">
<div class="head"><h5>{#DOC_NEW_NOTICE_TITLE#}</h5></div>
	<div class="body">
		{#DOC_SEND_NOTICE_INFO#}
	</div>

<form method="post" action="{$formaction}" class="mainForm">
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<tr>
			<td width="135"><strong>{#DOC_NOTICE_TITLE#}</strong></td>
			<td>
				<div class="pr12"><input name="remark_title" type="text" id="remark_title" style="width:100%" value=""></div>
			</td>
		</tr>

		<tr>
			<td width="135"><strong>{#DOC_NOTICE_TEXT#}</strong></td>
			<td>
				<div class="pr12"><textarea name="remark_text" style="width:100%;height:100px" id="remark_text"></textarea></div>
			</td>
		</tr>

		<tr>
			<td colspan="2">
				<input type="submit" class="basicBtn" value="{#DOC_BUTTON_ADD_NOTICE#}" />
				<a href="index.php?do=docs&action=remark_del&Id={$smarty.request.Id|escape}&CId={$answer.Id}&remark_first={$answer.remark_first}&pop=1&cp={$sess}" class="btn redBtn floatright">{#DOC_NOTICE_DELETE_ALL#}</a>
			</td>
		</tr>
	</table>
</form>
</div>
