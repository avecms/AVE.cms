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

	define('BASE_DIR', str_replace("\\", "/", dirname(dirname(__FILE__))));

	require(BASE_DIR . '/inc/init.php');

	unset($_SESSION['captcha_keystring']);

	require(BASE_DIR . '/lib/kcaptcha/kcaptcha.php');

	$captcha = new KCAPTCHA();

	$_SESSION['captcha_keystring'] = $captcha->getKeyString();
?>