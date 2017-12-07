<?php
/**
 *
 * Ultimate Teams. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Mr. Goldy
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mrgoldy\ultimateteams\notification\type;

/**
 * Ultimate Teams Notification class.
 */
class ultimateteams extends \phpbb\notification\type\base
{
	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/**
	 * Set the controller helper
	 * @param \phpbb\controller\helper $helper
	 *
	 * @return void
	 */
	public function set_controller_helper(\phpbb\controller\helper $helper)
	{
		$this->helper = $helper;
	}

	/**
	 * Set the user loader
	 * @param \phpbb\user_loader $user_loader
	 * @return void
	 */
	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	/**
	 * Get notification type name
	 *
	 * @return string
	 */
	public function get_type()
	{
		return 'mrgoldy.ultimateteams.notification.type.ultimateteams';
	}

	/**
	 * Is this type available to the current user (defines whether or not it will be shown in the UCP Edit notification options)
	 *
	 * @return bool True/False whether or not this is available to the user
	 */
	public function is_available()
	{
		return false;
	}

	/**
	 * Get the id of the notification
	 *
	 * @param array $data The type specific data
	 *
	 * @return int Id of the notification
	 */
	public static function get_item_id($data)
	{
		return $data['ut_notification_id'];
	}

	/**
	 * Get the id of the parent
	 *
	 * @param array $data The type specific data
	 *
	 * @return int Id of the parent
	 */
	public static function get_item_parent_id($data)
	{
		// No parent
		return 0;
	}

	/**
	 * Find the users who want to receive notifications
	 *
	 * @param array $data The type specific data
	 * @param array $options Options for finding users for notification
	 * 		ignore_users => array of users and user types that should not receive notifications from this type because they've already been notified
	 * 						e.g.: array(2 => array(''), 3 => array('', 'email'), ...)
	 *
	 * @return array
	 */
	public function find_users_for_notification($data, $options = array())
	{
		$users[$data['author_id']] = $this->notification_manager->get_default_methods();

		return $users;
	}

	/**
	 * Users needed to query before this notification can be displayed
	 *
	 * @return array Array of user_ids
	 */
	public function users_to_query()
	{
		return array($this->get_data('actionee_id'));
	}

	/**
	 * Get the HTML formatted title of this notification
	 *
	 * @return string
	 */
	public function get_title()
	{
		$username = $this->user_loader->get_username($this->get_data('actionee_id'), 'no_profile');
		$team_name = $this->get_data('team_name');
		$event = $this->get_data('event');
		switch ($event)
		{
			case 'deleted':
				$title = $this->user->lang('UT_NOTIFICATION_DELETED', $username, $team_name); // 1: Username | 2: Username
			break;

			case 'invite_send':
				$title = $this->user->lang('UT_NOTIFICATION_INVITE_SEND', $username, $team_name); // 1: Username | 2: Username
			break;
			case 'invite_accepted':
				$title = $this->user->lang('UT_NOTIFICATION_INVITE_ACCEPTED', $username, $team_name); // 1: Username | 2: Username
			break;
			case 'invite_denied':
				$title = $this->user->lang('UT_NOTIFICATION_INVITE_DENIED', $username, $team_name); // 1: Username | 2: Username
			break;
			case 'request_send':
				$title = $this->user->lang('UT_NOTIFICATION_REQUEST_SEND', $username, $team_name); // 1: Username | 2: Username
			break;
			case 'request_accepted':
				$title = $this->user->lang('UT_NOTIFICATION_REQUEST_ACCEPTED', $team_name); // 1: Team name
			break;
			case 'request_denied':
				$title = $this->user->lang('UT_NOTIFICATION_REQUEST_DENIED', $team_name); // 1: Team name
			break;
			case 'left':
				$title = $this->user->lang('UT_NOTIFICATION_LEFT', $username, $team_name); // 1: Username | 2: Username
			break;
			case 'kicked':
				$title = $this->user->lang('UT_NOTIFICATION_KICKED', $team_name); // 1: Team name
			break;
			case 'promoted':
				$title = $this->user->lang('UT_NOTIFICATION_PROMOTED', $username, $team_name); // 1: Username | 2: Username
			break;
			case 'demoted':
				$title = $this->user->lang('UT_NOTIFICATION_DEMOTED', $username, $team_name); // 1: Username | 2: Username
			break;
		}

		return $title;
	}

	/**
	 * Get the url to this item
	 *
	 * @return string URL
	 */
	public function get_url()
	{
		$event = $this->get_data('event');
		$team_id = (int) $this->get_data('team_id');

		if ($event === 'deleted')
		{
			$url = $this->helper->route('mrgoldy_ultimateteams_index');
		}
		else
		{
			$url = $this->helper->route('mrgoldy_ultimateteams_view', array('team_id' => $team_id));
		}

		return $url;
	}

	/**
	 * Get email template
	 *
	 * @return string|bool
	 */
	public function get_email_template()
	{
		return false;
	}

	/**
	 * Get email template variables
	 *
	 * @return array
	 */
	public function get_email_template_variables()
	{
		return array();
	}

	/**
	 * Function for preparing the data for insertion in an SQL query
	 * (The service handles insertion)
	 *
	 * @param array $data The type specific data
	 * @param array $pre_create_data Data from pre_create_insert_array()
	 *
	 * @return array Array of data ready to be inserted into the database
	 */
	public function create_insert_array($data, $pre_create_data = array())
	{
		$this->set_data('event', $data['event']);
		$this->set_data('team_id', $data['team_id']);
		$this->set_data('team_name', $data['team_name']);
		$this->set_data('actionee_id', $data['actionee_id']);
		$this->set_data('recipients_array', $data['recipients_array']);
		$this->set_data('ut_notification_id', $data['ut_notification_id']);

		parent::create_insert_array($data, $pre_create_data);
	}
}
