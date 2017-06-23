<script language="javascript" type="text/javascript">
function Submit() {ldelim}
	window.opener.document.location.href='index.php?&do=docs&action=new&rubric_id='+ document.NewDoc.rubric_id.value +'&document_title='+ document.NewDoc.document_title.value +'&cp={$sess}';
	window.close();
{rdelim}
</script>
<div class="first"></div>

<div class="title"><h5>{#MAIN_ADD_IN_RUB#}</h5></div>

<div class="widget" style="margin-top: 0px;">
    <div class="body">
		{#DOC_ADD_NEW_LIGHT_TIP#}
    </div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
	    <ul>
			<li class="firstB"><a href="index.php?pop=1" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
	        <li>{#DOC_SUB_TITLE#}</li>
	        <li>{#DOC_ADD_NEW_LIGHT_ADD#}</li>
	    </ul>
	</div>
</div>

<div class="widget first">
	<div class="head"><h5>{#MAIN_ADD_IN_RUB#}</h5></div>
	<div style="display: block;">

<form class="mainForm" name="NewDoc" method="post" action="index.php" onsubmit="return Submit();">
	<input type="hidden" name="cp" value="{$sess}" />
	<input type="hidden" name="do" value="docs" />
	<input type="hidden" name="action" value="new" />
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<tr class="noborder">
			<td>{#DOC_NAME#}</td>
			<td>
				<div class="pr12"><input name="document_title" placeholder="{#DOC_TITLE#}" type="text" value="" /></div>
			</td>
		</tr>
		<tr>
			<td>{#DOC_CHOSE_RUB#}</td>
			<td>
				<select name="rubric_id" style="width:250px">
					{foreach from=$rubrics item=rubric}
						<option value="{$rubric->Id}" {if $smarty.request.rubric_id==$rubric->Id}selected{/if}>{$rubric->rubric_title|escape}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" class="basicBtn" value="{#DOC_ADD_NEW_LIGHT_BTN#}"></td>
		</tr>
	</table>
</form>

	</div>
</div>