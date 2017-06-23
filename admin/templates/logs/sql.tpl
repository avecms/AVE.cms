<script language="javascript">
$(document).ready(function(){ldelim}

	$(".ConfirmLogClear").click(function(event){ldelim}
		event.preventDefault();
		var href = $(this).attr('href');
		var title = '{#LOGS_SQL_BUTTON_DELETE#}';
		var confirm = '{#LOGS_SQL_DELETE_CONFIRM#}';
		jConfirm(
				confirm,
				title,
				function(b){ldelim}
					if (b){ldelim}
						window.location = href;
					{rdelim}
				{rdelim}
			);
	{rdelim});

	$(".show_full").click(function(event) {ldelim}
		event.preventDefault();
		$(this).next('.full_error').slideToggle();
	{rdelim});

{rdelim});

</script>


<div class="title"><h5>{#LOGS_SQL_SUB_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#LOGS_SQL_TIP#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=logs&amp;cp={$sess}">{#LOGS_SUB_TITLE#}</a></li>
			<li>{#LOGS_SQL_SUB_TITLE#}</li>
		</ul>
	</div>
</div>

<div class="widget first">
	<ul class="inact_tabs">
		<li><a href="index.php?do=logs&cp={$sess}">{#LOGS_TITLE#}</a></li>
		<li><a href="index.php?do=logs&action=log404&cp={$sess}">{#LOGS_404_SUB_TITLE#}</a></li>
		<li class="activeTab"><a href="index.php?do=logs&action=logsql&cp={$sess}">{#LOGS_SQL_SUB_TITLE#}</a></li>
	</ul>
	<table cellpadding="0" cellspacing="0" width="100%" class="display" id="dinamTable">
		<col width="5%">
		<col width="10%">
		<col width="15%">
		<col width="70%">
		<thead>
			<tr>
				<th width="5%">{#LOGS_SQL_ID#}</th>
				<th width="10%">{#LOGS_SQL_IP#}</th>
				<th width="15%">{#LOGS_SQL_DATE#}</th>
				<th width="70%">{#LOGS_SQL_ACTION#}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$logs key=key item=log}
			<tr class="gradeA">
				<td align="center">{$key}</td>
				<td align="center">{$log.log_ip}</td>
				<td align="center">
					<span class="date_text dgrey">{$log.log_time|date_format:$TIME_FORMAT|pretty_date}</span>
					<br/>
					<span class="date_text dgrey">{$log.log_user_name}</span>
				</td>
				<td>
					<strong class="code_red">ERROR:</strong>
					{$log.log_text.sql_error}
					<br />
					<a class="show_full" href="javascript:void(0);">Полная информация об ошибке</a>

					<div class="full_error" style="display: none;">
						<br />
						<strong class="code">SQL Query:</strong>
						<pre class="code">
						{$log.log_text.sql_query}
						</pre>
						{foreach from=$log.log_text.caller item=call}
							<strong class="code">Файл:</strong> {$call.call_file}
							<br />
							<strong class="code">Функция:</strong> {$call.call_func}
							<br />
							<strong class="code">Строка:</strong> {$call.call_line}
						{/foreach}
						<br />
						<strong class="code">Url:</strong> {$log.log_text.url}
					</div>
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>

	<div class="body aligncenter">
		{if check_permission('logs_clear')}
			<input href="index.php?do=logs&action=deletesql&cp={$sess}" type="button" class="basicBtn ConfirmLogClear" value="{#LOGS_SQL_BUTTON_DELETE#}" />
		{/if}
			<input onclick="location.href='index.php?do=logs&action=exportsql&cp={$sess}'" class="redBtn" type="button" value="{#LOGS_SQL_BUTTON_EXPORT#}" />
	</div>
</div>