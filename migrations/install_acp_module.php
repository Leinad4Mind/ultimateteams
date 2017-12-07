<?php
/**
 *
 * Ultimate Teams. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Mr. Goldy
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mrgoldy\ultimateteams\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['ut_image_dir']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('ut_enable_top_link', 1)),
			array('config.add', array('ut_enable_bottom_link', 1)),
			array('config.add', array('ut_image_dir', 'images/teams')),
			array('config.add', array('ut_image_size', 15)),
			array('config.add', array('ut_multiple_teams', 1)),
			array('config.add', array('ut_notification_id', 0)),
			array('config.add', array('ut_tag_length', 5)),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_ULTIMATETEAMS'
			)),
			array('module.add', array(
				'acp',
				'ACP_ULTIMATETEAMS',
				array(
					'module_basename'	=> '\mrgoldy\ultimateteams\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
