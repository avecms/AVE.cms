<script language="javascript">
$(document).ready(function(){ldelim}

	$(".ConfirmLogClear").click(function(e){ldelim}
		e.preventDefault();
		var href = $(this).attr('href');
		var title = '{#LOGS_BUTTON_DELETE#}';
		var confirm = '{#LOGS_DELETE_CONFIRM#}';
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

{rdelim});

</script>


<div class="title"><h5>{#LOGS_SUB_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#LOGS_TIP#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li>{#LOGS_SUB_TITLE#}</li>
		</ul>
	</div>
</div>

<div class="widget first">
	<ul class="inact_tabs">
		<li class="activeTab"><a href="index.php?do=logs&cp={$sess}">{#LOGS_TITLE#}</a></li>
		<li><a href="index.php?do=logs&action=log404&cp={$sess}">{#LOGS_404_SUB_TITLE#}</a></li>
		<li><a href="index.php?do=logs&action=logsql&cp={$sess}">{#LOGS_SQL_SUB_TITLE#}</a></li>
	</ul>
	<table cellpadding="0" cellspacing="0" width="100%" class="display" id="dinamTable">
		<col width="2%">
		<col width="8%">
		<col width="10%">
		<col width="10%">
		<col width="70%">
		<thead>
			<tr>
				<th width="5%">{#LOGS_ID#}</th>
				<th width="10%">{#LOGS_IP#}</th>
				<th width="10%">{#LOGS_USER#}</th>
				<th width="15%">{#LOGS_DATE#}</th>
				<th width="70%">{#LOGS_ACTION#}</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$logs key=k item=log}
			<tr class="gradeA">
				<td align="center">{$k}</td>
				<td align="center">{$log.log_ip}</td>
				<td align="center">{if check_permission('user_edit')}<a href="index.php?do=user&action=edit&Id={$log.log_user_id}&cp={$sess}">{$log.log_user_name}</a>{else}{$log.log_user_name}{/if}</td>
				<td align="center"><span class="date_text dgrey">{$log.log_time|date_format:$TIME_FORMAT|pretty_date}</span></td>
				<td>
					{$log.log_text}
					<div class="clear"></div>
					<span class="code">url:</span> <span class="date_text dgrey">{$log.log_url}</span>
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<div class="body aligncenter">
		{if check_permission('logs_clear')}
		<input href="index.php?do=logs&action=delete&cp={$sess}" type="button" class="basicBtn ConfirmLogClear" value="{#LOGS_BUTTON_DELETE#}" />
		{/if}
		<input onclick="location.href='index.php?do=logs&action=export&cp={$sess}'" class="redBtn" type="button" value="{#LOGS_BUTTON_EXPORT#}" />
	</div>
</div>