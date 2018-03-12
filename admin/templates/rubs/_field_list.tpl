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
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=edit&Id={$smarty.request.rubric_id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_FIELDS#}</a>
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=fieldsgroups&Id={$smarty.request.rubric_id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_FGROUPS#}</a>
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=template&Id={$smarty.request.rubric_id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_TEMPLATES#}</a>
		</td>
		<td>
			{if check_permission('rubric_code')}
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=code&Id={$smarty.request.rubric_id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_CODE#}</a>
			{/if}
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=rules&Id={$smarty.request.rubric_id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_RULES#}</a>
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

	{foreach from=$rubrics item=rubric}
	<div class="widget">
		<div class="head">
			<h5>{$main.name} ({$main.id})</h5>
			<div class="num">
				<a class="basicNum" href="index.php?do=rubs&action=ftlist&Id={$smarty.request.rubric_id}&cp={$sess}">{#RUBRIK_FIELDS_TEMPLATES_BACK#}</a>
			</div>
		</div>
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
			<col width="10" />
			<col />
			<col width="100" />
			<col width="100" />
			<col width="100" />
			<col width="80" />
			<thead>
			<tr>
				<td colspan="1" rowspan="2">Id</td>
				<td colspan="1" rowspan="2">{#RUBRIK_FIELDS_TEMPLATES_FNAME#}</td>
				<td colspan="3" rowspan="1">{#RUBRIK_FIELDS_TEMPLATES_FTEMPL#}</td>
				<td colspan="1" rowspan="2">{#RUBRIK_FIELDS_TEMPLATES_DB#}</td>
			</tr>
			<tr>
				<td style="border-left: solid 1px #C7D6E6 !important;">{#RUBRIK_FIELDS_TEMPLATES_PANEL#}</td>
				<td>{#RUBRIK_FIELDS_TEMPLATES_DOC#}</td>
				<td>{#RUBRIK_FIELDS_TEMPLATES_REQ#}</td>
			</tr>
			</thead>
			<tbody>
			{foreach from=$rubric.fields item=field}
			<tr class="center">
				<td align="center">{$field.id}</td>
				<td><strong>{$field.title}</strong></td>
				<td align="center" id="adm_{$main.id}_{$field.id}">
				{if $field.adm_main}
					{if $field.adm_tpl}
					<a data-dialog="rft-{$field.id}" href="index.php?do=rubs&action=ftedit&rubric_id={$smarty.request.rubric_id}&id={$field.id}&fld={$main.id}&type=adm&cp={$sess}&pop=1&onlycontent=1" data-height="650" data-modal="true" class="openDialog">{#RUBRIC_TMPLS_EDIT#}</a>
						<br />
						<a href="index.php?do=rubs&action=ftdelete&rubric_id={$smarty.request.rubric_id}&id={$field.id}&fld={$main.id}&type=adm&cp={$sess}" class="link">{#RUBRIC_TMPLS_DELETE#}</a>
					{else}
						<a data-dialog="rft-{$field.id}" href="index.php?do=rubs&action=ftcreate&rubric_id={$smarty.request.rubric_id}&id={$field.id}&fld={$main.id}&amp;type=adm&cp={$sess}&pop=1&onlycontent=1" data-height="650" data-modal="true" class="openDialog">{#RUBRIC_TMPLS_CREAT#}</a>
					{/if}
				{else}
					<small>{#RUBRIK_FIELDS_NO_TEMPLATES#}</small>
				{/if}
				</td>
				<td align="center" id="doc_{$main.id}_{$field.id}">
				{if $field.doc_main}
					{if $field.doc_tpl}
						<a data-dialog="rft-{$field.id}" href="index.php?do=rubs&action=ftedit&rubric_id={$smarty.request.rubric_id}&id={$field.id}&fld={$main.id}&type=doc&cp={$sess}&pop=1&onlycontent=1" data-height="650" data-modal="true" class="openDialog">{#RUBRIC_TMPLS_EDIT#}</a>
						<br />
						<a href="index.php?do=rubs&action=ftdelete&rubric_id={$smarty.request.rubric_id}&id={$field.id}&fld={$main.id}&type=doc&cp={$sess}" class="link">{#RUBRIC_TMPLS_DELETE#}</a>
					{else}
						<a data-dialog="rft-{$field.id}" href="index.php?do=rubs&action=ftcreate&rubric_id={$smarty.request.rubric_id}&id={$field.id}&fld={$main.id}&amp;type=doc&cp={$sess}&pop=1&onlycontent=1" data-height="650" data-modal="true" class="openDialog">{#RUBRIC_TMPLS_CREAT#}</a>
					{/if}
				{else}
					<small>{#RUBRIK_FIELDS_NO_TEMPLATES#}</small>
				{/if}
				</td>
				<td align="center" id="req_{$main.id}_{$field.id}">
				{if $field.req_main}
					{if $field.req_tpl}
						<a data-dialog="rft-{$field.id}" href="index.php?do=rubs&action=ftedit&rubric_id={$smarty.request.rubric_id}&id={$field.id}&fld={$main.id}&type=req&cp={$sess}&pop=1&onlycontent=1" data-height="650" data-modal="true" class="openDialog">{#RUBRIC_TMPLS_EDIT#}</a>
						<br />
						<a href="index.php?do=rubs&action=ftdelete&rubric_id={$smarty.request.rubric_id}&id={$field.id}&fld={$main.id}&type=req&cp={$sess}" class="link">{#RUBRIC_TMPLS_DELETE#}</a>
					{else}
						<a data-dialog="rft-{$field.id}" href="index.php?do=rubs&action=ftcreate&rubric_id={$smarty.request.rubric_id}&id={$field.id}&fld={$main.id}&amp;type=req&cp={$sess}&pop=1&onlycontent=1" data-height="650" data-modal="true" class="openDialog">{#RUBRIC_TMPLS_CREAT#}</a>
					{/if}
				{else}
					<small>{#RUBRIK_FIELDS_NO_TEMPLATES#}</small>
				{/if}
				</td>
				<td align="center">
					<a data-dialog="rft-{$field.id}" title="{#RUBRIK_FILED_TEMPLATE_H#}" href="index.php?do=rubs&action=field_template&field_id={$field.id}&rubric_id={$rubric.rubric_id}&cp={$sess}&pop=1&onlycontent=1" data-height="700" data-modal="true" data-title="{#RUBRIK_FILED_TEMPLATE_H#}" class="openDialog icon_sprite ico_template topleftDir"></a>
				</td>
			</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	{/foreach}

{include file="$codemirror_connect"}