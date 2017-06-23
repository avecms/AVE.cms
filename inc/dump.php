<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
<title>Plupload - Form dump</title>
</head>
<body style="font: 11px 'Consolas', Verdana, Tahoma; background: #fff; color: #484848">

<h1>Post dump</h1>

<table>
	<col width="200">
	<col width="10">
	<col>
	<tr>
		<th>Name</th>
		<th></th>
		<th>Value</th>
	</tr>
	<?php $count = 0; foreach ($_POST as $name => $value) { ?>
	<tr class="<?php echo $count % 2 == 0 ? 'alt' : ''; ?>">
		<td style="background: #f0f0f0; padding: 10px"><?php echo htmlentities(stripslashes($name)); ?></td>
		<td></td>
		<td style="background: #f0f0f0; padding: 10px"><pre><?php echo (is_array($value)) ? var_export($value) : nl2br(htmlentities(stripslashes($value))); ?></pre></td>
	</tr>
	<?php } ?>
</table>

</body>
</html>
