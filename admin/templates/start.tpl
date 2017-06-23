<div class="title">
	<h5>{#MAIN_WELCOME#}</h5>
</div>

<div class="widgets">
		{if $smarty.const.CHECK_VERSION}
		<ul class="messages first">
			<li class="highlight yellow hidden" id="update">

			<script type="text/javascript">
			$(document).ready(function(){ldelim}
				$.ajax({ldelim}
					url: 'http://ave-cms.ru/version.php?jsoncallback=?',
					dataType: "jsonp",
					success: function (data) {ldelim}
						var current_version = {$smarty.const.APP_VERSION};
						var stable_version = data.version;
						var newstext = data.newstext;
						if (current_version < stable_version) {ldelim}
							$("#update").removeClass("hidden").html(newstext);
						{rdelim}
					{rdelim}
				{rdelim});
			{rdelim});
			</script> 

			</li>
		</ul>
		{/if}
		{if $login_menu && $online_users > "1"}
		<ul class="messages first">
			<li class="highlight grey">{#MAIN_USERS_LAST_TIME#} 
			  {foreach from=$online_users item=item name=online_users}
				<a href="index.php?do=user&action=edit&Id={$item->Id}" class="topDir link" title="{$item->user_group_name}">{if $item->user_group == "1"}<strong>{$item->user_name}</strong>{else}{$item->user_name}{/if}</a>{if !$smarty.foreach.online_users.last}, {/if}
			  {/foreach}
			</li>
		</ul>
		{/if}

		<div class="widget">
			<div class="head">
				<h5>{#MAIN_START_DOC_TITLE#}</h5>
			</div>
			<div class="dataTables_wrapper" id="example_wrapper">
			<div class="">
				<div class="dataTables_filter" id="example_filter">
				<form method="get" id="doc_search" action="index.php">
					<input type="hidden" name="do" value="docs" />
					<input type="hidden" name="rubric_id" value="all" />
				<label>{#MAIN_START_SEARCH#} <input type="text" placeholder="{#MAIN_START_SEARCH_T#} " name="QueryTitel" style="width: 350px;"><div class="srch"></div></label>
				</form>
				</div>
			</div>
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col width="10" />
				<col />
				<col width="200" />
				<col width="150" />
				<thead>
					<tr>
						<td>{#MAIN_START_DOC_ID#}</td>
						<td>{#MAIN_START_DOC_NAME#}</td>
						<td>{#MAIN_START_DOC_RUBRIC#}</td>
						<td>{#MAIN_START_DOC_DATE#}</td>
					</tr>
				</thead>
				{foreach from=$doc_start item=item}
					<tr {if $item->document_deleted==1}class="red"{/if}{if $item->document_status!=1}class="yellow"{/if}>
						<td nowrap="nowrap"><strong><a class="toprightDir" title="{#MAIN_DOC_SHOW_TITLE#}" href="../{if $item->Id!=1}index.php?id={$item->Id}&cp={$sess}{/if}" target="_blank">{$item->Id}</a></strong></td>
						<td>
							<div  class="docaction">
							{if $item->cantEdit==1}
								{if $item->rubric_admin_teaser_template != ""}
									{$item->rubric_admin_teaser_template}
								{else}
									<strong>
									<a class="docname topDir" title="{#MAIN_DOC_EDIT_TITLE#}" href="index.php?do=docs&action=edit&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}">
										{if $item->document_breadcrum_title != ""}{$item->document_breadcrum_title|stripslashes|escape}{elseif $item->document_title != ""}{$item->document_title|stripslashes|escape}{else}{#MAIN_DOC_SHOW3_TITLE#}{/if}
									</a>
									</strong>
									<br />
									<a href="../{if $item->Id!=1}{$item->document_alias}{/if}" target="_blank" class="rightDir" title="{#MAIN_DOC_SHOW2_TITLE#}"><span class="dgrey doclink">{$item->document_alias}</span></a>
								{/if}
							{else}
								{if $item->document_breadcrum_title != ""}{$item->document_breadcrum_title|stripslashes|escape}{elseif $item->document_title != ""}{$item->document_title|stripslashes|escape}{else}{#MAIN_DOC_SHOW3_TITLE#}{/if}
								<br />
								<a href="../{if $item->Id!=1}{$item->document_alias}{/if}" target="_blank" class="rightDir" title="{#MAIN_DOC_SHOW2_TITLE#}"><span class="dgrey doclink">{$item->document_alias}</span></a>
							{/if}
							</div>
						</td>
						<td align="center">
							{if check_permission('rubric_edit')}
								<a href="index.php?do=rubs&action=edit&Id={$item->rubric_id}&cp={$sess}" class="link">{$item->rubric_title|escape:html}</a>
								<br />
								<strong>{#MAIN_START_DOC_AUTOR#}:</strong> {$item->document_author|escape}
							{else}
								{$item->rubric_title|escape:html}
								<br />
								<strong>{#MAIN_START_DOC_AUTOR#}:</strong> {$item->document_author|escape}
							{/if}
						</td>
						<td align="center"><span class="date_text dgrey">{$item->document_published|date_format:$TIME_FORMAT|pretty_date}</span></td>
					</tr>
				{/foreach}
			</table>
		</div>
	</div>
</div>

		<div class="widgets">
			<!-- Left widgets -->
			<div class="oneThree">

				<!-- Statistics -->
				<div class="widget">
					<div class="head">
						<h5>{#MAIN_STAT_SYSTEM_INFO#}</h5>
					</div>
					<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
						<col width="40%"/>
						<col/>
						<tbody>
							<tr class="noborder">
								<td>{$smarty.const.APP_NAME}</td>
								<td align="right"><span class="cmsStats">{$smarty.const.APP_VERSION}</span></td>
							</tr>
							<tr>
								<td>{#MAIN_STAT_DOMEN#}</td>
								<td align="right"><span class="cmsStats">{$domain}</span></td>
							</tr>
							<tr>
								<td>{#MAIN_STAT_PHP#}</td>
								<td align="right"><span class="cmsStats">{$php_version}</span></td>
							</tr>
							<tr>
								<td>{#MAIN_STAT_MYSQL_VERSION#}</td>
								<td align="right"><span class="cmsStats">{$mysql_version}</span></td>
							</tr>
							<tr>
								<td>{#MAIN_STAT_MYSQL#}</td>
								<td align="right"><span class="cmsStats">{$mysql_size}</span></td>
							</tr>
							<tr>
								<td>{#MAIN_STAT_CACHE#}</td>
								<td align="right"><span class="cmsStats" id="cachesize"><a href="javascript:void(0);" class="link" id="cacheShow">{#MAIN_STAT_CACHE_SHOW#}</a></span></td>
							</tr>
						</tbody>
					</table>
				</div>

			</div>

			<!-- Right widgets -->
			<div class="oneThree">

				<!-- User widget -->
				<div class="widget">
					<div class="head">
						<h5>{#MAIN_STAT#}</h5>
					</div>
					<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
						<col width="40%"/>
						<col/>
						<tbody>
							<tr class="noborder">
								<td>{#MAIN_STAT_DOCUMENTS#}</td>
								<td align="right"><span class="cmsStats">{$cnts.documents}</span></td>
							</tr>
							<tr>
								<td>{#MAIN_STAT_RUBRICS#}</td>
								<td align="right"><span class="cmsStats">{$cnts.rubrics}</span></td>
							</tr>
							<tr>
								<td>{#MAIN_STAT_QUERIES#}</td>
								<td align="right"><span class="cmsStats">{$cnts.request}</span></td>
							</tr>
							<tr>
								<td>{#MAIN_STAT_TEMPLATES#}</td>
								<td align="right"><span class="cmsStats">{$cnts.templates}</span></td>
							</tr>
							<tr>
								<td>{#MAIN_STAT_MODULES#}</td>
								<td align="right"><span class="cmsStats">{$cnts.modules_0+$cnts.modules_1}</span></td>
							</tr>
							{if $cnts.modules_0}
							<tr>
								<td><span class="ml20 dotted btext">{#MAIN_STAT_MODULES_OFF#}</span></td>
								<td align="right"><span class="cmsStatsAlert">{$cnts.modules_0|default:0}</span></td>
							</tr>
							{/if}
							<tr>
								<td>{#MAIN_STAT_USERS#}</td>
								<td align="right"><span class="cmsStats">{$cnts.users_0+$cnts.users_1}</span></td>
							</tr>
							{if $cnts.users_0}
							<tr>
								<td><span class="ml20 dotted btext">{#MAIN_STAT_USERS_WAIT#}</span></td>
								<td align="right"><span class="cmsStatsAlert">{$cnts.users_0|default:0}</span></td>
							</tr>
							{/if}
						</tbody>
					</table>
				</div>

			</div>

			<!-- Right widgets -->
			<div class="oneThree">

				<!-- User widget -->
				<div class="widget">
					<div class="head">
						<h5>{#MAIN_LOGS#}</h5>
					</div>
					<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
						<col width="40%"/>
						<col/>
						<tbody>
							<tr class="noborder">
								<td>
									{if check_permission('logs_view')}
									<a class="link" href="index.php?do=logs&cp={$sess}">{#MAIN_START_LOGS_LOG#}</a>
									{else}
									{#MAIN_START_LOGS_LOG#}
									{/if}
								</td>
								<td align="right"><span class="cmsStats">{$logs.logs}</span></td>
							</tr>
							<tr>
								<td>
									{if check_permission('logs_view')}
									<a class="link" href="index.php?do=logs&action=log404&cp={$sess}">{#MAIN_START_LOGS_404#}</a>
									{else}
									{#MAIN_START_LOGS_404#}
									{/if}
								</td>
								<td align="right"><span {if $logs.404 > 0}class="cmsStatsAlert"{else}class="cmsStats"{/if}>{$logs.404}</span></td>
							</tr>
							<tr>
								<td>
									{if check_permission('logs_view')}
									<a class="link" href="index.php?do=logs&action=logsql&cp={$sess}">{#MAIN_START_LOGS_SQL#}</a>
									{else}
									{#MAIN_START_LOGS_SQL#}
									{/if}
								</td>
								<td align="right"><span {if $logs.sql > 0}class="cmsStatsAlert"{else}class="cmsStats"{/if}>{$logs.sql}</span></td>
							</tr>
						</tbody>
					</table>
				</div>

			</div>
		<div class="fix"></div>
		</div>
		{if check_permission('logs_view')}

		{/if}