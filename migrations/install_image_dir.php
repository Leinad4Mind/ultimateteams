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

class install_image_dir extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		global $phpbb_container;
		return $phpbb_container->get('filesystem')->exists($this->phpbb_root_path . 'images/teams');
	}

	static public function depends_on()
	{
		return array('\mrgoldy\ultimateteams\migrations\install_user_data');
	}

	public function update_data()
	{
		return array(
			# Create teams images directory
			array('custom', array(array($this, 'create_teams_image_dir'))),
		);
	}

	public function create_teams_image_dir()
	{
		global $phpbb_container;

		$img_dir = $this->phpbb_root_path . 'images';
		$teams_dir = $img_dir . '/teams';
		$filesystem = $phpbb_container->get('filesystem');

		if ($filesystem->exists($img_dir) && $filesystem->is_writable($img_dir))
		{
			if (!$filesystem->exists($teams_dir))
			{
				$filesystem->mkdir($teams_dir, 511);
			}
		}
	}
}
