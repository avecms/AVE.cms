{if check_permission('documents')}

<script type="text/javascript" language="JavaScript">
function ChangeRazd() {ldelim}
	window.location.href='index.php?do=docs&action=change&Id={$smarty.request.Id|escape}&rubric_id={$smarty.request.rubric_id|escape}&NewRubr='+document.form1.NewRubr.value+'&pop=1&cp={$sess}';
{rdelim}
</script>

<div class="first"></div>

<div class="title"><h5>{#DOC_CHANGE_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
    <div class="body">
		{#DOC_CHANGE_INFO#}
    </div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
	    <ul>
	        <li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
	        <li>{#DOC_CHANGE_TITLE#}</li>
	    </ul>
	</div>
</div>

<form name="form1" action="{$formaction}" method="post" class="mainForm">

<div class="widget first">
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
			<tr>
				<td>
				<select name="NewRubr" size="1" onchange="ChangeRazd();" style="width: 370px">
					{foreach from=$rubrics item=rubric}
						{if $rubric->Show==1}
							<option value="{$rubric->Id}" {if ($smarty.request.NewRubr=='' && $smarty.request.rubric_id==$rubric->Id) || ($smarty.request.NewRubr!='' && $smarty.request.NewRubr==$rubric->Id)}selected{/if}>{$rubric->rubric_title|escape}</option>
						{/if}
					{/foreach}
				</select>
			</td>
		</tr>
	</table>
	<div class="fix"></div>
</div>

<div class="widget first">
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">

		<thead>
		<tr>
			<td>{#DOC_CHANGE_OLD_FIELD#}</td>
			<td>{#DOC_CHANGE_NEW_FIELD#}</td>
		</tr>
		</thead>
		
		{foreach from=$fields item=field key=Id}
			<tr>
				<td>{$field.title}</td>
				<td>
					{html_options name=$Id options=$field.Options selected=$field.Selected}
				</td>
			</tr>
		{/foreach}

		<tr>
			<td colspan="2">
				<input type="submit" name="submit" class="basicBtn" value="{#DOC_CHANGE_BUTTON#}" />
			</td>
		</tr>
	</table>

</div>
</form>

{/if}