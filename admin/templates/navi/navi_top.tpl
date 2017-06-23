{if check_permission('document_view')}
	{include file='documents/nav_top.tpl'}
{/if}

{if check_permission('rubric_view')}
	{include file='rubs/nav.tpl'}
{/if}

{if check_permission('request_view')}
	{include file='request/nav.tpl'}
{/if}

{if check_permission('navigation_view')}
	{include file='navigation/nav.tpl'}
{/if}

{if check_permission('blocks_view')}
	{include file='blocks/nav.tpl'}
{/if}

{if check_permission('sysblocks_view')}
	{include file='sysblocks/nav.tpl'}
{/if}

{if check_permission('template_view')}
	{include file='templates/nav.tpl'}
{/if}

{if check_permission('mediapool_finder')}
	{include file='finder/nav.tpl'}
{/if}

{if check_permission('modules_view')}
	{include file='modules/nav.tpl'}
{/if}

{if check_permission('user_view')}
	{include file='user/nav.tpl'}
{/if}

{if check_permission('group_view')}
	{include file='groups/nav.tpl'}
{/if}

{if check_permission('gen_settings')}
	{include file='settings/nav.tpl'}
{/if}

{if check_permission('db_actions')}
	{include file='dbactions/nav.tpl'}
{/if}

{if check_permission('logs_view')}
	{include file='logs/nav.tpl'}
{/if}
