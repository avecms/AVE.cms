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
 * Возвращаем аватар по пользователю
 *
 * @param int    $id   Ид пользователя- если не придет то текущий пользователь
 * @param int    $size размер картинки по краю
 * @param string $prefix
 * @return string путь до файла с превью
 */
function getAvatar($id = null, $size = 58, $prefix = "")
{
	global $AVE_DB;
	static $result=array();

	if ($id === null) $id = $_SESSION['user_id'];

	if(!isset($result[$id])){
		$user=get_user_rec_by_id($id);
		$ava = ABS_PATH. UPLOAD_DIR .'/avatars/'.(($prefix==="")?"":$prefix).md5($user->user_name);
		$ava = (file_exists(BASE_DIR.$ava.'.jpg') ? $ava.'.jpg' : (file_exists(BASE_DIR.$ava.'.png') ? $ava.'.png' : (file_exists(BASE_DIR.$ava.'.gif') ? $ava.'.gif' : '')));
		$result[$id]=$ava;
	}

	$ava=$result[$id];

	$src = (file_exists(BASE_DIR.$ava) ?
		make_thumbnail(array('link' => $ava,'size' => 'c' . $size . 'x' . $size)):
		make_thumbnail(array('link' => $AVE_DB->Query("SELECT default_avatar FROM " . PREFIX . "_user_groups WHERE user_group=" . (int)$user->user_group)->GetCell(), 'size' => 'c' . $size . 'x' . $size))
	);

	return $src;
}


/**
 * Устанавливаем аватар пользователю
 *
 * @param int $id Ид пользователя
 * @param string $avatar путь до картинки которая будет аватаром
 * @return bool установился аватар или нет
 */
function SetAvatar($id, $avatar)
{
	if ($id === null) $id = $_SESSION['user_id'];

	$user = get_user_rec_by_id($id);

	$file_ext = pathinfo($avatar, PATHINFO_EXTENSION);

	if (! file_exists($avatar))
		return false;

	$new_ava = BASE_DIR . '/' . UPLOAD_DIR . '/avatars/' . md5($user->user_name) . '.' . strtolower($file_ext);

	foreach (glob(BASE_DIR . '/' . UPLOAD_DIR . '/avatars/' . md5($user->user_name) . '.*') as $filename)
	{
		@unlink($filename);
	}
	
	//Чистим превьюшки
	foreach (glob(BASE_DIR . '/' . UPLOAD_DIR . '/avatars/' . THUMBNAIL_DIR . '/' . md5($user->user_name) . '*.*') as $filename)
	{
		@unlink($filename);
	}

	@file_put_contents($new_ava, file_get_contents($avatar));
	@unlink($avatar);

	return true;
}


/**
 * Формирование строки имени пользователя
 * При наличии всех параметров пытается сформировать строку <b>Имя Фамилия</b>
 * Если задать $short=1 - формирует короткую форму <b>И. Фамилия</b>
 * Когда отсутствует информация о Имени или Фамилии пытается сформировать
 * строку на основе имеющихся данных, а если данных нет вообще - выводит
 * имя анонимного пользователя которое задается в основных настройках системы.
 *
 * @todo добавить параметр 'anonymous' в настройки
 *
 * @param string $login логин пользователя
 * @param string $first_name имя пользователя
 * @param string $last_name фамилия пользователя
 * @param int $short {0|1} признак формирования короткой формы
 * @return string
 */
function get_username($login = '', $first_name = '', $last_name = '', $short = 1)
{
	if ($first_name != '' && $last_name != '')
	{
		if ($short == 1) $first_name = mb_substr($first_name, 0, 1, 'utf-8') . '.';
		return ucfirst_utf8(mb_strtolower($first_name)) . ' ' . ucfirst_utf8(mb_strtolower($last_name));
		return ucfirst_utf8(mb_strtolower($login));
	}
	elseif ($first_name != '' && $last_name == '')
	{
		return ucfirst_utf8(mb_strtolower($first_name));
	}
	elseif ($first_name == '' && $last_name != '')
	{
		return ucfirst_utf8(mb_strtolower($last_name));
	}
	elseif ($login != '')
	{
		return ucfirst_utf8(mb_strtolower($login));
	}

	return 'Anonymous';
}


/**
 * Возвращает запись для пользователя по идентификатору
 * не делает лишних запросов
 *
 * @param int $id - идентификатор пользователя
 * @return object
 */
function get_user_rec_by_id($id){
	global $AVE_DB;

	static $users = array();

	if (!isset($users[$id]))
	{
		$row = $AVE_DB->Query("
			SELECT
				*
			FROM " . PREFIX . "_users
			WHERE Id = '" . (int)$id . "'
		")->FetchRow();

		$users[$id] = $row;
	}

	return $users[$id];
}


/**
 * Возвращает параметры группы пользователей по идентификатору
 * не делает лишних запросов
 *
 * @param int $id - идентификатор группы
 * @return object
 */
function get_usergroup_rec_by_id($id){
	global $AVE_DB;

	static $usergroups = array();

	if (!isset($usergroups[$id]))
	{
		$row = $AVE_DB->Query("
			SELECT
				*
			FROM " . PREFIX . "_user_groups
			WHERE user_group = '" . (int)$id . "'
		")->FetchRow();

		$usergroups[$id] = $row;
	}
	return $usergroups[$id];

}


/**
 * Возвращает login пользователя по его идентификатору
 *
 * @param int $id - идентификатор пользователя
 * @return string
 */
function get_userlogin_by_id($id)
{
	$rec = get_user_rec_by_id($id);

	return $rec->user_name;
}


/**
 * Возвращает имя группы пользователя по его идентификатору
 *
 * @param int $id - идентификатор группы пользователя
 * @return string
 */
function get_usergroup_by_id($id)
{
	$rec = get_usergroup_rec_by_id($id);

	return $rec->user_group_name;
}


/**
 * Возвращает email пользователя по его идентификатору
 *
 * @param int $id - идентификатор пользователя
 * @return string
 */
function get_useremail_by_id($id)
{
	$rec = get_user_rec_by_id($id);

	return $rec->email;
}


/**
 * Возвращает имя пользователя по его идентификатору
 *
 * @param int $id - идентификатор пользователя
 * @param int $param - Сокрашать имя (1 - да, 0 - нет)
 *
 * @return string
 */
function get_username_by_id($id, $param = 1)
{
	$row = get_user_rec_by_id($id);
	$row = !empty($row) ? get_username($row->user_name, $row->firstname, $row->lastname, $param) : get_username();
	return $row;
}

?>