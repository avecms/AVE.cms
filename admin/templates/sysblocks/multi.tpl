<div class="title">
	<h5>{#SYSBLOCK_COPY_TITLE#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
    <div class="body">
		{#SYSBLOCK_COPY_TIP2#}
    </div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
	    <ul>
	        <li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
	        <li><a href="index.php?do=sysblocks&cp={$sess}" title="">{#SYSBLOCK_LIST_LINK#}</a></li>
	        <li>{#SYSBLOCK_COPY_TITLE#}</li>
	    </ul>
	</div>
</div>

<div class="widgets">
	<table class="first tableButtons" cellpadding="0" cellspacing="0" width="100%" id="sysblocksButtons">
		<colgroup>
			<col width="25%">
			<col width="25%">
			<col width="25%">
			<col width="25%">
		</colgroup>
		<tbody>
		<tr>
			<td>
				<a class="button greyishBtn topBtn" href="index.php?do=sysblocks&cp={$sess}">{#SYSBLOCK_LIST_LINK#}</a>
			</td>
			<td>
				<a class="button greenBtn topBtn" href="index.php?do=sysblocks&action=new&cp={$sess}">{#SYSBLOCK_BUTTON_ADD#}</a>
			</td>
			<td>
				<a class="button basicBtn topBtn" href="index.php?do=sysblocks&action=groups&cp={$sess}">{#SYS_GROUPS#}</a>
			</td>
		</tr>
		</tbody>
	</table>
</div>

{foreach from=$errors item=e}
{assign var=message value=$e}
<ul class="messages first">
	<li class="highlight red"><strong>Ошибка:</strong> {$message}</li>
</ul>
{/foreach}

<div class="widget first">
	<div class="head">
		<h5 class="iFrames">{#SYSBLOCK_COPY_TITLE#}</h5>
	</div>
	<form name="m" method="post" action="?do=sysblocks&action=multi&sub=save&id={$smarty.request.id|escape}" class="mainForm">
		<div class="rowElem noborder">

			<label>{#SYSBLOCK_NAME#}</label>
			<div class="formRight">
				<input name="sysblock_name" type="text" value="{$smarty.request.sysblock_name|escape|default:"Default"}" maxlength="50" style="width: 250px;" />&nbsp;<input class="basicBtn" type="submit" value="{#SYSBLOCK_BUTTON_COPY#}" />
			</div>
			<div class="fix"></div>
			<input name="oId" type="hidden" id="oId" value="{$smarty.request.id|escape}" />
		</div>
	</form>
</div>
