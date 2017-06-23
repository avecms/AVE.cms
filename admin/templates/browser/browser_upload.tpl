<script>
	var path_upload = "{$smarty.request.dir|escape}";
</script>

<!-- Wrapper -->
<div class="wrapper">
	<div class="widget">
		<form action="index.php?do=browser&type={$smarty.request.typ|escape}&action=upload2&tval={$smarty.request.dir|escape}" method="post" enctype="multipart/form-data" name="upform" id="upform" style="display:inline;">
			<input name="fromuploader" type="hidden" id="fromuploader" value="1" />
			<input name="target" type="hidden" value="{$smarty.request.target}" />
			<fieldset>
				<div class="head">
					<h5>{#MAIN_MP_SELECT_FILES#}</h5>
				</div>
				<div id="uploader" style="position: relative;">
					<p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p>
				</div>
			</fieldset>

			<div class="rowElem">
				<input name="button" type="submit" class="basicBtn" value="{#MAIN_BUTTON_UPLOAD#}" />
			</div>
		</form>
	</div>
</div>

<script language="javascript">
{literal}
$(function() {
	//===== File uploader =====//
	$("#uploader").pluploadQueue({
		runtimes : 'html5,flash,html4,browserplus',
		//browse_button : 'pickfiles', // you can pass in id...
		url : '../inc/upload.php?path_upload='+path_upload,
		max_file_size : '150mb',
		unique_names : true,
		dragdrop: true,
		filters : [
			{title : "Image files", extensions : "jpg,jpeg,jpe,gif,png"},
			{title : "Video files", extensions : "mp4,avi,mov,wmv,wmf"},
			{title : "Music files", extensions : "mp3"},
			{title : "Documents", extensions : "doc,xls,pdf"},
			{title : "Zip files", extensions : "zip,rar"}
		],
		// Flash settings
		flash_swf_url : '/lib/scripts/uploader/Moxie.swf'
	});

	// Client side form validation
	$('#upform').submit(function(event) {
		var uploader = $('#uploader').pluploadQueue();
		// Files in queue upload them first

			// When all files are uploaded submit form
			uploader.bind('StateChanged', function() {
				if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
					$('#upform')[0].submit();
				}
			});
			uploader.start();

		return false;
	});

	$("#uploader").pluploadQueue();

});
{/literal}
</script>