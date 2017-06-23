{if $get_field_text_to_image != load}
  {assign var=get_field_text_to_image value='' scope="global"}
  <link href="{$ABS_PATH}fields/{$field_dir}/css/field.css" rel="stylesheet" type="text/css" media="screen" />
  <script src="{$ABS_PATH}fields/{$field_dir}/js/jscolor.js" type="text/javascript"></script>
  {assign var=get_field_text_to_image value="load" scope="global"}
{/if}
<div class="get_field_text_to_image mt10" id="text_to_img{$field_id}">
  <div class="get_field_text_to_image mb10" id="wr_text_to_img{$field_id}">
<input type="text" class="mousetrap" value="" id="text{$field_id}" placeholder="{#TXT_IMG_TEXT#}" style="width: 280px;"/>&nbsp;&nbsp;
<select style="width: 300px;" class="get_field_text_to_image mb10" id="font{$field_id}">
  <option>CoreSansNR27.ttf</option>
  <option>CoreSansNR37.ttf</option>
  <option>CoreSansNR47.ttf</option>
  <option>Cuprum-Regular.ttf</option>
  <option>Cuprum-Italic.ttf</option>
  <option>Cuprum-Bold.ttf</option>
</select>&nbsp;&nbsp;
<span>{#TXT_IMG_SIZE#}</span>&nbsp;&nbsp;
<input type="text" class="mousetrap" value="14" id="font_size{$field_id}" placeholder="14" style="width: 28px;"/>&nbsp;&nbsp;
<span>{#TXT_IMG_COLOR#}</span>&nbsp;&nbsp;
<input type="text" class="mousetrap jscolor {ldelim}hash:true{rdelim}" value="#FF5C3C" id="color_text{$field_id}" placeholder="#CCCCCC" style="width: 64px;"/>&nbsp;&nbsp;
<br />
<span>{#TXT_IMG_ALT#}</span>
<label style="float:none; display: inline-block; margin-left:-6px; position:relative; top:4px;">
<input type="checkbox" value="1" id="alt_text{$field_id}" checked="checked">
</label>
<span id="results{$field_id}">&nbsp;</span>&nbsp;&nbsp;
<span id="exist_text{$field_id}">&nbsp;</span>&nbsp;&nbsp;
<span id="exist_image{$field_id}">&nbsp;</span>
<div id="btn{$field_id}" style="width:100px; text-align:center;" class="button basicBtn">{#TXT_IMG_BTN#}</div>&nbsp;&nbsp;
<script type="text/javascript" language="javascript">
   var sess = '{$sess}';
    $("#btn{$field_id}").on('click', function call() {ldelim}
   var text       = $("#text{$field_id}").val();
   var font       = $("#font{$field_id}").val();
   var font_size  = $("#font_size{$field_id}").val();
   var color_text = $("#color_text{$field_id}").val();
   if($("#alt_text{$field_id}").attr("checked") == 'checked') {ldelim}
   var alt_text   = $("#alt_text{$field_id}").val();
{rdelim}
   else {ldelim}
   var alt_text = '0'
{rdelim}
        $.ajax({ldelim}
          type: 'POST',
          url: '{$ABS_PATH}fields/text_to_image/res.php',
          data: {ldelim}a:text,b:font,c:font_size,d:color_text,e:alt_text{rdelim},
          success: function(data) {ldelim}
            $('#exist_text{$field_id}').html("&nbsp;");
            $('#exist_image{$field_id}').html("&nbsp;");
            $('#results{$field_id}').html(data);
            $('#wrap_feld{$field_id}').val(data.replace("/../../", "[tag:path]"));
          {rdelim},
          error:  function(xhr, str){ldelim}
      alert('Возникла ошибка: ' + xhr.responseCode);
          {rdelim}
{rdelim});
 {rdelim});
</script>
	</div>
</div>
<input type="text" id="wrap_feld{$field_id}" style="width: 100%;" name="feld[{$field_id}]" value="{$field_value|escape}" class="mousetrap" />
<script type="text/javascript" language="javascript">
var ext_img = $("#wrap_feld{$field_id}").val();
if (ext_img.length==0){ldelim}
$('#exist_text{$field_id}').html("&nbsp;");  
$('#exist_image{$field_id}').html("&nbsp;");
{rdelim}
else {ldelim}
$('#exist_text{$field_id}').html("{#TXT_IMG_EXIST#}&nbsp;");  
$('#exist_image{$field_id}').html(ext_img.replace("[tag:path]", "/../../"));
{rdelim}
</script>