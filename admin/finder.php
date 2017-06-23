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
if (!defined('ACP'))
{
	header('Location:index.php');
	exit;
}

global $AVE_DB, $AVE_Template;
	if(check_permission_acp('mediapool_finder'))
	{
		$AVE_Template->assign('content', $AVE_Template->fetch('finder/finder.tpl'));
	}
?>