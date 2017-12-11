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

class install_user_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'users', 'user_team_id');
	}

	static public function depends_on()
	{
		return array('\mrgoldy\ultimateteams\migrations\install_user_data');
	}

	public function update_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'ut_teams'		=> array(
					'COLUMNS'		=> array(
						'team_id'			=> array('UINT', null, 'auto_increment'),
						'team_type'			=> array('USINT', 2),
						'team_name'			=> array('VCHAR:255', ''),
						'team_tag'			=> array('VCHAR_UNI:20', ''),
						'team_tag_clean'	=> array('VCHAR_UNI:20', ''),
						'team_colour'		=> array('VCHAR:6', ''),
						'team_description'	=> array('TEXT_UNI', ''),
						'team_image'		=> array('VCHAR:100', ''),
						'team_website'		=> array('VCHAR:200', ''),
						'team_location'		=> array('VCHAR:100', ''),
					),
					'PRIMARY_KEY'	=> 'team_id',
				),

				$this->table_prefix . 'ut_corr'	=> array(
					'COLUMNS'		=> array(
						'team_id'			=> array('UINT', 0),
						'user_id'			=> array('UINT', 0),
						'team_leader'		=> array('BOOL', 0),
						'user_status'		=> array('USINT', 1),
					),
					'KEYS'			=> array(
						'team_id'			=> array('INDEX', 'team_id'),
						'user_id'			=> array('INDEX', 'user_id'),
						'team_leader'		=> array('INDEX', 'team_leader'),
					),
				),
			),
			'add_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_team_id'			=> array('UINT', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_team_id',
				),
			),
			'drop_tables'		=> array(
				$this->table_prefix . 'ut_teams',
				$this->table_prefix . 'ut_corr',
			),
		);
	}
}
