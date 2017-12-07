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
 * Ultimate Teams ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $phpbb_container, $phpbb_root_path, $request, $template, $user;

		$filesystem = $phpbb_container->get('filesystem');
		$lang = $phpbb_container->get('language');
		$log = $phpbb_container->get('log');

		$this->tpl_name = 'acp_ut_settings';
		$this->page_title = $user->lang('ACP_ULTIMATETEAMS');
		add_form_key('ut_settings');

		if ($request->is_set_post('submit'))
		{
			# Request user input
			$ut_enable_bottom_link	= $request->variable('ut_enable_bottom_link', 0);
			$ut_enable_top_link		= $request->variable('ut_enable_top_link', 0);
			$ut_image_size			= $request->variable('ut_image_size', 15);
			$ut_image_dir			= $request->variable('ut_image_dir', 'images/teams');
			$ut_multiple_teams		= $request->variable('ut_multiple_teams', 0);
			$ut_tag_length			= $request->variable('ut_tag_length', 5);

			# Check form key for security
			if (!check_form_key('ut_settings'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			# Check tag length bounderies
			if ($ut_tag_length < 1 || $ut_tag_length > 20)
			{
				trigger_error('ACP_UT_ERROR_TAG_LENGTH', E_USER_WARNING);
			}

			# Check if directory exists
			if (!$filesystem->exists($phpbb_root_path . $ut_image_dir))
			{
				trigger_error('ACP_UT_ERROR_DIRECTORY_NOT_EXIST', E_USER_WARNING);
			}

			# Check if directory is writable
			if (!$filesystem->is_writable($phpbb_root_path . $ut_image_dir))
			{
				trigger_error('ACP_UT_ERROR_DIRECTORY_NOT_WRITE', E_USER_WARNING);
			}

			# No errors, set the config
			$config->set('ut_enable_bottom_link', $ut_enable_bottom_link);
			$config->set('ut_enable_top_link', $ut_enable_top_link);
			$config->set('ut_image_size', $ut_image_size);
			$config->set('ut_image_dir', $ut_image_dir);
			$config->set('ut_multiple_teams', $ut_multiple_teams);
			$config->set('ut_tag_length', $ut_tag_length);

			# Add it to the log
			$log->add('admin', $user->data['user_id'], $user->data['user_ip'], 'ACP_UT_LOG_SETTINGS_SAVED', time(), array());

			# Send confirmation message
			trigger_error($lang->lang('ACP_UT_SETTINGS_SAVED') . adm_back_link($this->u_action));
		}

		$action = $request->variable('action', '');

		if (!empty($action))
		{
			if ($request->is_ajax())
			{
				if (confirm_box(true))
				{
					switch ($action)
					{
						case 'purgeteam':
							# Delete the images
							$this->purge_ultimateteams_images();

							# Add it to the log
							$log->add('admin', $user->data['user_id'], $user->data['user_ip'], 'ACP_UT_LOG_IMAGES_PURGED', time(), array());

							# Show success message
							trigger_error($lang->lang('ACP_UT_PURGE_TEAM_IMAGES_SUCCESS'));
						break;

						case 'findteams':
							$teams = $this->find_teams_without_leaders();

							# Add it to the log
							$log->add('admin', $user->data['user_id'], $user->data['user_ip'], 'ACP_UT_LOG_TEAMS_SEARCHED', time(), array());

							# Set up complete message
							$complete_message = $teams['count'] == 0 ? $lang->lang('ACP_UT_FIND_TEAMS_NONE') : $lang->lang('ACP_UT_FIND_TEAMS_FOUND', $teams['count'], $teams['message']);

							trigger_error($complete_message);
						break;
					}
				}
				else
				{
					switch ($action)
					{
						case 'purgeteam':
							$confirm_lang = $lang->lang('ACP_UT_PURGE_TEAM_IMAGES_CONFIRM');
						break;

						case 'findteams':
							$confirm_lang = $lang->lang('ACP_UT_FIND_TEAMS_CONFIRM');
						break;
					}

					# Display mode
					confirm_box(false, $confirm_lang, build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action,
						)
					));
				}
			}
		}

		$template->assign_vars(array(
			'UT_ENABLE_BOTTOM_LINK'	=> $config['ut_enable_bottom_link'],
			'UT_ENABLE_TOP_LINK'	=> $config['ut_enable_top_link'],
			'UT_IMAGE_SIZE'			=> $config['ut_image_size'],
			'UT_IMAGE_DIR'			=> $config['ut_image_dir'],
			'UT_MULTIPLE_TEAMS'		=> $config['ut_multiple_teams'],
			'UT_TAG_LENGTH'			=> $config['ut_tag_length'],

			'U_ACTION'				=> $this->u_action,
		));
	}

	/**
	 * Delete all un-used Ultimate Teams images
	 */
	private function purge_ultimateteams_images()
	{
		global $config, $db, $phpbb_container, $phpbb_root_path;

		$filesystem = $phpbb_container->get('filesystem');
		$ut_teams_table = $phpbb_container->getParameter('mrgoldy.ultimateteams.tables.ut_teams');

		$all_images = $inuse_images = array();

		# Set up a new finder
		$finder = new \phpbb\finder($filesystem, $phpbb_root_path);
		$finder->core_path($config['ut_image_dir'] . '/')
				->core_prefix('ut_');

		# Grab all the images
		$all_imgs = $finder->get_files();

		# Get only the image name of all the images we found, as we need that for comparison against the database entries
		foreach ($all_imgs as $image_path)
		{
			$image_path_array = explode('/', $image_path);
			$all_images[] = end($image_path_array);
		}

		# Grab all the current images in use for this mode
		$sql = 'SELECT team_image FROM ' . $ut_teams_table;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$inuse_images[] = $row['team_image'];
		}
		$db->sql_freeresult($result);

		# Find the difference in the arrays
		$delete_images_array = array_diff($all_images, $inuse_images);

		# Foreach difference, we delete the file. Be gone you fools!
		foreach ($delete_images_array as $delete_image)
		{
			$filesystem->remove($phpbb_root_path . $config['ut_image_dir'] . '/' . $delete_image);
		}
	}

	/**
	 * Find all Ultimate Team teams without leaders
	 */
	private function find_teams_without_leaders()
	{
		global $db, $phpbb_container;

		$helper = $phpbb_container->get('controller.helper');
		$ut_teams_table = $phpbb_container->getParameter('mrgoldy.ultimateteams.tables.ut_teams');
		$ut_correlation_table = $phpbb_container->getParameter('mrgoldy.ultimateteams.tables.ut_correlation');

		$sql = 'SELECT c.team_id, t.team_name
				FROM ' . $ut_correlation_table . ' c
				JOIN ' . $ut_teams_table . ' t
				WHERE c.team_id = t.team_id
					AND c.team_leader = 0
				GROUP BY c.team_id
				ORDER BY c.team_leader DESC';
		$result = $db->sql_query($sql);
		$rowset = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		$team_list = '';
		$i = 0;
		$len = count($rowset);

		if (empty($rowset))
		{
			return false;
		}
		else
		{
			foreach ($rowset as $row)
			{
				$team_list .= $i == 0 ? '<ul>' : '';
				$team_list .= '<li>';
				$team_list .= '<a href="' . $helper->route('mrgoldy_ultimateteams_view', array('team_id' => (int) $row['team_id'])) . '">';
				$team_list .= $row['team_name'];
				$team_list .= '</a></li>';
				$team_list .= $i == $len - 1 ? '</ul>' : '';

				$i++;
			}

			return array('count' => $len, 'message' => $team_list);
		}
	}
}
