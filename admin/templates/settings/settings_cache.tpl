<script type="text/javascript">
	$sess = '{$sess}';
</script>

<div class="title">
	<h5>{#SETTINGS_CACHE_TITLE#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#SETTINGS_SAVE_INFO#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=settings&cp={$sess}">{#SETTINGS_MAIN_TITLE#}</a></li>
			<li>{#SETTINGS_CACHE_TITLE#}</li>
		</ul>
	</div>
</div>

<div class="widget first">
	<div class="body">
		{if check_permission('cache_clear')}<a class="button redBtn clearCacheSess" href="javascript:void(0);">{#MAIN_STAT_CLEAR_CACHE_FULL#}</a>&nbsp;{/if}
		{if check_permission('cache_thumb')}<a class="button redBtn clearThumb" href="javascript:void(0);">{#MAIN_STAT_CLEAR_THUMB#}</a>&nbsp;{/if}
		{if check_permission('document_revisions')}<a class="button redBtn clearRev" href="javascript:void(0);">{#MAIN_STAT_CLEAR_REV#}</a>&nbsp;{/if}
		{if check_permission('gen_settings')}<a class="button redBtn clearCount" href="javascript:void(0);">{#MAIN_STAT_CLEAR_COUNT#}</a>&nbsp;{/if}
		{if check_permission('gen_settings_robots')}<a data-dialog="robots" data-title="{#SETTINGS_FILE_ROBOTS#}" data-height="650" data-modal="true" class="button greenBtn openDialog" href="index.php?do=settings&action=robots&cp={$sess}">{#SETTINGS_FILE_ROBOTS#}</a>&nbsp;{/if}
		{if check_permission('gen_settings_fcustom')}<a data-dialog="custom" data-title="{#SETTINGS_FILE_CUSTOM#}" data-height="650" data-modal="true" class="button greenBtn openDialog" href="index.php?do=settings&action=custom&cp={$sess}">{#SETTINGS_FILE_CUSTOM#}</a>{/if}
	</div>
</div>

<div class="widget first">

	<ul class="inact_tabs">
        {if check_permission('gen_settings')}<li><a href="index.php?do=settings&cp={$sess}">{#SETTINGS_MAIN_SETTINGS#}</a></li>{/if}
        {if check_permission('gen_settings_more')}<li><a href="index.php?do=settings&sub=case&cp={$sess}">{#SETTINGS_CASE_TITLE#}</a></li>{/if}
        {if check_permission('gen_settings_countries')}<li><a href="index.php?do=settings&sub=countries&cp={$sess}">{#MAIN_COUNTRY_EDIT#}</a></li>{/if}
        {if check_permission('gen_settings_languages')}<li><a href="index.php?do=settings&sub=language&cp={$sess}">{#SETTINGS_LANG_EDIT#}</a></li>{/if}
		<li><a href="index.php?do=settings&action=paginations&cp={$sess}">{#SETTINGS_PAGINATION#}</a></li>
		<li class="activeTab"><a href="index.php?do=settings&action=showcache&cp={$sess}">{#SETTINGS_SHOWCACHE#}</a></li>
	</ul>

	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<col />
		<col width="150" />
		<col width="100" />

		<thead>
			<tr>
				<td>{#SETTINGS_CACHE_H_TITLE#}</td>
				<td>{#SETTINGS_CACHE_H_SHOW#}</td>
				<td>{#SETTINGS_CACHE_CLEAR#}</td>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td>
					{#SETTINGS_CACHE_T_SMARTY#}
				</td>
				<td align="center">
					<strong class="code" id="btn-smarty"><a href="javascript:void(0);" class="link btn-show" data-source="smarty">{#SETTINGS_CACHE_SHOW#}</a></strong>
				</td>
				<td align="center">
					<a href="javascript:void(0);" class="btn redBtn btn-clear" data-source="smarty">{#SETTINGS_CACHE_CLEAR#}</a>
				</td>
			</tr>
			<tr>
				<td>
					{#SETTINGS_CACHE_T_DOCS#}
				</td>
				<td align="center">
					<strong class="code" id="btn-documents"><a href="javascript:void(0);" class="link btn-show" data-source="documents">{#SETTINGS_CACHE_SHOW#}</a></strong>
				</td>
				<td align="center">
					<a href="javascript:void(0);" class="btn redBtn btn-clear" data-source="documents">{#SETTINGS_CACHE_CLEAR#}</a>
				</td>
			</tr>
			<tr>
				<td>
					{#SETTINGS_CACHE_T_QUERIES#}
				</td>
				<td align="center">
					<strong class="code" id="btn-requests"><a href="javascript:void(0);" class="link btn-show" data-source="requests">{#SETTINGS_CACHE_SHOW#}</a></strong>
				</td>
				<td align="center">
					<a href="javascript:void(0);" class="btn redBtn btn-clear" data-source="requests">{#SETTINGS_CACHE_CLEAR#}</a>
				</td>
			</tr>
			<tr>
				<td>
					{#SETTINGS_CACHE_T_MODULES#}
				</td>
				<td align="center">
					<strong class="code" id="btn-modules"><a href="javascript:void(0);" class="link btn-show" data-source="modules">{#SETTINGS_CACHE_SHOW#}</a></strong>
				</td>
				<td align="center">
					<a href="javascript:void(0);" class="btn redBtn btn-clear" data-source="modules">{#SETTINGS_CACHE_CLEAR#}</a>
				</td>
			</tr>
			<tr>
				<td>
					{#SETTINGS_CACHE_T_NAVI#}
				</td>
				<td align="center">
					<strong class="code" id="btn-navigations"><a href="javascript:void(0);" class="link btn-show" data-source="navigations">{#SETTINGS_CACHE_SHOW#}</a></strong>
				</td>
				<td align="center">
					<a href="javascript:void(0);" class="btn redBtn btn-clear" data-source="navigations">{#SETTINGS_CACHE_CLEAR#}</a>
				</td>
			</tr>
			<tr>
				<td>
					{#SETTINGS_CACHE_T_RUBRICS#}
				</td>
				<td align="center">
					<strong class="code" id="btn-rubrics"><a href="javascript:void(0);" class="link btn-show" data-source="rubrics">{#SETTINGS_CACHE_SHOW#}</a></strong>
				</td>
				<td align="center">
					<a href="javascript:void(0);" class="btn redBtn btn-clear" data-source="rubrics">{#SETTINGS_CACHE_CLEAR#}</a>
				</td>
			</tr>
			<tr>
				<td>
					{#SETTINGS_CACHE_T_PAGINATION#}
				</td>
				<td align="center">
					<strong class="code" id="btn-paginations"><a href="javascript:void(0);" class="link btn-show" data-source="paginations">{#SETTINGS_CACHE_SHOW#}</a></strong>
				</td>
				<td align="center">
					<a href="javascript:void(0);" class="btn redBtn btn-clear" data-source="paginations">{#SETTINGS_CACHE_CLEAR#}</a>
				</td>
			</tr>
			<tr>
				<td>
					{#SETTINGS_CACHE_T_SESSIONS#}
				</td>
				<td align="center">
					<strong class="code" id="btn-sessions"><a href="javascript:void(0);" class="link btn-show" data-source="sessions">{#SETTINGS_CACHE_SHOW#}</a></strong>
				</td>
				<td align="center">
					<a href="javascript:void(0);" class="btn redBtn btn-clear" data-source="sessions">{#SETTINGS_CACHE_CLEAR#}</a>
				</td>
			</tr>
			<tr>
				<td>
					{#SETTINGS_CACHE_T_BLOCKS#}
				</td>
				<td align="center">
					<strong class="code" id="btn-block"><a href="javascript:void(0);" class="link btn-show" data-source="block">{#SETTINGS_CACHE_SHOW#}</a></strong>
				</td>
				<td align="center">
					<a href="javascript:void(0);" class="btn redBtn btn-clear" data-source="block">{#SETTINGS_CACHE_CLEAR#}</a>
				</td>
			</tr>
			<tr>
				<td>
					{#SETTINGS_CACHE_T_SYSBLOKS#}
				</td>
				<td align="center">
					<strong class="code" id="btn-sysblocks"><a href="javascript:void(0);" class="link btn-show" data-source="sysblocks">{#SETTINGS_CACHE_SHOW#}</a></strong>
				</td>
				<td align="center">
					<a href="javascript:void(0);" class="btn redBtn btn-clear" data-source="sysblocks">{#SETTINGS_CACHE_CLEAR#}</a>
				</td>
			</tr>
			<tr>
				<td>
					{#SETTINGS_CACHE_T_SETTINGS#}
				</td>
				<td align="center">
					<strong class="code" id="btn-settings"><a href="javascript:void(0);" class="link btn-show" data-source="settings">{#SETTINGS_CACHE_SHOW#}</a></strong>
				</td>
				<td align="center">
					<a href="javascript:void(0);" class="btn redBtn btn-clear" data-source="settings">{#SETTINGS_CACHE_CLEAR#}</a>
				</td>
			</tr>
		</tbody>
	</table>
</div>


{literal}
<script language="javascript">

	$(document).ready(function() {

		$('.btn-show').on('click', function (){

			var button = $(this);

			var source = button.data('source');

			$.ajax({
				url: 'index.php?do=settings&action=showsize&cp=' + $sess,
				data: {
					source: source
				},
				dataType: 'JSON',
				beforeSend: function() {
					// Действие перед отправкой
					$.alerts._overlay('show');
				},
				complete: function() {
					// Действие по окнчанию
					$.alerts._overlay('hide');
				},
				success: function (data) {
					if (data) {
						$('#btn-' + source).html(data['size']);
					}
				}
			});
		});

		$('.btn-clear').on('click', function (){

			var button = $(this);

			var source = button.data('source');

			$.ajax({
				url: 'index.php?do=settings&action=cacheclear&cp=' + $sess,
				data: {
					source: source
				},
				dataType: 'JSON',
				beforeSend: function() {
					// Действие перед отправкой
					$.alerts._overlay('show');
				},
				complete: function() {
					// Действие по окнчанию
					$.alerts._overlay('hide');
				},
				success: function (data) {
					if (data) {
						$.jGrowl(data.message, {header: data.header, theme: data.theme});
						$('#btn-' + source).html(data['size']);
					}
				}
			});
		});

	});

</script>
{/literal}
