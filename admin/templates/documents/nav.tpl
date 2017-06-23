<li>
	<a {if $smarty.request.do=='docs'}class="active"{/if}href="index.php?do=docs&cp={$sess}"><span>{#MAIN_NAVI_DOCUMENTS#}</span></a>
	{if $smarty.request.do=='docs'}
	<ul class="sub" style="display: block; ">
	{foreach from=$rubrics item=rubric}
		{if $rubric->Show==1 && $rubric->rubric_docs_active==1}
			<li {if $smarty.request.do=='docs' && $rubric->Id==$smarty.request.rubric_id}class="active"{/if}>
				<a href="index.php?do=docs&rubric_id={$rubric->Id}&cp={$sess}">{$rubric->rubric_title|escape}</a>
				<a class="numberRight rightDir" href="index.php?&do=docs&action=new&rubric_id={$rubric->Id}&cp={$sess}" title="{#DOC_BUTTON_ADD_DOCUMENT#}"><img src="{$tpl_dir}/images/icons/add2.png" alt="" /></a>
			</li>
		{/if}
	{/foreach}
	</ul>
	{/if}
</li>