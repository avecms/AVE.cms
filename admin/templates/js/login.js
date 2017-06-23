$(document).ready(function(){
	$("form.mainForm").jqTransform({imgPath:"../images"});
	$("#captcha-ref").click(function() { $("#captcha img").attr("src", '../inc/captcha.php?refresh=' + new Date().getTime()); });
});