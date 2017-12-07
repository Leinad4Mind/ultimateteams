<?php
/**
 *
 * Ultimate Teams. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Mr. Goldy
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mrgoldy\ultimateteams\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Ultimate Teams Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'						=> 'load_language_on_setup',
			'core.page_header'						=> 'add_page_header_link',
			'core.memberlist_prepare_profile_data'	=> 'memberlist_prepare_profile_data',
			'core.viewtopic_get_post_data'			=> 'viewtopic_get_post_data',
			'core.viewtopic_cache_user_data'		=> 'viewtopic_cache_user_data',
			'core.viewtopic_modify_post_row'		=> 'viewtopic_modify_post_row',
			'core.viewonline_overwrite_location'	=> 'viewonline_page',
			'core.permissions'						=> 'add_permission',
		);
	}

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpEx */
	protected $php_ext;

	/** @var string Ultimate Teams teams table */
	protected $ut_teams_table;

	/** @var string Ultimate Teams correlation table */
	protected $ut_correlation_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config				$config		Configuration object
	 * @param \phpbb\controller\helper			$helper		Controller helper object
	 * @param \phpbb\db\driver\driver_interface	$db			Database object
	 * @param \phpbb\language\language			$lang		Language object
	 * @param \phpbb\template\template			$template	Template object
	 * @param \phpbb\user               		$user       User object
	 * @param string                    		$php_ext    phpEx
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\language\language $lang, \phpbb\template\template $template, \phpbb\user $user, $php_ext, $ut_teams_table, $ut_correlation_table)
	{
		$this->config	= $config;
		$this->db		= $db;
		$this->helper   = $helper;
		$this->lang		= $lang;
		$this->template = $template;
		$this->user     = $user;
		$this->php_ext  = $php_ext;
		$this->ut_teams_table = $ut_teams_table;
		$this->ut_correlation_table = $ut_correlation_table;
	}

	/**
	 * Load common language files during user setup
	 * @param \phpbb\event\data		$event		Event object
	 * @return void
	 * @access public
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'mrgoldy/ultimateteams',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Add a link to the controller in the forum navbar
	 * @return void
	 * @access public
	 */
	public function add_page_header_link()
	{
		$this->template->assign_vars(array(
			'S_UT_ENABLE_BOTTOM_LINK'	=> $this->config['ut_enable_bottom_link'],
			'S_UT_ENABLE_TOP_LINK'		=> $this->config['ut_enable_top_link'],

			'U_UT_TEAMS'				=> $this->helper->route('mrgoldy_ultimateteams_index'),
		));
	}

	/**
	 * Grab and add the team data for the user's profile
	 * @param \phpbb\event\data		$event		Event object
	 * @return void
	 * @access public
	 */
	public function memberlist_prepare_profile_data($event)
	{
		$user_id = (int) $event['data']['user_id'];
		$team_id = (int) $event['data']['user_team_id'];
		$template_data = $event['template_data'];

		$sql = 'SELECT t.team_name, t.team_id, t.team_tag, t.team_colour
				FROM ' . $this->ut_teams_table . ' t
				JOIN ' . $this->ut_correlation_table . ' c
				WHERE t.team_id = c.team_id
					AND c.user_id = ' . $user_id;
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$team_count = !empty($rowset) ? count($rowset) : false;

		$template_data['UT_TEAM_COUNT'] = $team_count;

		if ($team_count == 1)
		{
			$template_data['UT_TEAM_NAME'] = $rowset[0]['team_name'];
			$template_data['UT_TEAM_COLOUR'] = $rowset[0]['team_colour'];
			$template_data['UT_TEAM_TAG'] = $rowset[0]['team_tag'];
			$template_data['U_UT_TEAM'] = $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => (int) $rowset[0]['team_id']));
		}
		else if ($team_count > 1)
		{
			foreach ($rowset as $row)
			{
				$this->template->assign_block_vars('ut_teams', array(
					'NAME'		=> $row['team_name'],
					'SELECTED'	=> $row['team_id'] == $team_id ? true : false,
					'U_TEAM'	=> $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => (int) $row['team_id']))
				));
			}
		}

		$event['template_data'] = $template_data;
	}

	/**
	 * Add to the SQL array to get the team data
	 * @param \phpbb\event\data		$event		Event object
	 * @return void
	 * @access public
	 */
	public function viewtopic_get_post_data($event)
	{
		$sql_ary = $event['sql_ary'];

		$sql_ary['SELECT'] .= ', utt.team_id, utt.team_name, utt.team_tag, utt.team_colour';
		$sql_ary['LEFT_JOIN'][] = array(
			'FROM'	=> array($this->ut_teams_table => 'utt'),
			'ON'	=> 'u.user_team_id = utt.team_id',
		);

		$event['sql_ary'] = $sql_ary;
	}

	/**
	 * Add team data to the user's cache
	 * @param \phpbb\event\data		$event		Event object
	 * @return void
	 * @access public
	 */
	public function viewtopic_cache_user_data($event)
	{
		$user_cache_data = $event['user_cache_data'];
		$row = $event['row'];

		$user_cache_data['UT_TEAM_ID']		= !empty($row['team_id']) ? $row['team_id'] : '';
		$user_cache_data['UT_TEAM_NAME']	= !empty($row['team_name']) ? $row['team_name'] : '';
		$user_cache_data['UT_TEAM_TAG']		= !empty($row['team_tag']) ? $row['team_tag'] : '';
		$user_cache_data['UT_TEAM_COLOUR']	= !empty($row['team_colour']) ? $row['team_colour'] : '';

		$event['user_cache_data'] = $user_cache_data;
	}

	/**
	 * Add template data for the user's team
	 * @param \phpbb\event\data		$event		Event object
	 * @return void
	 * @access public
	 */
	public function viewtopic_modify_post_row($event)
	{
		$post_row = $event['post_row'];
		$user_poster_data = $event['user_poster_data'];

		$post_row['UT_TEAM_ID']		= !empty($user_poster_data['UT_TEAM_ID']) ? $user_poster_data['UT_TEAM_ID'] : '';
		$post_row['UT_TEAM_NAME']	= !empty($user_poster_data['UT_TEAM_NAME']) ? $user_poster_data['UT_TEAM_NAME'] : '';
		$post_row['UT_TEAM_TAG']	= !empty($user_poster_data['UT_TEAM_TAG']) ? $user_poster_data['UT_TEAM_TAG'] : '';
		$post_row['UT_TEAM_COLOUR']	= !empty($user_poster_data['UT_TEAM_COLOUR']) ? $user_poster_data['UT_TEAM_COLOUR'] : '';
		$post_row['U_UT_TEAM']		= !empty($user_poster_data['UT_TEAM_ID']) ? $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => (int) $user_poster_data['UT_TEAM_ID'])) : '';

		$event['post_row'] = $post_row;
	}

	/**
	 * Show users viewing Acme Demo on the Who Is Online page
	 *
	 * @param \phpbb\event\data		$event		Event object
	 * @return void
	 */
	public function viewonline_page($event)
	{
		if ($event['on_page'][1] === 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/teams') === 0)
		{
			# Grab team names
			$sql = 'SELECT team_id, team_name FROM ' . $this->ut_teams_table;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$teams[$row['team_id']] = $row['team_name'];
			}
			$this->db->sql_freeresult($result);

			$params = explode('/', $event['row']['session_page']);

			switch ($params[2])
			{
				case 'view':
					$event['location'] = $this->lang->lang('UT_VIEWING_TEAM', $teams[$params[3]]);
					$event['location_url'] = $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => (int) $params[3]));
				break;

				case 'manage':
					if ($params[3] == 'add')
					{
						$event['location'] = $this->lang->lang('UT_VIEWING_ADD');
						$event['location_url'] = $this->helper->route('mrgoldy_ultimateteams_index');
					}
					else
					{
						$event['location'] = $this->lang->lang('UT_VIEWING_MANAGE', $teams[$params[4]]);
						$event['location_url'] = $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => (int) $params[4]));
					}
				break;

				case 'page':
				default:
					$event['location'] = $this->lang->lang('UT_VIEWING_INDEX');
					$event['location_url'] = $this->helper->route('mrgoldy_ultimateteams_index');
				break;
			}
		}
	}

	/**
	 * Add permission language
	 * @param \phpbb\event\data		$event		Event object
	 * @return void
	 * @access public
	 */
	public function add_permission($event)
	{
		$permissions = $event['permissions'];
		$categories = $event['categories'];

		$categories['ultimateteams'] = 'ACL_CAT_ULTIMATETEAMS';

		$permissions['u_ut_view']		= array('lang' => 'ACL_U_UT_VIEW', 'cat' => 'ultimateteams');
		$permissions['u_ut_view_team']	= array('lang' => 'ACL_U_UT_VIEW_TEAM', 'cat' => 'ultimateteams');
		$permissions['u_ut_add']		= array('lang' => 'ACL_U_UT_ADD', 'cat' => 'ultimateteams');
		$permissions['u_ut_delete']		= array('lang' => 'ACL_U_UT_DELETE', 'cat' => 'ultimateteams');
		$permissions['u_ut_edit']		= array('lang' => 'ACL_U_UT_EDIT', 'cat' => 'ultimateteams');

		$permissions['m_ut_delete']		= array('lang' => 'ACL_M_UT_DELETE', 'cat' => 'ultimateteams');
		$permissions['m_ut_edit']		= array('lang' => 'ACL_M_UT_EDIT', 'cat' => 'ultimateteams');

		$event['categories'] = $categories;
		$event['permissions'] = $permissions;
	}
}
