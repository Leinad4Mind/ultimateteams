<?php
/**
 *
 * Ultimate Teams. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Mr. Goldy
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mrgoldy\ultimateteams\acp;

/**
 * Ultimate Teams ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\mrgoldy\ultimateteams\acp\main_module',
			'title'		=> 'ACP_ULTIMATETEAMS',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_ULTIMATETEAMS_SETTINGS',
					'auth'	=> 'ext_mrgoldy/ultimateteams && acl_a_board',
					'cat'	=> array('ACP_ULTIMATETEAMS')
				),
			),
		);
	}
}
