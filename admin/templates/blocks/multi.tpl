<div class="title"><h5>{#BLOCK_COPY_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
    <div class="body">
		{#BLOCK_COPY_TIP2#}
    </div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
	    <ul>
	        <li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
	        <li><a href="index.php?do=blocks&cp={$sess}" title="">{#BLOCK_LIST_LINK#}</a></li>
	        <li>{#BLOCK_COPY_TITLE#}</li>
	    </ul>
	</div>
</div>

      {foreach from=$errors item=e}
      {assign var=message value=$e}
		<ul class="messages first">
			<li class="highlight red"><strong>Ошибка:</strong> {$message}</li>
		</ul>
      {/foreach}

<div class="widget first">
<div class="head"><h5 class="iFrames">{#BLOCK_COPY_TITLE#}</h5></div>
<form name="m" method="post" action="?do=blocks&action=multi&sub=save&id={$smarty.request.id|escape}" class="mainForm">
<div class="rowElem noborder">

	<label>{#BLOCK_NAME#}</label>
	<div class="formRight"><input name="block_name" type="text" value="{$smarty.request.block_name|escape|default:"Default"}" maxlength="50" style="width: 250px;" />&nbsp;<input class="basicBtn" type="submit" value="{#BLOCK_BUTTON_COPY#}" /></div>
	<div class="fix"></div>
	<input name="oId" type="hidden" id="oId" value="{$smarty.request.id|escape}" />
</div>
</form>
</div>
