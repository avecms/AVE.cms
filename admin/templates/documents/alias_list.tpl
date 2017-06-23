<div class="title">
	<h5>{#DOC_ALIASES#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#DOC_ALIASES_TITLE#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB">
				<a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a>
			</li>
			<li>
				<a href="index.php?do=docs&cp={$sess}">{#DOC_SUB_TITLE#}</a>
			</li>
			<li>{#DOC_ALIASES#}</li>
		</ul>
	</div>
</div>

<div class="widget first">

	<div class="head">
		<h5 class="iFrames">{#DOC_ALIASES_DOC_LIST#}</h5>
	</div>

	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<col>
		<col width="180">
		<col width="120">
		<col width="100">
		<col width="20">
		<col width="20">
		<col width="20">
		<thead>
			<tr class="noborder">
				<td>{#DOC_ALIASES_LIST_NM#}</td>
				<td>{#DOC_ALIASES_LIST_RB#}</td>
				<td>{#DOC_ALIASES_LIST_CH#}</td>
				<td>{#DOC_ALIASES_LIST_CR#}</td>
				<td colspan="3">{#DOC_ALIASES_LIST_AT#}</td>
			</tr>
		</thead>
		<tbody>
			{if $documents}
			{foreach from=$documents item=document}
			<tr>
				<td>
					<a data-dialog="aliases-{$document.document_id}" href="index.php?do=docs&action=aliases_doc&doc_id={$document.document_id}&cp={$sess}&pop=1&onlycontent=1" data-height="650" data-modal="true" data-title="Редактировать" class="openDialog topDir link" title="Редактировать">{$document.document_title}</a>
					<br />
					<span class="code">url:</span>&nbsp;
					<a class="topDir" title="Перейти по ссылке" href="../{$document.document_alias}" target="_blank">
						<span class="dgrey doclink">{$document.document_alias}</span>
					</a>
				</td>
				<td align="center">
					{$document.rubric_title}
				</td>
				<td align="center">
					<span class="date_text dgrey">{$document.document_alias_changed|date_format:$DATE_FORMAT|pretty_date}</span>
				</td>
				<td align="center">
					<strong class="code">{$document.count}</strong>
				</td>
				<td align="center">
					<a data-dialog="aliases-{$document.document_id}" href="index.php?do=docs&action=aliases_doc&doc_id={$document.document_id}&cp={$sess}&pop=1&onlycontent=1" data-height="650" data-modal="true" data-title="Редактировать" class="openDialog icon_sprite ico_edit topleftDir"></a>
				</td>
				<td align="center">
					<a class="topleftDir icon_sprite ico_copy" title="Перейти к документу" href="index.php?do=docs&action=edit&rubric_id={$document.rubric_id}&Id={$document.document_id}&cp={$sess}" target="_blank"></a>
				</td>
				<td align="center">
					<a href="../{$document.document_alias}" title="Перейти по ссылке" class="icon_sprite ico_globus topleftDir" target="_blank"></a>
				</td>
			</tr>
			{/foreach}
			{else}
			<tr>
				<td colspan="7">
					<ul class="messages">
						<li class="highlight yellow">{#DOC_ALIASES_LIST_EMPT#}</li>
					</ul>
				</td>
			</tr>
			{/if}
		</tbody>
	</table>

</div>