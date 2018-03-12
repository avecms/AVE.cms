<script language="Javascript" type="text/javascript">
var sess = '{$sess}';
</script>

<div class="title">
	<h5>{#RUBRIK_FIELDS_TEMPLATES_H2#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		<ul style="list-style: square; margin-left:15px;">
			<li>{#RUBRIK_FIELDS_TEMPLATES_T1#}</li>
			<li>{#RUBRIK_FIELDS_TEMPLATES_T2#}</li>
		</ul>
	</div>
</div>

<table class="first tableButtons" cellpadding="0" cellspacing="0" width="100%" id="rubricButtons">
	<col width="20%">
	<col width="20%">
	<col width="20%">
	<col width="20%">
	<col width="20%">
	<tr>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=edit&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_FIELDS#}</a>
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=fieldsgroups&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_FGROUPS#}</a>
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=template&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_TEMPLATES#}</a>
		</td>
		<td>
			{if check_permission('rubric_code')}
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=code&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_CODE#}</a>
			{/if}
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=rules&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_RULES#}</a>
		</td>
	</tr>
</table>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB">
				<a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a>
			</li>
			<li>
				<a href="index.php?do=rubs&cp={$sess}">{#RUBRIK_SUB_TITLE#}</a>
			</li>
			<li>{#RUBRIK_FIELDS_TEMPLATES_H1#}</li>
			<li><strong class="code">{$rubric->rubric_title}</strong></li>
		</ul>
	</div>
</div>

<div class="widget">
	<div class="head">
		<h5>{#RUBRIK_FIELDS_TEMPLATES_LIST#}</h5>
	</div>
<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">

	<col />
	<col width="100" />
	<col width="100" />
	<col width="100" />
	<col width="80" />
	<col width="100" />
	<col width="80" />
	<thead>
		<tr>

		</tr>

			<tr>
				<td colspan="1" rowspan="2">{#RUBRIK_FIELDS_TEMPLATES_FNAME#}</td>
				<td colspan="1" rowspan="2">{#RUBRIK_FIELDS_TEMPLATES_FFUNC#}</td>
				<td colspan="3" rowspan="1">{#RUBRIK_FIELDS_TEMPLATES_FTEMP#}</td>
			</tr>
			<tr>
				<td style="border-left: solid 1px #C7D6E6 !important;">{#RUBRIK_FIELDS_TEMPLATES_PANEL#}</td>
				<td>{#RUBRIK_FIELDS_TEMPLATES_DOC#}</td>
				<td>{#RUBRIK_FIELDS_TEMPLATES_REQ#}</td>
			</tr>

	</thead>
	<tbody>
	{foreach from=$fields item=field key=number}
		{if in_array($field.id, $enable)}
		<tr>
			<td>
				<strong><a class="link" href="index.php?do=rubs&action=ftshowfield&rubric_id={$smarty.request.Id}&type={$field.id}&cp={$sess}">{$field.name}</a></strong>
			</td>

			<td class="date_text dgrey" align="center">
				{$field.id}
			</td>

			<td align="center">
				{foreach  from=$exists item=exist key=key}
					{if $field.id == $key}
						{if $exist.adm}
						<a data-dialog="rft-{$field.id}" href="index.php?do=rubs&action=ftempledit&rubric_id={$smarty.request.Id}&fld={$field.id}&type=adm&cp={$sess}&pop=1&onlycontent=1" data-height="650" data-modal="true" class="openDialog">{#RUBRIC_TMPLS_EDIT#}</a>
						{/if}
					{/if}
				{/foreach}
			</td>
			<td align="center">
				{foreach  from=$exists item=exist key=key}
					{if $field.id == $key}
						{if $exist.doc}
						<a data-dialog="rft-{$field.id}" href="index.php?do=rubs&action=ftempledit&rubric_id={$smarty.request.Id}&fld={$field.id}&type=doc&cp={$sess}&pop=1&onlycontent=1" data-height="650" data-modal="true" class="openDialog">{#RUBRIC_TMPLS_EDIT#}</a>
						{/if}
					{/if}
				{/foreach}
			</td>
			<td align="center">
				{foreach  from=$exists item=exist key=key}
					{if $field.id == $key}
						{if $exist.req}
						<a data-dialog="rft-{$field.id}" href="index.php?do=rubs&action=ftempledit&rubric_id={$smarty.request.Id}&fld={$field.id}&type=req&cp={$sess}&pop=1&onlycontent=1" data-height="650" data-modal="true" class="openDialog">{#RUBRIC_TMPLS_EDIT#}</a>
						{/if}
					{/if}
				{/foreach}
			</td>
		</tr>
		{/if}
	{/foreach}
	</tbody>
</table>
</div>

{include file="$codemirror_connect"}