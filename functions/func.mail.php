<?php

/**
 * AVE.cms
 *
 * @package AVE.cms
 * @version 3.x
 * @filesource
 * @copyright © 2007-2014 AVE.cms, http://www.ave-cms.ru
 *
 * @license GPL v.2
 */


/**
 * Отправка e-Mail
 *
 * @param string $to - email получателя
 * @param string $body - текст сообщения
 * @param string $subject - тема сообщения
 * @param string $from_email - e-mail отправителя
 * @param string $from_name - имя отправителя
 * @param string $type - содержимое (html или text)
 * @param array $attach - пути файлов вложений
 * @param bool $saveattach - сохранять вложения после отправки в ATTACH_DIR?
 * @param bool $signature - добавлять подпись из общих настроек?
 */
if ( ! function_exists('send_mail'))
{
	function send_mail($to='', $body='', $subject='', $from_email='', $from_name='', $type='text', $attach=array(), $saveattach=true, $signature=true)
	{
		require_once BASE_DIR . '/lib/SwiftMailer/swift_required.php';

		unset($transport, $message, $mailer);

		$to = str_nospace($to);

		$from_email = str_nospace($from_email);

		// Определяем тип письма
		$type = ((strtolower($type) == 'html' || strtolower($type) == 'text/html') ? 'text/html' : 'text/plain');

		// Добавляем подпись, если просили
		if ($signature)
		{
			if ($type == 'text/html')
			{
				$signature = '<br><br>' . nl2br(get_settings('mail_signature'));
			}
			else
			{
				$signature = "\r\n\r\n" . get_settings('mail_signature');
			}
		}
		else $signature = '';

		// Составляем тело письма
		$body = stripslashes($body) . $signature;

		if ($type == 'text/html')
		{
			$body = str_replace(array("\t","\r","\n"),'',$body);
			$body = str_replace(array('  ','> <'),array(' ','><'),$body);
		}

		// Формируем письмо
		$message = Swift_Message::newInstance($subject)
			-> setFrom(array($from_email => $from_name))
			-> setTo($to)
			-> setContentType($type)
			-> setBody($body)
			-> setMaxLineLength((int)get_settings('mail_word_wrap'));

		// Прикрепляем вложения
		if ($attach)
		{
			foreach ($attach as $attach_file)
			{
				$message -> attach(Swift_Attachment::fromPath(trim($attach_file)));
			}
		}

		// Выбираем метод отправки и формируем транспорт
		switch (get_settings('mail_type'))
		{
			default:
			case 'mail':
				$transport = Swift_MailTransport::newInstance();
				break;

			case 'smtp':
				$transport = Swift_SmtpTransport::newInstance(stripslashes(get_settings('mail_host')), (int)get_settings('mail_port'));

				// Добавляем шифрование
				$smtp_encrypt = get_settings('mail_smtp_encrypt');
				if($smtp_encrypt)
					$transport
						->setEncryption(strtolower(stripslashes($smtp_encrypt)));

				// Имя пользователя/пароль
				$smtp_user = get_settings('mail_smtp_login');
				$smtp_pass = get_settings('mail_smtp_pass');
				if($smtp_user)
					$transport
						->setUsername(stripslashes($smtp_user))
						->setPassword(stripslashes($smtp_pass));
				break;

			case 'sendmail':
				$transport = Swift_SendmailTransport::newInstance(get_settings('mail_sendmail_path'));
				break;
		}

		// Сохраняем вложения в ATTACH_DIR, если просили
		if ($attach && $saveattach)
		{
			$attach_dir = BASE_DIR . '/' . ATTACH_DIR . '/';
			foreach ($attach as $file_path)
			{
				if ($file_path && file_exists($file_path))
				{
					$file_name = basename($file_path);
					$file_name = str_replace(' ','',mb_strtolower(trim($file_name)));
					if (file_exists($attach_dir . $file_name))
					{
						$file_name = rand(1000, 9999) . '_' . $file_name;
					}
					$file_path_new = $attach_dir . $file_name;
					if (!@move_uploaded_file($file_path,$file_path_new))
					{
						copy($file_path,$file_path_new);
					}
				}
			}
		}

		// Отправляем письмо
		/** @var $transport TYPE_NAME */
		$mailer = Swift_Mailer::newInstance($transport);

		if (!@$mailer -> send($message, $failures))
		{
			reportLog('Не удалось отправить письма следующим адресатам: ' . implode(',',$failures));
			return $failures;
		}

	}
}

if ( ! function_exists('safe_mailto'))
{
	function safe_mailto($email, $title = '', $attributes = '')
	{
		$title = (string) $title;

		if ($title == "")
		{
			$title = $email;
		}

		for ($i = 0; $i < 16; $i++)
		{
			$x[] = substr('<a href="mailto:', $i, 1);
		}

		for ($i = 0; $i < strlen($email); $i++)
		{
			$x[] = "|".ord(substr($email, $i, 1));
		}

		$x[] = '"';

		if ($attributes != '')
		{
			if (is_array($attributes))
			{
				foreach ($attributes as $key => $val)
				{
					$x[] =  ' '.$key.'="';
					for ($i = 0; $i < strlen($val); $i++)
					{
						$x[] = "|".ord(substr($val, $i, 1));
					}
					$x[] = '"';
				}
			}
			else
			{
				for ($i = 0; $i < strlen($attributes); $i++)
				{
					$x[] = substr($attributes, $i, 1);
				}
			}
		}

		$x[] = '>';

		$temp = array();

		for ($i = 0; $i < strlen($title); $i++)
		{
			$ordinal = ord($title[$i]);

			if ($ordinal < 128)
			{
				$x[] = "|".$ordinal;
			}
			else
			{
				if (count($temp) == 0)
				{
					$count = ($ordinal < 224) ? 2 : 3;
				}

				$temp[] = $ordinal;
				if (count($temp) == $count)
				{
					$number = ($count == 3) ? (($temp['0'] % 16) * 4096) + (($temp['1'] % 64) * 64) + ($temp['2'] % 64) : (($temp['0'] % 32) * 64) + ($temp['1'] % 64);
					$x[] = "|".$number;
					$count = 1;
					$temp = array();
				}
			}
		}

		$x[] = '<'; $x[] = '/'; $x[] = 'a'; $x[] = '>';

		$x = array_reverse($x);
		ob_start();

	?><script type="text/javascript">
	//<![CDATA[
	var l=new Array();
	<?php
	$i = 0;
	foreach ($x as $val){ ?>l[<?php echo $i++; ?>]='<?php echo $val; ?>';<?php } ?>

	for (var i = l.length-1; i >= 0; i=i-1){
	if (l[i].substring(0, 1) == '|') document.write("&#"+unescape(l[i].substring(1))+";");
	else document.write(unescape(l[i]));}
	//]]>
	</script><?php

		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}

?>