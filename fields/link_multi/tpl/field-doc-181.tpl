{if ! empty($field_value)}
<table class="table table-params table-no-border">
	<tr>
		<td class="table-header">
			Документация
		</td>
	</tr>

{foreach from=$field_value item=list}
	<tr>
		<td>
			<a href="{$list[1]}" target="_blank"><i class="fa fa-file-pdf-o"></i>&nbsp;{$list[0]}</a>
		</td>
	</tr>
{/foreach}
	
</table>
{else}
<table class="table table-params table-no-border">
	<tr>
		<td class="table-header">
			Документация
		</td>
	</tr>
	<tr>
		<td>
			<div class="alert alert-warning">
				Нет файлов для скачивания
			</div>
		</td>
	</tr>
</table>
{/if}