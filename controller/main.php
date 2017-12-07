<?php
/**
 *
 * Ultimate Teams. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Mr. Goldy
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mrgoldy\ultimateteams\controller;

use mrgoldy\ultimateteams\constants;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Ultimate Teams main controller.
 */
class main
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\files\factory */
	protected $files_factory;

	/** @var \phpbb\filesystem/filesystem */
	protected $filesystem;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\textformatter\s9e\parser */
	protected $parser;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\textformatter\s9e\renderer */
	protected $renderer;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\textformatter\s9e\utils */
	protected $utils;

	/** @var string .php extension */
	protected $php_ext;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string Ultimate Teams teams table */
	protected $ut_teams_table;

	/** @var string Ultimate Teams correlation table */
	protected $ut_correlation_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth					$auth					Authentication object
	 * @param \phpbb\config\config				$config					Configuration object
	 * @param \phpbb\db\driver\driver_interface	$db						Database object
	 * @param \phpbb\files\factory				$files_factory			Files factory object
	 * @param \phpbb\filesystem/filesystem		$filesystem				Filesystem object
	 * @param \phpbb\controller\helper			$helper					Controller helper object
	 * @param \phpbb\language\language			$language				Language object
	 * @param \phpbb\log\log					$log					Log object
	 * @param \phpbb\notification\manager		$notification_manager	Notification manager
	 * @param \phpbb\pagination					$pagination				Pagination object
	 * @param \phpbb\textformatter\s9e\parser	$parser					Textformatter parser object
	 * @param \phpbb\path_helper				$path_helper			Path helper object
	 * @param \phpbb\textformatter\s9e\renderer	$renderer				Textformatter renderer object
	 * @param \phpbb\request\request			$request				Request object
	 * @param \phpbb\template\template			$template				Template object
	 * @param \phpbb\user						$user					User object
	 * @param \phpbb\textformatter\s9e\utils	$utils					Textformatter utils object
	 * @param string							$php_ext				.php extension
	 * @param string							$phpbb_root_path		phpBB root path
	 * @param string							$ut_teams_table			Ultimate Teams teams table
	 * @param string							$ut_correlation_table	Ultimate Teams correlation table
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\files\factory $files_factory,
		\phpbb\filesystem\filesystem $filesystem,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\notification\manager $notification_manager,
		\phpbb\pagination $pagination,
		\phpbb\textformatter\s9e\parser $parser,
		\phpbb\path_helper $path_helper,
		\phpbb\textformatter\s9e\renderer $renderer,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\textformatter\s9e\utils $utils,
		$php_ext,
		$phpbb_root_path,
		$ut_teams_table,
		$ut_correlation_table
	)
	{
		$this->auth					= $auth;
		$this->config				= $config;
		$this->db					= $db;
		$this->files_factory		= $files_factory;
		$this->filesystem			= $filesystem;
		$this->helper				= $helper;
		$this->lang					= $lang;
		$this->log					= $log;
		$this->notification_manager	= $notification_manager;
		$this->pagination			= $pagination;
		$this->parser				= $parser;
		$this->path_helper			= $path_helper;
		$this->renderer				= $renderer;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;
		$this->utils				= $utils;
		$this->php_ext				= $php_ext;
		$this->phpbb_root_path		= $phpbb_root_path;
		$this->ut_teams_table		= $ut_teams_table;
		$this->ut_correlation_table	= $ut_correlation_table;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function index($page)
	{
		# Can not view teams list
		if (!$this->auth->acl_get('u_ut_view'))
		{
			throw new \phpbb\exception\http_exception(403, $this->lang->lang('NOT_AUTHORISED'));
		}

		# Set start variable for pagination
		$start = (($page - 1) * $this->config['topics_per_page']);

		# Possible keys: N name | T tag | M members | Y type
		$default_key = 'n';
		$sort_key = $this->request->variable('sk', $default_key);
		$sort_dir = $this->request->variable('sd', 'a');

		$sql_order_by = '';
		switch ($sort_key)
		{
			case 'n':
				$sql_order_by = 't.team_name';
			break;
			case 't':
				$sql_order_by = 't.team_tag_clean';
			break;
			case 'm':
				$sql_order_by = 'member_count';
			break;
			case 'y':
				$sql_order_by = 't.team_type';
			break;
		}

		$sql_order_dir = $sort_dir == 'a' ? 'ASC' : 'DESC';

		# Grab all teams
		$sql_array = array(
			'SELECT'	=> 't.team_id, t.team_name, t.team_tag, t.team_colour, t.team_type, t.team_location, COUNT(distinct c.user_id) as member_count',

			'FROM'		=> array(
				$this->ut_teams_table		=> 't',
				$this->ut_correlation_table	=> 'c',
			),

			'WHERE'		=> 't.team_id = c.team_id AND c.user_status = ' . constants::UT_USER_STATUS_JOINED,
			'GROUP_BY'	=> 't.team_id',
			'ORDER_BY'	=> $sql_order_by . ' ' . $sql_order_dir,
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			# Assign template block variables for the teams.
			$this->template->assign_block_vars('teams', array(
				'COLOUR'	=> $row['team_colour'],
				'ID'		=> $row['team_id'],
				'MEMBERS'	=> $row['member_count'],
				'NAME'		=> $row['team_name'],
				'TAG'		=> $row['team_tag'],
				'TYPE'		=> $row['team_type'] == constants::UT_TEAM_TYPE_OPEN ? $this->lang->lang('UT_TEAM_TYPE_OPEN') : ($row['team_type'] == constants::UT_TEAM_TYPE_REQUEST ? $this->lang->lang('UT_TEAM_TYPE_REQUEST') : $this->lang->lang('UT_TEAM_TYPE_CLOSED')),

				'U_VIEW'	=> $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => (int) $row['team_id'])),
			));
		}
		# Free the results!
		$this->db->sql_freeresult($result);

		# Count the total number of teams
		$sql = 'SELECT COUNT(team_id) as team_count FROM ' . $this->ut_teams_table;
		$result = $this->db->sql_query($sql);
		$team_count = (int) $this->db->sql_fetchfield('team_count');
		$this->db->sql_freeresult($result);

		# Start pagination
		$this->pagination->generate_template_pagination(
			array(
				'routes' => array(
					'mrgoldy_ultimateteams_index',
					'mrgoldy_ultimateteams_indexpage',
				),
				'params' => array('sk' => $sort_key, 'sd' => $sort_dir),
			), 'pagination', 'page', $team_count, $this->config['topics_per_page'], $start);

		$this->template->assign_vars(array(
			'PAGE_NUMBER'		=> $this->pagination->on_page($team_count, $this->config['topics_per_page'], $start),
			'TOTAL_TEAMS'		=> $this->lang->lang('UT_TEAMS_TOTAL', $team_count),

			'S_TEAM_ADD'		=> $this->auth->acl_get('u_ut_add') && (empty($this->user->data['user_id'] && !$this->config['ut_multiple_teams'])) ? true : false,
			'S_TEAM_VIEW'		=> $this->auth->acl_get('u_ut_view_team'),

			'U_TEAM_ADD'		=> $this->helper->route('mrgoldy_ultimateteams_manage', array('mode' => 'add')),

			'U_SORT_MEMBERS'	=> $this->helper->route('mrgoldy_ultimateteams_index', array('sk' => 'm', 'sd' => (($sort_key == 'm' && $sort_dir == 'd') ? 'a' : 'd'))),
			'U_SORT_NAME'		=> $this->helper->route('mrgoldy_ultimateteams_index', array('sk' => 'n', 'sd' => (($sort_key == 'n' && $sort_dir == 'd') ? 'a' : 'd'))),
			'U_SORT_TAG'		=> $this->helper->route('mrgoldy_ultimateteams_index', array('sk' => 't', 'sd' => (($sort_key == 't' && $sort_dir == 'd') ? 'a' : 'd'))),
			'U_SORT_TYPE'		=> $this->helper->route('mrgoldy_ultimateteams_index', array('sk' => 'y', 'sd' => (($sort_key == 'y' && $sort_dir == 'd') ? 'a' : 'd'))),
		));

		# Breadcrumbs
		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $this->lang->lang('UT_TEAMS'),
			'U_VIEW_FORUM'	=> $this->helper->route('mrgoldy_ultimateteams_index'),
		));

		return $this->helper->render('ut_index.html', $this->lang->lang('UT_TEAMS'));
	}

	/**
	 * @param $mode Editing mode (add, edit, delete)
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function manage($mode, $team_id)
	{
		$submit = $this->request->is_set_post('submit');
		$cancel = $this->request->is_set_post('cancel');

		# Include phpBB's posting language file
		$this->lang->add_lang('posting');

		# Add BBCodes ABC

		# Grab the team info
		if ($mode === 'edit' || $mode === 'delete')
		{
			$sql = 'SELECT * FROM ' . $this->ut_teams_table . ' WHERE team_id = ' . (int) $team_id;
			$result = $this->db->sql_query($sql);
			$team_to_edit = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			# Check if there is a team for this ID.
			if (empty($team_to_edit))
			{
				throw new \phpbb\exception\http_exception(404, $this->lang->lang('UT_ERROR_NO_TEAM', $team_id));
			}

			# Grab all members for this team
			$team_members = $this->get_team_members((int) $team_id);
		}

		if (!empty($cancel))
		{
			switch ($mode)
			{
				case 'edit':
				case 'delete':
					return new RedirectResponse ($this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => (int) $team_id)), 302);
				break;

				case 'add':
					return new RedirectResponse ($this->helper->route('mrgoldy_ultimateteams_index'), 302);
				break;
			}
		}

		switch ($mode)
		{
			case 'delete':
				# Check delete permission
				if ((!$this->auth->acl_get('u_ut_delete') || !in_array($this->user->data['user_id'], $team_members['leaders'])) && !$this->auth->acl_get('m_ut_delete'))
				{
					throw new \phpbb\exception\http_exception(403, $this->lang->lang('NOT_AUTHORISED'));
				}

				if (confirm_box(true))
				{
					# Send out a notification to all team members

					# Delete the team image
					if ($this->filesystem->exists($this->phpbb_root_path . $this->config['ut_image_dir'] . '/' . $team_to_edit['team_image']))
					{
						$this->filesystem->remove($this->phpbb_root_path . $this->config['ut_image_dir'] . '/' . $team_to_edit['team_image']);
					}

					# Delete the team from the table
					$sql = 'DELETE FROM ' . $this->ut_teams_table . ' WHERE team_id = ' . (int) $team_id;
					$this->db->sql_query($sql);

					# Grab all users with this team as default team at the moment.
					$user_id_ary = array();

					$sql = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE user_team_id = ' . (int) $team_id;
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$user_id_ary = $row['user_id'];
					}
					$this->db->sql_freeresult($result);

					# Remove team_id from USERS_TABLE where team ID is current ID
					$sql = 'UPDATE ' . USERS_TABLE . ' SET user_team_id = 0 WHERE user_team_id = ' . (int) $team_id;
					$this->db->sql_query($sql);

					# Delete all members from the correlation table
					$sql = 'DELETE FROM ' . $this->ut_correlation_table . ' WHERE team_id = ' . (int) $team_id;
					$this->db->sql_query($sql);

					# Check if any user that had this team is default, also is in an other group, then set that as default
					$sql = 'SELECT user_id, team_id
							FROM ' . $this->ut_correlation_table . '
							GROUP BY user_id
							ORDER BY team_leader DESC, team_name ASC
							WHERE ' . $this->db->sql_in_set('user_id', $user_id_ary) . '
								AND user_status = 1';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$sql = 'UPDATE ' . USERS_TABLE . ' SET user_team_id = ' . (int) $row['team_id'] . ' WHERE user_id = ' . (int) $row['user_id'];
						$this->db->sql_query($sql);
					}
					$this->db->sql_freeresult($result);

					# Add it to the log
					$log_mode = in_array($this->user->data['user_id'], $team_members['leaders']) ? 'user' : 'mod';
					$this->log->add($log_mode, $this->user->data['user_id'], $this->user->data['user_ip'], 'ACP_UT_LOG_TEAM_DELETED', time(), array('reportee_id' => $this->user->data['user_id'], $team_to_edit['team_name']));

					# Show a success message
					trigger_error($this->lang->lang('UT_TEAM_DELETED'));
				}
				else
				{
					confirm_box(false, $this->lang->lang('UT_TEAM_DELETE_CONFIRM', $team_to_edit['team_name']), build_hidden_fields(array(
						'mode'		=> $mode,
						'team_id'	=> (int) $team_id,
					)));
				}
			break;

			case 'add':
				# Check add permission
				if (!$this->auth->acl_get('u_ut_add'))
				{
					throw new \phpbb\exception\http_exception(403, $this->lang->lang('NOT_AUTHORISED'));
				}

				# Check if user is not already in a team, if multiple teams is disallowed.
				if (!empty($this->user->data['user_team_id']) && !$this->config['ut_multiple_teams'])
				{
					throw new \phpbb\exception\http_exception(403, $this->lang->lang('UT_ERROR_ALREADY_IN_TEAM'));
				}
			break;

			case 'edit':
				# Check edit permission
				if ((!$this->auth->acl_get('u_ut_edit') || !in_array($this->user->data['user_id'], $team_members['leaders'])) && !$this->auth->acl_get('m_ut_edit'))
				{
					throw new \phpbb\exception\http_exception(403, $this->lang->lang('NOT_AUTHORISED'));
				}
			break;
		}

		# Add a form key for security
		add_form_key('team_update');

		# Request user input
		if (!empty($submit))
		{
			$team_description = $this->request->variable('team_description', '', true);
			$team_description = htmlspecialchars_decode($team_description, ENT_COMPAT);
			$this->config['allow_bbcode'] ? $this->parser->enable_bbcodes() : $this->parser->disable_bbcodes();
			$this->config['allow_smilies'] ? $this->parser->enable_smilies() : $this->parser->disable_smilies();
			$this->config['allow_post_links'] ? $this->parser->enable_magic_url() : $this->parser->disable_magic_url();

			$team_to_update = array(
				'team_colour'		=> $this->request->variable('team_colour', ''),
				'team_description'	=> $this->parser->parse($team_description),
				'team_location'		=> $this->request->variable('team_location', '', true),
				'team_website'		=> $this->request->variable('team_website', '', true),
				'team_name'			=> $this->request->variable('team_name', '', true),
				'team_tag'			=> $this->request->variable('team_tag', '', true),
				'team_tag_clean'	=> preg_replace('/[^A-Za-z0-9\-]/', '', $this->request->variable('team_tag', '', true)),
				'team_type'			=> $this->request->variable('team_type', 0),
			);

			# Begin error checking
				# Check form key for security
				if (!check_form_key('team_update'))
				{
					$errors[] = $this->lang->lang('FORM_INVALID');
				}

				# Check if there is a team name ABC
				if (empty($team_to_update['team_name']) || utf8_strlen($team_to_update['team_name']) > 60)
				{
					$errors[] = (!utf8_strlen($team_to_update['team_name'])) ? $this->lang->lang('UT_ERROR_NAME_NONE') : $this->lang->lang('UT_ERROR_NAME', utf8_strlen($team_to_update['team_name']));
				}

				# Check if the team name is not already in use
				$team_name = utf8_clean_string($team_to_update['team_name']);
				$sql = 'SELECT team_name
						FROM ' . $this->ut_teams_table . "
						WHERE LOWER(team_name) = '" . $this->db->sql_escape(utf8_strtolower($team_name)) . "'";
				$sql .= $mode === 'edit' ? ' AND team_id != ' . (int) $team_id : '';
				$result = $this->db->sql_query($sql);
				$taken_name = $this->db->sql_fetchfield('team_name');
				$this->db->sql_freeresult($result);

				if (!empty($taken_name))
				{
					$errors[] = $this->lang->lang('UT_ERROR_NAME_TAKEN', $taken_name);
				}

				# Check if there is a valid colour
				if (empty($team_to_update['team_colour']))
				{
					$errors[] = $this->lang->lang('WRONG_DATA_COLOUR');
				}

				# Check if there is a valid tag
				if (empty($team_to_update['team_tag']) || utf8_strlen($team_to_update['team_tag']) > $this->config['ut_tag_length'])
				{
					$errors[] = (!utf8_strlen($team_to_update['team_tag'])) ? $this->lang->lang('UT_ERROR_TAG_NONE') : $this->lang->lang('UT_ERROR_TAG', $this->config['ut_tag_length'], utf8_strlen($team_to_update['team_tag']));
				}

				# Check if the tag is not already in use
				$sql = 'SELECT team_tag
						FROM ' . $this->ut_teams_table . "
						WHERE LOWER(team_tag_clean) = '" . $this->db->sql_escape(utf8_strtolower($team_to_update['team_tag_clean'])) . "'";
				$sql .= $mode === 'edit' ? ' AND team_id != ' . (int) $team_id : '';
				$result = $this->db->sql_query($sql);
				$taken_tag = $this->db->sql_fetchfield('team_tag');
				$this->db->sql_freeresult($result);

				if (!empty($taken_tag))
				{
					$errors[] = $this->lang->lang('UT_ERROR_TAG_TAKEN', $taken_tag);
				}

				# Get an instance of the files upload class
				$upload = $this->files_factory->get('upload')
						-> set_max_filesize($this->config['ut_image_size'] * 1000)
						-> set_allowed_extensions(array('png', 'jpg', 'jpeg', 'gif'));

				$upload_file = $upload->handle_upload('files.types.form', 'team_image');

				# Check for errors, only when adding a new file
				if ($upload_file->get('uploadname'))
				{
					if (!empty($upload_file->error) || !$upload_file->is_image() || !$upload_file->is_uploaded() || $upload_file->init_error())
					{
						$upload_file->remove();
						foreach ($upload_file->error as $file_error)
						{
							$errors[] = $file_error;
						}
					}
				}
			# End error checking

			if (!empty($submit) && empty($errors))
			{
				# Upload the team image, if one is supplied
				if ($upload_file->get('uploadname'))
				{
					# If editing and uploading a new file, delete the old file
					if ($mode === 'edit' && !empty($team_to_edit['team_image']) && $this->filesystem->exists($this->phpbb_root_path . $this->config['ut_image_dir'] . '/' . $team_to_edit['team_image']))
					{
						$this->filesystem->remove($this->phpbb_root_path . $this->config['ut_image_dir'] . '/' . $team_to_edit['team_image']);
					}

					# We're adding the new file
					$upload_file->clean_filename('unique_ext', 'ut_');
					$upload_file->move_file($this->config['ut_image_dir'], true, true, 0644);
					@chmod($this->phpbb_root_path . $this->config['ut_image_dir'] . $upload_file->get('realname'), 0644);

					$team_to_update['team_image'] = $upload_file->get('realname');
				}

				# Insert or update the team
				if ($mode === 'add')
				{
					$sql = 'INSERT INTO ' . $this->ut_teams_table . ' ' . $this->db->sql_build_array('INSERT', $team_to_update);
				}
				else
				{
					$sql = 'UPDATE ' . $this->ut_teams_table . ' SET ' . $this->db->sql_build_array('UPDATE', $team_to_update) . ' WHERE team_id = ' . (int) $team_id;
				}
				$this->db->sql_query($sql);
				$updated_team_id = $mode === 'add' ? $this->db->sql_nextid() : $team_id;

				# Add the user correlation when creating a new team
				if ($mode === 'add')
				{
					$user_to_add = array(
						'team_id'		=> (int) $updated_team_id,
						'user_id'		=> (int) $this->user->data['user_id'],
						'team_leader'	=> 1,
						'user_status'	=> constants::UT_USER_STATUS_JOINED,
					);
					$sql = 'INSERT INTO ' . $this->ut_correlation_table . ' ' . $this->db->sql_build_array('INSERT', $user_to_add);
					$this->db->sql_query($sql);

					$sql = 'UPDATE ' . USERS_TABLE . ' SET user_team_id = ' . (int) $updated_team_id . ' WHERE user_id = ' . (int) $this->user->data['user_id'];
					$this->db->sql_query($sql);
				}

				# Add it to the log
				$log_mode = $mode === 'add' ? 'user' : (in_array($this->user->data['user_id'], $team_members['leaders']) ? 'user' : 'mod');
				$this->log->add($log_mode, $this->user->data['user_id'], $this->user->data['user_ip'], 'ACP_UT_LOG_TEAM_' . strtoupper($mode) . 'ED', time(), array('reportee_id' => $this->user->data['user_id'], $team_to_update['team_name']));

				# Send confirmation message
				$view_team_message = '<a href="' . $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => (int) $updated_team_id)) . '">' . $this->lang->lang('UT_TEAM_VIEW') . '</a>';
				trigger_error($this->lang->lang('UT_TEAM_' . strtoupper($mode) . 'ED') . '<br /><br />' . $view_team_message);
			}
		}

		$this->template->assign_vars(array(
			'TEAM_COLOUR'		=> !empty($submit) ? $team_to_update['team_colour'] : (!empty($team_to_edit['team_colour']) ? $team_to_edit['team_colour'] : ''),
			'TEAM_DESCRIPTION'	=> !empty($submit) ? $this->utils->unparse($team_to_update['team_description']) : ($mode === 'edit' ? $this->utils->unparse($team_to_edit['team_description']) : ''),
			'TEAM_ID'			=> !empty($team_id) ? (int) $team_id : '',
			'TEAM_LOCATION'		=> !empty($submit) ? $team_to_update['team_location'] : (!empty($team_to_edit['team_location']) ? $team_to_edit['team_location'] : ''),
			'TEAM_IMAGE'		=> !empty($team_to_edit['team_image']) ? $this->path_helper->get_web_root_path() . $this->config['ut_image_dir'] . '/' . $team_to_edit['team_image'] : '',
			'TEAM_IMAGE_NAME'	=> !empty($team_to_edit['team_image']) ? $team_to_edit['team_image'] : '',
			'TEAM_MEMBERS'		=> $mode === 'edit' ? (int) $team_members['count'] : 0,
			'TEAM_NAME'			=> !empty($submit) ? $team_to_update['team_name'] : (!empty($team_to_edit['team_name']) ? $team_to_edit['team_name'] : ''),
			'TEAM_TAG'			=> !empty($submit) ? $team_to_update['team_tag'] : (!empty($team_to_edit['team_tag']) ? $team_to_edit['team_tag'] : ''),
			'TEAM_TYPE'			=> !empty($submit) ? $team_to_update['team_type'] : (!empty($team_to_edit['team_type']) ? $team_to_edit['team_type'] : constants::UT_TEAM_TYPE_REQUEST),
			'TEAM_WEBSITE'		=> !empty($submit) ? $team_to_update['team_website'] : (!empty($team_to_edit['team_website']) ? $team_to_edit['team_website'] : ''),

			'BBCODE_STATUS'		=> $this->config['allow_bbcode'] ? $this->lang->lang('BBCODE_IS_ON', '<a href="' . $this->helper->route('phpbb_help_bbcode_controller') . '">', '</a>') : $this->lang->lang('BBCODE_IS_OFF', '<a href="' . $this->helper->route('phpbb_help_bbcode_controller') . '">', '</a>'),
			'SMILIES_STATUS'	=> $this->config['allow_smilies'] ? $this->lang->lang('SMILIES_ARE_ON') : $this->lang->lang('SMILIES_ARE_OFF'),
			'URL_STATUS'		=> $this->config['allow_post_links'] ? $this->lang->lang('URL_IS_ON') : $this->lang->lang('URL_IS_OFF'),

			'S_TEAM_ADD'		=> $mode === 'add' ? true : false,
			'S_TEAM_EDIT'		=> $mode === 'edit' ? true : false,

			'S_TEAM_TYPE_VALUE_OPEN'	=> constants::UT_TEAM_TYPE_OPEN,
			'S_TEAM_TYPE_VALUE_REQUEST'	=> constants::UT_TEAM_TYPE_REQUEST,
			'S_TEAM_TYPE_VALUE_CLOSED'	=> constants::UT_TEAM_TYPE_CLOSED,

			'U_TEAM_ADD_ACTION'		=> $this->helper->route('mrgoldy_ultimateteams_manage', array('mode' => 'add')),
			'U_TEAM_EDIT_ACTION'	=> $this->helper->route('mrgoldy_ultimateteams_manage', array('mode' => 'edit', 'team_id' => (int) $team_id)),
			'U_USER_INVITE_ACTION'	=> $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => 'invite', 'subject_id' => 0, 'action' => 'send')),

			'S_ERROR'			=> !empty($errors),
			'ERROR_MSG'			=> !empty($errors) ? implode('<br />', $errors) : '',
		));

		# Breadcrumbs
		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $this->lang->lang('UT_TEAMS'),
			'U_VIEW_FORUM'	=> $this->helper->route('mrgoldy_ultimateteams_index'),
		));

		if ($mode === 'edit')
		{
			$this->template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $team_to_edit['team_name'],
				'U_VIEW_FORUM'	=> $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => (int) $team_id)),
			));
		}

		$page_title = $mode === 'edit' ? $this->lang->lang('UT_TEAM_EDIT') : $this->lang->lang('UT_TEAM_ADD');
		return $this->helper->render('ut_manage.html', $page_title);
	}

	/**
	 * @param $id Team id
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function view($team_id)
	{
		# Grab the team info
		$sql = 'SELECT t.*
				FROM ' . $this->ut_teams_table . ' t
				WHERE t.team_id = ' . (int) $team_id;
		$result = $this->db->sql_query($sql);
		$team = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		# Check if there is an ID for the given ID.
		if (empty($team))
		{
			throw new \phpbb\exception\http_exception(404, $this->lang->lang('UT_ERROR_NO_TEAM', $team_id));
		}

		# Grab all team members and applicants
		$team_members = $this->get_team_members((int) $team_id);

		# Set up the LEAVE link.
		if (!empty($team_members['user_status']))
		{
			switch ($team_members['user_status'])
			{
				case constants::UT_USER_STATUS_JOINED:
					$leave_url = $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => 'leave', 'subject_id' => (int) $this->user->data['user_id']));
				break;

				case constants::UT_USER_STATUS_REQUESTED:
					$leave_url = $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => 'request', 'subject_id' => (int) $this->user->data['user_id'], 'action' => 'withdraw'));
				break;

				case constants::UT_USER_STATUS_INVITED:
					$leave_url = $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => 'invite', 'subject_id' => (int) $this->user->data['user_id'], 'action' => 'deny'));
				break;
			}
		}

		$this->template->assign_vars(array(
			'TEAM_COLOUR'		=> $team['team_colour'],
			'TEAM_DESCRIPTION'	=> $this->renderer->render($team['team_description']),
			'TEAM_ID'			=> $team_id,
			'TEAM_LOCATION'		=> !empty($team['team_location']) ? $team['team_location'] : '',
			'TEAM_IMAGE'		=> !empty($team['team_image']) ? $this->path_helper->get_web_root_path() . $this->config['ut_image_dir'] . '/' . $team['team_image'] : '',
			'TEAM_IMAGE_NAME'	=> !empty($team['team_image']) ? $team['team_image'] : '',
			'TEAM_MEMBERS'		=> $team_members['count'],
			'TEAM_NAME'			=> $team['team_name'],
			'TEAM_TAG'			=> $team['team_tag'],
			'TEAM_TYPE'			=> $team['team_type'] == constants::UT_TEAM_TYPE_OPEN ? $this->lang->lang('UT_TEAM_TYPE_OPEN') : ($team['team_type'] == constants::UT_TEAM_TYPE_REQUEST ? $this->lang->lang('UT_TEAM_TYPE_REQUEST') : $this->lang->lang('UT_TEAM_TYPE_CLOSED')),
			'TEAM_TYPE_EXPLAIN'	=> $team['team_type'] == constants::UT_TEAM_TYPE_OPEN ? $this->lang->lang('UT_TEAM_TYPE_OPEN_EXPLAIN') : ($team['team_type'] == constants::UT_TEAM_TYPE_REQUEST ? $this->lang->lang('UT_TEAM_TYPE_REQUEST_EXPLAIN') : $this->lang->lang('UT_TEAM_TYPE_CLOSED_EXPLAIN')),
			'TEAM_WEBSITE'		=> !empty($team['team_website']) ? $team['team_website'] : '',

			'S_TEAM_DELETE'		=> (($this->auth->acl_get('u_ut_delete') && in_array($this->user->data['user_id'], $team_members['leaders'])) || $this->auth->acl_get('m_ut_delete')) ? true : false,
			'S_TEAM_EDIT'		=> (($this->auth->acl_get('u_ut_edit') && in_array($this->user->data['user_id'], $team_members['leaders'])) || $this->auth->acl_get('m_ut_edit')) ? true : false,
			'S_USER_INVITED'	=> $team_members['user_status'] == constants::UT_USER_STATUS_INVITED ? true : false,
			'S_USER_JOIN'		=> ($team['team_type'] != constants::UT_TEAM_TYPE_CLOSED && empty($team_members['user_status']) && $this->user->data['user_id'] != ANONYMOUS) ? true : false,
			'S_USER_LEAVE'		=> ($team_members['user_status'] == constants::UT_USER_STATUS_JOINED || $team_members['user_status'] == constants::UT_USER_STATUS_REQUESTED) ? true : false,

			'U_TEAM_DELETE'			=> $this->helper->route('mrgoldy_ultimateteams_manage', array('mode' => 'delete', 'team_id' => (int) $team_id)),
			'U_TEAM_EDIT'			=> $this->helper->route('mrgoldy_ultimateteams_manage', array('mode' => 'edit', 'team_id' => (int) $team_id)),
			'U_USER_INVITE_ACCEPT'	=> $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => 'invite', 'subject_id' => $this->user->data['user_id'], 'action' => 'accept')),
			'U_USER_INVITE_DENY'	=> $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => 'invite', 'subject_id' => $this->user->data['user_id'], 'action' => 'deny')),
			'U_USER_JOIN'			=> $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => 'request', 'subject_id' => $this->user->data['user_id'], 'action' => 'apply')),
			'U_USER_LEAVE'			=> !empty($team_members['user_status']) ? $leave_url : '',
		));

		# Breadcrumbs
		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $this->lang->lang('UT_TEAMS'),
			'U_VIEW_FORUM'	=> $this->helper->route('mrgoldy_ultimateteams_index'),
		));
		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $team['team_name'],
			'U_VIEW_FORUM'	=> $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => (int) $team_id)),
		));

		return $this->helper->render('ut_view.html', $this->lang->lang('UT_TEAM_VIEWING') . ' - ' . $team['team_name']);
	}

	private function get_team_members($team_id)
	{
		$team_leaders_array = array();
		$team_members_count = $user_status = 0;

		$sql = 'SELECT c.team_leader, c.user_status, u.user_id, u.username, u.user_colour
				FROM ' . $this->ut_correlation_table . ' c
				JOIN ' . USERS_TABLE . ' u
				WHERE c.user_id = u.user_id
					AND c.team_id = ' . (int) $team_id . '
				ORDER BY c.team_leader DESC, u.username ASC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			# Check if user is in this team, in any sort of status
			if ($row['user_id'] == $this->user->data['user_id'])
			{
				$user_status = $row['user_status'];
			}

			switch ($row['user_status'])
			{
				case constants::UT_USER_STATUS_JOINED:
					# Increase member count
					$team_members_count++;

					# Assign template block variables for members
					$this->template->assign_block_vars('members', array(
						'COLOUR'		=> $row['user_colour'],
						'FULL'			=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
						'ID'			=> $row['user_id'],
						'NAME'			=> $row['username'],

						'S_IS_LEADER'	=> !empty($row['team_leader']) ? true : false,

						'U_PROMOTE'		=> $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => 'promote', 'subject_id' => (int) $row['user_id'])),
						'U_DEMOTE'		=> $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => 'demote', 'subject_id' => (int) $row['user_id'])),
						'U_KICK'		=> $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => 'leave', 'subject_id' => (int) $row['user_id'])),
					));

					if (!empty($row['team_leader']))
					{
						$team_leaders_array[] = $row['user_id'];
					}
				break;

				case constants::UT_USER_STATUS_REQUESTED:
					# Assign template block variables for applicants
					$this->template->assign_block_vars('applicants', array(
						'COLOUR'		=> $row['user_colour'],
						'FULL'			=> !empty($row) ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : '',
						'ID'			=> $row['user_id'],
						'NAME'			=> $row['username'],

						'U_ACCEPT'		=> $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => 'request', 'subject_id' => (int) $row['user_id'], 'action' => 'accept')),
						'U_DENY'		=> $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => 'request', 'subject_id' => (int) $row['user_id'], 'action' => 'deny')),
					));
				break;

				case constants::UT_USER_STATUS_INVITED:
					# Assign template block variables for invitees
					$this->template->assign_block_vars('invitees', array(
						'COLOUR'		=> $row['user_colour'],
						'FULL'			=> !empty($row) ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : '',
						'ID'			=> $row['user_id'],
						'NAME'			=> $row['username'],

						'U_WITHDRAW'	=> $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => 'invite', 'subject_id' => (int) $row['user_id'], 'action' => 'withdraw')),
					));
				break;
			}
		}
		$this->db->sql_freeresult($result);

		return array('count' => (int) $team_members_count, 'leaders' => $team_leaders_array, 'user_status' => $user_status);
	}

	public function member($mode, $team_id, $subject_id, $action)
	{
		# Grab team leaders from this team and check if current user is a leader.
		$team_leaders = array();
		$sql = 'SELECT user_id FROM ' . $this->ut_correlation_table . ' WHERE team_leader = 1 AND team_id = ' . (int) $team_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$team_leaders[] = $row['user_id'];
		}
		$this->db->sql_freeresult($result);
		$is_leader = in_array($this->user->data['user_id'], $team_leaders) ? true : false;

		# Grab team name and type (OPEN | REQUEST | CLOSED)
		$sql = 'SELECT team_name, team_type FROM ' . $this->ut_teams_table . ' WHERE team_id = ' . (int) $team_id;
		$result = $this->db->sql_query($sql);
		$team = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		switch ($mode)
		{
			case 'request':
			# Switch the action variable (APPLY | WITHDRAW | DENY | ACCEPT)
				switch ($action)
				{
					case 'apply':
						# Check if current team is not a CLOSED team
						if ($team['team_type'] == constants::UT_TEAM_TYPE_CLOSED)
						{
							trigger_error($this->lang->lang('UT_ERROR_TEAM_CLOSED'));
						}

						# Check if subject is not already in an other team and multiple teams is disabled
						if (!empty($this->user->data['user_team_id']) && !$this->config['ut_multiple_teams'])
						{
							trigger_error($this->lang->lang('UT_ERROR_ALREADY_IN_TEAM'));
						}
					break;

					/*
					case 'withdraw':
					break;
					 */

					case 'deny':
						# Check if current user is team leader and has the permission to edit the team.
						if (!$is_leader || !$this->auth->acl_get('u_ut_edit'))
						{
							trigger_error($this->lang->lang('NOT_AUTHORISED'));
						}
					break;

					case 'accept':
						# Check if current user is team leader and has the permission to edit the team.
						if (!$is_leader || !$this->auth->acl_get('u_ut_edit'))
						{
							trigger_error($this->lang->lang('NOT_AUTHORISED'));
						}

						# Check if subject is not already in an other team and multiple teams is disabled
						if (!empty($this->user->data['user_id']) && !$this->config['ut_multiple_teams'])
						{
							trigger_error($this->lang->lang('UT_ERROR_ALREADY_OTHER_TEAM'));
						}
					break;
				}

				if ($this->request->is_ajax())
				{
					if (confirm_box(true))
					{
						switch ($action)
						{
							case 'apply':
								$user_status = $team['team_type'] == constants::UT_TEAM_TYPE_OPEN ? constants::UT_USER_STATUS_JOINED : constants::UT_USER_STATUS_REQUESTED;
								$this->add_correlation($team_id, $subject_id, $user_status);
								$refresh_url = $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => $team_id));

								# Send a notification to the leaders
								# send_ut_notification($event, $team_id, $team_name, $actionee_id, $recipients_array)
								$notification_mode = $team['team_type'] == constants::UT_TEAM_TYPE_OPEN ? 'request_joined' : 'request_send';
								$this->send_ut_notification($notification_mode, $team_id, $team['team_name'], $this->user->data['user_id'], array($subject_id));
							break;

							case 'withdraw':
								$this->remove_correlation($team_id, $subject_id);
								$refresh_url = $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => $team_id));
							break;

							case 'deny':
								$this->remove_correlation($team_id, $subject_id);
								$refresh_url = $this->helper->route('mrgoldy_ultimateteams_manage', array('team_id' => $team_id, 'mode' => 'edit'));

								# Send a notification to the subject
								# send_ut_notification($event, $team_id, $team_name, $actionee_id, $recipients_array)
								$this->send_ut_notification('request_denied', $team_id, $team['team_name'], $this->user->data['user_id'], array($subject_id));
							break;

							case 'accept':
								$this->update_correlation($team_id, $subject_id, constants::UT_USER_STATUS_JOINED);
								$refresh_url = $this->helper->route('mrgoldy_ultimateteams_manage', array('team_id' => $team_id, 'mode' => 'edit'));

								# Send a notification to the subject
								# send_ut_notification($event, $team_id, $team_name, $actionee_id, $recipients_array)
								$this->send_ut_notification('request_accepted', $team_id, $team['team_name'], $this->user->data['user_id'], array($subject_id));
							break;
						}

						# Show success message
						$success_language = ($team['team_type'] == constants::UT_TEAM_TYPE_OPEN && $action === 'apply') ? $this->lang->lang('UT_REQUEST_JOIN_SUCCESS') : $this->lang->lang('UT_REQUEST_' . strtoupper($action) . '_SUCCESS');
						return new JsonResponse(array(
							'MESSAGE_TITLE'	=> $this->lang->lang('CONFIRM'),
							'MESSAGE_TEXT'	=> $success_language,
							'REFRESH_DATA'	=> array('url' => $refresh_url, 'time' => 3),
						));
					}
					else
					{
						$confirm_language = ($team['team_type'] == constants::UT_TEAM_TYPE_OPEN && $action === 'apply') ? $this->lang->lang('UT_REQUEST_JOIN_CONFIRM') : $this->lang->lang('UT_REQUEST_' . strtoupper($action) . '_CONFIRM');

						# Display mode
						confirm_box(false, $confirm_language, build_hidden_fields(array(
							'subject_id'	=> $subject_id,
							'team_id'		=> $team_id,
							'action'		=> $action,
							'mode'			=> $mode,
						)), 'confirm_body.html', $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => $mode, 'subject_id' => $subject_id, 'action' => $action)));
					}
				}
			break;

			case 'invite':
				# Switch the action variable (SEND | WITHDRAW | DENY | ACCEPT)
				switch ($action)
				{
					case 'send':
						# Check if current user is team leader and has the permission to edit the team.
						if (!$is_leader || !$this->auth->acl_get('u_ut_edit'))
						{
							trigger_error($this->lang->lang('NOT_AUTHORISED'));
						}
					break;

					case 'withdraw':
						# Check if current user is team leader and has the permission to edit the team.
						if (!$is_leader || !$this->auth->acl_get('u_ut_edit'))
						{
							trigger_error($this->lang->lang('NOT_AUTHORISED'));
						}
					break;

					/*
					case 'deny':
					break;
					*/

					case 'accept':
						# Check if subject is not already in an other team and multiple teams is disabled
						if (!empty($this->user->data['user_team_id']) && !$this->config['ut_multiple_teams'])
						{
							trigger_error($this->lang->lang('UT_ERROR_ALREADY_OTHER_TEAM'));
						}
					break;
				}

				if ($this->request->is_ajax())
				{
					if (confirm_box(true))
					{
						switch ($action)
						{
							case 'send':
								$this->add_correlation($team_id, $subject_id, constants::UT_USER_STATUS_INVITED);
								$refresh_url = $this->helper->route('mrgoldy_ultimateteams_manage', array('team_id' => $team_id, 'mode' => 'edit'));

								# Send notification to the subject
								# send_ut_notification($event, $team_id, $team_name, $actionee_id, $recipients_array)
								$this->send_ut_notification('invite_send', $team_id, $team['team_name'], $this->user->data['user_id'], array($subject_id));
							break;

							case 'withdraw':
								$this->remove_correlation($team_id, $subject_id);
								$refresh_url = $this->helper->route('mrgoldy_ultimateteams_manage', array('team_id' => $team_id, 'mode' => 'edit'));
							break;

							case 'deny':
								$this->remove_correlation($team_id, $subject_id);
								$refresh_url = $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => $team_id));

								# Send notification to the subject
								# send_ut_notification($event, $team_id, $team_name, $actionee_id, $recipients_array)
								$this->send_ut_notification('invite_denied', $team_id, $team['team_name'], $this->user->data['user_id'], $team_leaders);
							break;

							case 'accept':
								$this->update_correlation($team_id, $subject_id, constants::UT_USER_STATUS_JOINED);
								$refresh_url = $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => $team_id));

								# Send notification to the subject
								# send_ut_notification($event, $team_id, $team_name, $actionee_id, $recipients_array)
								$this->send_ut_notification('invite_accepted', $team_id, $team['team_name'], $this->user->data['user_id'], $team_leaders);
							break;
						}

						# Show success message
						$success_language = $this->lang->lang('UT_INVITE_' . strtoupper($action) . '_SUCCESS');
						return new JsonResponse(array(
							'MESSAGE_TITLE'	=> $this->lang->lang('CONFIRM'),
							'MESSAGE_TEXT'	=> $success_language,
							'REFRESH_DATA'	=> array('url' => $refresh_url, 'time' => 3),
						));
					}
					else
					{
						if ($action === 'send')
						{
							$username = $this->request->variable('invitee_username', '', true);
							# Grab the User ID from the input username
							$sql = 'SELECT user_id, user_team_id
									FROM ' . USERS_TABLE . "
									WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'
										AND user_type <> " . USER_IGNORE;
							$result = $this->db->sql_query($sql);
							$row = $this->db->sql_fetchrow($result);
							$this->db->sql_freeresult($result);

							if (empty($row['user_id']))
							{
								trigger_error($this->lang->lang('NO_USER'));
							}

							# Check if subject is not already in an other team and multiple teams is disabled
							if (!empty($row['user_team_id']) && !$this->config['ut_multiple_teams'])
							{
								trigger_error($this->lang->lang('UT_ERROR_ALREADY_OTHER_TEAM'));
							}

							# Then set subject ID to this user's id
							$subject_id = (int) $row['user_id'];
						}
						$confirm_language = $this->lang->lang('UT_INVITE_' . strtoupper($action) . '_CONFIRM');

						# Display mode
						confirm_box(false, $confirm_language, build_hidden_fields(array(
							'subject_id'	=> $subject_id,
							'team_id'		=> $team_id,
							'action'		=> $action,
							'mode'			=> $mode,
						)), 'confirm_body.html', $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => $mode, 'subject_id' => $subject_id, 'action' => $action)));
					}
				}
			break;

			case 'leave':
				# Make sure subject is in this team.
				$user_is_member = $this->check_membership($team_id, $subject_id);

				if (!$user_is_member)
				{
					trigger_error($this->lang->lang('UT_ERROR_NOT_MEMBER', $team['team_name']));
				}

				# Check if the subject ID is the same as current user ID, IF so: User is leaving, ELSE user is kicked
				if ($this->user->data['user_id'] == $subject_id)
				{
					if ($this->request->is_ajax())
					{
						if (confirm_box(true))
						{
							# Remove the correlation
							$this->remove_correlation($team_id, $subject_id);

							# Check if it's default group, otherwise update it
							$this->update_default_team($team_id, $subject_id);

							# Send out a notification to the leaders
							# send_ut_notification($event, $team_id, $team_name, $actionee_id, $recipients_array)
							$this->send_ut_notification('left', $team_id, $team['team_name'], $this->user->data['user_id'], $team_leaders);

							# Show success message
							$success_language = $this->lang->lang('UT_LEAVE_SELF_SUCCESS', $team['team_name']);
							return new JsonResponse(array(
								'MESSAGE_TITLE'	=> $this->lang->lang('CONFIRM'),
								'MESSAGE_TEXT'	=> $success_language,
								'REFRESH_DATA'	=> array('url' => $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => $team_id)), 'time' => 3),
							));
						}
						else
						{
							# Check if user is not the only leader trying to leave.
							if (in_array($subject_id, $team_leaders) && count($team_leaders) == 1)
							{
								trigger_error($this->lang->lang('UT_ERROR_ONLY_LEADER'));
							}

							# Display mode
							confirm_box(false, $confirm_language, build_hidden_fields(array(
								'subject_id'	=> $subject_id,
								'team_id'		=> $team_id,
								'action'		=> $action,
								'mode'			=> $mode,
							)), 'confirm_body.html', $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => $mode, 'subject_id' => $subject_id, 'action' => $action)));
						}
					}
				}
				else
				{
					if ($this->request->is_ajax())
					{
						if (confirm_box(true))
						{
							# Remove the correlation
							$this->remove_correlation($team_id, $subject_id);

							# Check if it's default group, otherwise update it.
							$this->update_default_team($team_id, $subject_id);

							# Send out a notification to the subject
							# send_ut_notification($event, $team_id, $team_name, $actionee_id, $recipients_array)
							$this->send_ut_notification('kicked', $team_id, $team['team_name'], $this->user->data['user_id'], array($subject_id));

							# Show success message
							$success_language = $this->lang->lang('UT_LEAVE_KICK_SUCCESS', $team['team_name']);
							return new JsonResponse(array(
								'MESSAGE_TITLE'	=> $this->lang->lang('CONFIRM'),
								'MESSAGE_TEXT'	=> $success_language,
								'REFRESH_DATA'	=> array('url' => $this->helper->route('mrgoldy_ultimateteams_manage', array('team_id' => $team_id, 'mode' => 'edit')), 'time' => 3),
							));
						}
						else
						{
							# Check if current user is team leader and has the permission to edit the team.
							if (!$is_leader || !$this->auth->acl_get('u_ut_edit'))
							{
								trigger_error($this->lang->lang('NOT_AUTHORISED'));
							}

							# Display mode
							confirm_box(false, $this->lang->lang('UT_LEAVE_KICK_CONFIRM', $team['team_name']), build_hidden_fields(array(
								'subject_id'	=> $subject_id,
								'team_id'		=> $team_id,
								'mode'			=> $mode,
							)));
						}
					}
				}
			break;

			# Promote a user to being a team leader.
			case 'promote':
				if ($this->request->is_ajax())
				{
					if (confirm_box(true))
					{
						# Promote user to leader
						$sql = 'UPDATE ' . $this->ut_correlation_table . ' SET team_leader = 1 WHERE team_id = ' . (int) $team_id . ' AND user_id = ' . (int) $subject_id;
						$this->db->sql_query($sql);

						# Send out a notification to the subject
						# send_ut_notification($event, $team_id, $team_name, $actionee_id, $recipients_array)
						$this->send_ut_notification('promoted', $team_id, $team['team_name'], $this->user->data['user_id'], array($subject_id));

						# Show success message
						$success_language = $this->lang->lang('UT_LEADER_PROMOTE_SUCCESS');
						return new JsonResponse(array(
							'MESSAGE_TITLE'	=> $this->lang->lang('CONFIRM'),
							'MESSAGE_TEXT'	=> $success_language,
							'REFRESH_DATA'	=> array('url' => $this->helper->route('mrgoldy_ultimateteams_manage', array('team_id' => $team_id, 'mode' => 'edit')), 'time' => 3),
						));
					}
					else
					{
						# Make sure subject is in this team.
						$user_is_member = $this->check_membership($team_id, $subject_id);

						if (!$user_is_member)
						{
							trigger_error($this->lang->lang('UT_ERROR_NOT_MEMBER', $team['team_name']));
						}

						# Check if current user is team leader and has the permission to edit the team.
						if (!$is_leader || !$this->auth->acl_get('u_ut_edit'))
						{
							trigger_error($this->lang->lang('NOT_AUTHORISED'));
						}

						# Display mode
						confirm_box(false, $this->lang->lang('UT_LEADER_PROMOTE_CONFIRM'), build_hidden_fields(array(
							'subject_id'	=> $subject_id,
							'team_id'		=> $team_id,
							'mode'			=> $mode,
						)));
					}
				}
			break;

			# Demote a user from being a team leader.
			case 'demote':
				if ($this->request->is_ajax())
				{
					if (confirm_box(true))
					{
						# Demote the person from being a team leader
						$sql = 'UPDATE ' . $this->ut_correlation_table . ' SET team_leader = 0 WHERE team_id = ' . (int) $team_id . ' AND user_id = ' . (int) $subject_id;
						$this->db->sql_query($sql);

						# Send out a notification to the subject
						# send_ut_notification($event, $team_id, $team_name, $actionee_id, $recipients_array)
						$this->send_ut_notification('demoted', $team_id, $team['team_name'], $this->user->data['user_id'], array($subject_id));

						# Show success message
						$success_language = $this->lang->lang('UT_LEADER_DEMOTE_SUCCESS');
						return new JsonResponse(array(
							'MESSAGE_TITLE'	=> $this->lang->lang('CONFIRM'),
							'MESSAGE_TEXT'	=> $success_language,
							'REFRESH_DATA'	=> array('url' => $this->helper->route('mrgoldy_ultimateteams_manage', array('team_id' => $team_id, 'mode' => 'edit')), 'time' => 3),
						));
					}
					else
					{
						# Make sure subject is in this team.
						$user_is_member = $this->check_membership($team_id, $subject_id);

						if (!$user_is_member)
						{
							trigger_error($this->lang->lang('UT_ERROR_NOT_MEMBER', $team['team_name']));
						}

						# Check if current user is team leader and has the permission to edit the team.
						if (!$is_leader || !$this->auth->acl_get('u_ut_edit'))
						{
							trigger_error($this->lang->lang('NOT_AUTHORISED'));
						}

						# Check if subject is even a leader
						if (!in_array($subject_id, $team_leaders))
						{
							trigger_error($this->lang->lang('UT_ERROR_SUBJECT_NOT_LEADER'));
						}

						# Check if user is demoting him/herself and is the only leader
						if ($subject_id == $this->user->data['user_id'] && count($team_leaders) == 1)
						{
							trigger_error($this->lang->lang('UT_ERROR_ONLY_LEADER'));
						}

						# Display mode
						confirm_box(false, $this->lang->lang('UT_LEADER_DEMOTE_CONFIRM'), build_hidden_fields(array(
							'subject_id'	=> $subject_id,
							'team_id'		=> $team_id,
							'mode'			=> $mode,
						)));
					}
				}
			break;

			# Not the switch-default, make team default for member
			case 'default':
				if ($this->request->is_ajax())
				{
					if (confirm_box(true))
					{
						# Make team default for member
						$sql = 'UPDATE ' . USERS_TABLE . ' SET user_team_id = ' . (int) $team_id . ' WHERE user_id = ' . (int) $subject_id;
						$this->db->sql_query($sql);

						# Show success message
						$success_language = $this->lang->lang('UT_TEAM_DEFAULT_SUCCESS', $team['team_name']);
						return new JsonResponse(array(
							'MESSAGE_TITLE'	=> $this->lang->lang('CONFIRM'),
							'MESSAGE_TEXT'	=> $success_language,
							'REFRESH_DATA'	=> array('url' => $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => $team_id)), 'time' => 3),
						));
					}
					else
					{
						# Make sure user is in this team.
						$user_is_member = $this->check_membership($team_id, $subject_id);

						if (!$user_is_member)
						{
							trigger_error($this->lang->lang('UT_ERROR_NOT_MEMBER', $team['team_name']));
						}

						# Display mode
						confirm_box(false, $this->lang->lang('UT_TEAM_DEFAULT_CONFIRM', $team['team_name']), build_hidden_fields(array(
							'subject_id'	=> $subject_id,
							'team_id'		=> $team_id,
							'mode'			=> $mode,
						)), 'confirm_body.html', $this->helper->route('mrgoldy_ultimateteams_member', array('team_id' => (int) $team_id, 'mode' => $mode, 'subject_id' => $subject_id)));
					}
				}
			break;
		}
	}

	private function check_membership($team_id, $subject_id)
	{
		$sql = 'SELECT user_status FROM ' . $this->ut_correlation_table . ' WHERE team_id = ' . (int) $team_id . ' AND user_id = ' . (int) $subject_id;
		$result = $this->db->sql_query($sql);
		$user_status = $this->db->sql_fetchfield('user_status');
		$this->db->sql_freeresult ($result);

		return $user_status == constants::UT_USER_STATUS_JOINED ? true : false;
	}

	private function add_correlation($team_id, $subject_id, $user_status)
	{
		$correlation_array = array(
			'user_id'		=> $subject_id,
			'team_id'		=> $team_id,
			'user_status'	=> $user_status,
			'team_leader'	=> 0,
		);

		$sql = 'INSERT INTO ' . $this->ut_correlation_table . ' ' . $this->db->sql_build_array('INSERT', $correlation_array);
		$this->db->sql_query($sql);

		# Check if the using is joining, and not already in a team, add it to the USERS table.
		if ($user_status == constants::UT_USER_STATUS_JOINED)
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_team_id = CASE
					   WHEN user_team_id = 0 THEN ' . (int) $team_id . '
					   ELSE user_team_id
					END
					WHERE user_id = ' . (int) $subject_id;
			$this->db->sql_query($sql);
		}
	}

	private function remove_correlation($team_id, $subject_id)
	{
		$sql = 'DELETE FROM ' . $this->ut_correlation_table . ' WHERE team_id = ' . (int) $team_id . ' AND user_id = ' . (int) $subject_id;
		$this->db->sql_query($sql);
	}

	private function update_correlation($team_id, $subject_id, $user_status)
	{
		# Update the correlation
		$sql = 'UPDATE ' . $this->ut_correlation_table . '
				SET user_status = ' . (int) $user_status . '
				WHERE user_id = ' . (int) $subject_id . '
					AND team_id = ' . (int) $team_id;
		$this->db->sql_query($sql);

		# Check if the using is joining, and not already in a team, add it to the USERS table.
		if ($user_status == constants::UT_USER_STATUS_JOINED)
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_team_id = CASE
					   WHEN user_team_id = 0 THEN ' . (int) $team_id . '
					   ELSE user_team_id
					END
					WHERE user_id = ' . (int) $subject_id;
			$this->db->sql_query($sql);
		}
	}

	private function update_default_team($team_id, $subject_id)
	{
		/**
		 * Check if subject that had this team as default, also is in an other group, then set that as default
		 * ORDER BY: team leader first, only logical to set a team of which the subject is leader of as default.
		 * LIMIT: We only want one team to be returned.
		 */
		$sql = 'SELECT team_id
				FROM ' . $this->ut_correlation_table . '
				WHERE team_id != ' . (int) $team_id . '
					AND user_id = ' . (int) $subject_id . '
				ORDER BY team_leader DESC, team_id ASC';

		$result = $this->db->sql_query_limit($sql, 1);
		$possible_new_team_id = $this->db->sql_fetchfield('team_id');
		$this->db->sql_freeresult($result);

		$new_team_id = !empty($possible_new_team_id) ? $possible_new_team_id : 0;

		$sql = 'UPDATE ' . USERS_TABLE . ' SET user_team_id = ' . (int) $new_team_id . ' WHERE user_id = ' . (int) $subject_id;
		$this->db->sql_query($sql);
	}

	private function send_ut_notification($event, $team_id, $team_name, $actionee_id, $recipients_array)
	{
		# Increment our notifications sent counter
		$this->config->increment('ut_notification_id', 1);

		# Send out notification
		$this->notification_manager->add_notifications('mrgoldy.ultimateteams.notification.type.ultimateteams', array(
			'event'					=> (string) $event,
			'team_id'				=> (int) $team_id,
			'team_name'				=> (string) $team_name,
			'actionee_id'			=> (int) $actionee_id,
			'recipients_array'		=> (array) $recipients_array,
			'ut_notification_id'	=> (int) $this->config['ut_notification_id'],
		));
	}
}
