<?php
/**
 *
 * Ultimate Teams. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Mr. Goldy
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Characters: “ ” ’ « »
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACME_DEMO_NOTIFICATION'		=> 'Acme demo notification',

	'UT_ERROR_ALREADY_IN_TEAM'		=> 'You are already part of a team.',
	'UT_ERROR_ALREADY_OTHER_TEAM'	=> 'The user is already part of a team.',
	'UT_ERROR_NAME'					=> 'You have entered an invalid team name. It has to be between 1 and 60 characters. You currently have %s characters.', // Team name length
	'UT_ERROR_NAME_NO'				=> 'You have to enter a team name.',
	'UT_ERROR_NAME_TAKEN'			=> 'The team name you have entered is already taken: %s.', // Name of team that already has this name
	'UT_ERROR_NO_IMAGE'				=> 'There is not team image to delete.',
	'UT_ERROR_NO_TEAM'				=> 'No team was found for the given identifier: %s.', // Team ID
	'UT_ERROR_NOT_MEMBER'			=> 'The slected user is not a member of %s.', // Team name
	'UT_ERROR_SUBJECT_NOT_LEADER'	=> 'The member you are trying to demote is not a team leader.',
	'UT_ERROR_ONLY_LEADER'			=> 'You are the only leader. First assign a new team leader.',
	'UT_ERROR_TAG'					=> 'You have entered an invalid team tag. It has to be between 1 and %1$s characters. You currently have %2$s characters.', // 1: Allowed team tag length | 2: Current team tag length
	'UT_ERROR_TAG_NO'				=> 'You have to enter a team tag.',
	'UT_ERROR_TAG_TAKEN'			=> 'The team tag you have entered is already taken: %s.', // Tag of team that already has this tag
	'UT_ERROR_TEAM_CLOSED'			=> 'The team you are requested to join, is a closed team. Which means it is invite-only.',

	'UT_INVITE'						=> 'Invite',
	'UT_INVITE_ACCEPT'				=> 'Accept team invitation',
	'UT_INVITE_ACCEPT_CONFIRM'		=> 'Are you sure you want to accept this team invitation?',
	'UT_INVITE_ACCEPT_SUCCESS'		=> 'You have successfully accepted this team invitation.',
	'UT_INVITE_DENY'				=> 'Deny team invitation',
	'UT_INVITE_DENY_CONFIRM'		=> 'Are you sure you want to deny this team invitation?',
	'UT_INVITE_DENY_SUCCESS'		=> 'You have successfully denied this team invitation.',
	'UT_INVITE_SEND'				=> 'Invite user',
	'UT_INVITE_SEND_CONFIRM'		=> 'Are you sure you want to invite this user?',
	'UT_INVITE_SEND_SUCCESS'		=> 'You have successfully invited this user.',
	'UT_INVITE_WITHDRAW'			=> 'Withdraw team invitation',
	'UT_INVITE_WITHDRAW_CONFIRM'	=> 'Are you sure you want to withdraw the invitation for this user?',
	'UT_INVITE_WITHDRAW_SUCCESS'	=> 'You have successfully withdrawn the invitation for this user.',

	'UT_LEADER_DEMOTE'				=> 'Demote to team member',
	'UT_LEADER_DEMOTE_CONFIRM'		=> 'Are you sure you want to demote this member?',
	'UT_LEADER_DEMOTE_SUCCESS'		=> 'You have successfully demoted this member.',
	'UT_LEADER_PROMOTE'				=> 'Promote to team leader',
	'UT_LEADER_PROMOTE_CONFIRM'		=> 'Are you sure you want to promote this member to team leader?',
	'UT_LEADER_PROMOTE_SUCCESS'		=> 'You have successfully promoted this member.',
	'UT_LEAVE_KICK'					=> 'Kick member from team',
	'UT_LEAVE_KICK_CONFIRM'			=> 'Are you sure you want to kick this member from %s?', // Team name
	'UT_LEAVE_KICK_SUCCESS'			=> 'You have successfully kicked this user from %s.', // Team name
	'UT_LEAVE_SELF_CONFIRM'			=> 'Are you sure you want to leave %s?', // Team name
	'UT_LEAVE_SELF_SUCCESS'			=> 'You have successfully left %s.', // Team name

	'UT_NOTIFICATION_DELETED'			=> '<strong>Group deleted</strong> by %1$s for team %2$s', // 1: Username | 2: Team name
	'UT_NOTIFICATION_DEMOTED'			=> '<strong>Demoted from team leader</strong> by %1$s for the team %2$s', // 1: Username | 2: Team name
	'UT_NOTIFICATION_PROMOTED'			=> '<strong>Promoted to team leader</strong> by %1$s for the team %2$s', // 1: Username | 2: Team name
	'UT_NOTIFICATION_KICKED'			=> '<strong>You have been kicked</strong> from the team %1$s', // 1: Team name
	'UT_NOTIFICATION_LEFT'				=> '<strong>Team member left</strong>, %1$s left the team %2$s', // 1: Username | 2: Team name
	'UT_NOTIFICATION_INVITE_ACCEPTED'	=> '<strong>Team invite accepted</strong> by %1$s to join the team %2$s', // 1: Username | 2: Team name
	'UT_NOTIFICATION_INVITE_DENIED'		=> '<strong>Team invite denied</strong> by %1$s to join the team %2$s', // 1: Username | 2: Team name
	'UT_NOTIFICATION_INVITE_SEND'		=> '<strong>Team invite</strong> from %1$s to join the team %2$s', // 1: Username | 2: Team name
	'UT_NOTIFICATION_REQUEST_ACCEPTED'	=> '<strong>Join request approved</strong> to join the team %1$s', // 1: Team name
	'UT_NOTIFICATION_REQUEST_DENIED'	=> '<strong>Join request denied</strong> to join the team %1$s', // 1: Team name
	'UT_NOTIFICATION_REQUEST_SEND'		=> '<strong>Join request</strong> from %1$s to join the team %2$s', // 1: Username | 2: Team name
	'UT_NOTIFICATION_REQUEST_JOINED'	=> '<strong>New team member</strong>, %1$s has joined the team %1$s', // 1: Username | 2: Team name

	'UT_REQUEST_ACCEPT'				=> 'Accept member',
	'UT_REQUEST_ACCEPT_CONFIRM'		=> 'Are you sure you want to accept this applicant for this team?',
	'UT_REQUEST_ACCEPT_SUCCESS'		=> 'You have successfully accepted this user to your team.',
	'UT_REQUEST_APPLY_CONFIRM'		=> 'Are you sure you want to apply for this team?',
	'UT_REQUEST_APPLY_SUCCESS'		=> 'You have successfully applied for this team.',
	'UT_REQUEST_DENY'				=> 'Deny member',
	'UT_REQUEST_DENY_CONFIRM'		=> 'Are you sure you want to deny this applicant for this team?',
	'UT_REQUEST_DENY_SUCCESS'		=> 'You have successfully denied this applicant for this team.',
	'UT_REQUEST_JOIN_CONFIRM'		=> 'Are you sure you want to join this team?',
	'UT_REQUEST_JOIN_SUCCESS'		=> 'You have successfully joined this team.',
	'UT_REQUEST_WITHDRAW_CONFIRM'	=> 'Are you sure you want to withdraw your application for this team?',
	'UT_REQUEST_WITHDRAW_SUCCESS'	=> 'You have successfully withdrawn your application for this team.',

	'UT_NO_TEAMS'					=> 'No teams yet.',

	'UT_TAG'						=> 'Tag',
	'UT_TAG_COLOUR'					=> 'Tag colour',

	'UT_TEAM_ADD'					=> 'Add team',
	'UT_TEAM_ADDED'					=> 'The team has been successfully added.',
	'UT_TEAM_APPLICANTS'			=> 'Applicants',
	'UT_TEAM_APPLICANTS_NONE'		=> 'No applicants',

	'UT_TEAM_DEFAULT_CONFIRM'		=> 'Are you sure you want to make %s your default team?', // Team name
	'UT_TEAM_DEFAULT_SUCCESS'		=> 'You have successfully made %s your default team.', // Team name
	'UT_TEAM_DELETE'				=> 'Delete team',
	'UT_TEAM_DELETE_CONFIRM'		=> 'Are you sure you wish to delete this team?<br><strong>» %s</strong>', // Team name
	'UT_TEAM_DELETED'				=> 'The team has been deleted successfully.',

	'UT_TEAM_DESCRIPTION'			=> 'Team description',

	'UT_TEAM_EDIT'					=> 'Edit team',
	'UT_TEAM_EDITED'				=> 'The team has been successfully edited.',

	'UT_TEAM_IMAGE'					=> 'Team image',
	'UT_TEAM_IMAGE_CONFIRM'			=> 'Are you sure you want to delete the team image?',
	'UT_TEAM_IMAGE_DELETE'			=> 'Delete team image',
	'UT_TEAM_IMAGE_SUCCESS'			=> 'You have successfully deleted the team image.',
	'UT_TEAM_INVITEES'				=> 'Invitees',
	'UT_TEAM_INVITEES_NONE'			=> 'No invitees',

	'UT_TEAM_JOIN'					=> 'Join team',

	'UT_TEAM_LEADER'				=> 'Team leader',
	'UT_TEAM_LEAVE'					=> 'Leave team',

	'UT_TEAM_MEMBER'				=> 'Team member',
	'UT_TEAM_NAME'					=> 'Team name',

	'UT_TEAM_TYPE'					=> 'Team type',
	'UT_TEAM_TYPE_OPEN'				=> 'Open',
	'UT_TEAM_TYPE_OPEN_EXPLAIN'		=> 'This is an open team, all new members are welcome.',
	'UT_TEAM_TYPE_REQUEST'			=> 'Request',
	'UT_TEAM_TYPE_REQUEST_EXPLAIN'	=> 'This is a request team, members can apply to join or join upon invitation of a team leader.',
	'UT_TEAM_TYPE_CLOSED'			=> 'Closed',
	'UT_TEAM_TYPE_CLOSED_EXPLAIN'	=> 'This is a closed team, new members can only join upon invitation of a team leader.',

	'UT_TEAM_VIEW'					=> '« View the team',
	'UT_TEAM_VIEWING'				=> 'Viewing team',

	'UT_TEAM'						=> 'Team',
	'UT_TEAMS'						=> 'Teams',

	'UT_TEAMS_TOTAL'				=> array(
		1 => '%d Team',
		2 => '%d Teams',
	),

	'UT_VIEWING_INDEX'				=> 'Viewing the teams',
	'UT_VIEWING_TEAM'				=> 'Viewing %s’s team page', // Team name
	'UT_VIEWING_ADD'				=> 'Creating a new team',
	'UT_VIEWING_MANAGE'				=> 'Editing %s’s team page', // Team name
));
