{if check_permission('documents')}

<script type="text/javascript" language="JavaScript">
{literal}
$(document).ready(function() {

	$(function() {		
		function format(data) {
			return "<div class='floatleft'><img src='" + data.avatar + "' class='rounded' /></div>"
			+"<div class='floatleft pl12'>"
			+"<span class='name'><span class='btext'>Имя:</span>&nbsp;"+data.firstname+" "+data.lastname+"&nbsp;(Id:&nbsp;"+data.userid+")</span>"
			+"<span class='email'><span class='btext'>Email:</span>&nbsp;"+data.email+"</span>"
			+"<span class='login'><span class='btext'>Логин:</span>&nbsp;"+data.login+"</span>"
			+"</div>";
		}

		function email(data) {
			return data.email
		}

		function login(data) {
			return data.login
		}

		function firstname(data) {
			return data.firstname
		}

		function lastname(data) {
			return data.lastname
		}

		function userid(data) {
			return data.userid
		}

		$("#find").autocomplete("index.php?do=docs&action=find_user&ajax=run&cp={$sess}", {
			width: $("#find").outerWidth(),
			max: 10,
			dataType: "json",
			matchContains: "word",
			scroll: true,
			scrollHeight: 185,
			parse: function(data) {
				return $.map(data, function(row) {
					return {
						data: row,
						value: row.login,
						result: $("#find").val()
					}
				});
			},
			formatItem: function(item) {
				return format(item);
			}
			}).result(function(e, item) {
				$("#find").val(firstname(item) +" "+ lastname(item) +" (Id: "+ userid(item) +")");
				$("#user_id").val(userid(item));
		});
	});

});
{/literal}
</script>
<div class="first"></div>

<div class="title"><h5>{#DOC_CHANGE_AU_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
    <div class="body">
		{#DOC_CHANGE_AU_INFO#}
    </div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
	    <ul>
	        <li class="firstB"><a href="index.php?pop=1" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
	        <li>{#DOC_CHANGE_AU_TITLE#}</li>
	    </ul>
	</div>
</div>

<form method="post" class="mainForm" action="index.php?do=docs&action=change_user&sub=save&cp={$sess}">

	<div class="widget first">
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
			<col width="" />
			<col width="100" />
			<tr>
				<td>
					<div class="pr12">
						<input type="text" name="q" id="find" value="" class="ac_input" /> 
						<input name="user_id" type="hidden" id="user_id" value="" />
						<input name="doc_id" type="hidden" id="doc_id" value="{$smarty.request.Id}" />
					</div>
				</td>
				<td align="center">
					<input type="submit" value="{#DOC_CHANGE_BUTTON#}" class="basicBtn" />
				</td>
			</tr>
		</table>
		<div class="fix"></div>
	</div>
</form>

{/if}