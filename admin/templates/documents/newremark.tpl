{if check_permission("remark_view")}
<div class="first"></div>

<div class="title"><h5>{#DOC_NOTICE#}</h5></div>


<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php?pop=1" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li>{#DOC_NOTICE#}</li>
			<li>{$document_title}</li>
		</ul>
	</div>
</div>

{if $answers}
	<div class="widget first">
		<ul class="messagesOne">
			{foreach from=$answers item=answer}
			<li {if $answer.remark_author_id == $smarty.session.user_id}class="by_me"{else}class="by_user"{/if}>
				<a href="#" title="">{if $answer.remark_avatar}<img src="{ $answer.remark_avatar}" class="rounded">{else}<img src="{$tpl_dir}/images/user.png" class="rounded" alt="" />{/if}</a>
					<div class="messageArea">
					<span class="aro"></span>
						<div class="infoRow">
							<span class="name"><strong>{$answer.remark_author}</strong> пишет: <strong>{$answer.remark_title}</strong></span>
							{if check_permission("remark_edit")}
							<a href="index.php?do=docs&action=remark_del&Id={$smarty.request.Id|escape}&CId={$answer.Id}&pop=1&cp={$sess}" title="{#DOC_NOTICE_DELETE_LINK#}" class="topDir icon_sprite ico_delete floatright"></a>
							{else}
							<span class="icon_sprite ico_delete_no floatright"></span>
							{/if}
							<span class="time">{$answer.remark_published|date_format:$TIME_FORMAT|pretty_date} </span>
							<div class="clear"></div>
						</div>
					{$answer.remark_text}
					</div>
				<div class="clear"></div>
			</li>
			{/foreach}
		</ul>
	</div>

	{if check_permission("remark_edit")}
		<div class="widget first">
			<form method="post" action="index.php?do=docs&action=remark_status&Id={$smarty.request.Id|escape}&pop=1&cp={$sess}" class="mainForm">
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<tr class="noborder">
					<td>
						<input class="float" name="remark_status" type="checkbox" id="remark_status" value="1" {if $remark_status==1}checked="checked" {/if}/>&nbsp;<label>{#DOC_ALLOW_NOTICE#}</label>
					</td>
				</tr>
				<tr>
					<td>
					<input type="submit" class="basicBtn" value="{#DOC_BUTTON_NOTICE#}" />
					</td>
				</tr>
		</table>
			</form>
		</div>
	{/if}

{/if}

{if $page_nav}
	<div class="pagination">
	<ul class="pages">
		{$page_nav}
	</ul>
	</div>
{/if}


{/if}
{if check_permission("remark_edit")}
	{if $reply==1}
		{if $remark_status==1 || $new ==1}
			{include file='documents/replyform.tpl'}
		{/if}
	{/if}
{/if}
