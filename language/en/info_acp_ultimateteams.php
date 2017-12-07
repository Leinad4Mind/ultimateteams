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
	'ACP_ULTIMATETEAMS'				=> 'Ultimate Teams',
	'ACP_ULTIMATETEAMS_SETTINGS'	=> 'Ultimate Teams settings',

	'ACP_UT_ENABLE_BOTTOM_LINK'			=> 'Enable bottom navigation link',
	'ACP_UT_ENABLE_TOP_LINK'			=> 'Enable top navigation link',
	'ACP_UT_FIND_TEAMS'					=> 'Find teams without leaders',
	'ACP_UT_FIND_TEAMS_CONFIRM'			=> 'Are you sure you want to run the "Find teams without leaders" query?',
	'ACP_UT_FIND_TEAMS_EXPLAIN'			=> 'Find all teams that have no leader and are thus unmanagable. It should not be possible, but this option is here to double check.',
	'ACP_UT_FIND_TEAMS_FOUND'			=> array(
		1 => 'In total there is %1$s team found without a leader.<br /><br />%2$s', // 1: Team count (1) | 2: Team list
		2 => 'In total there are %1$s teams found without a leader.<br /><br />%2$s', // 1: Team count  (Multiple) | 2: Team list
	),
	'ACP_UT_FIND_TEAMS_NONE'			=> 'No teams were found without leaders',
	'ACP_UT_IMAGE_DIR'					=> 'Team image upload directory',
	'ACP_UT_IMAGE_DIR_EXPLAIN'			=> 'Storage path for team images. Please note that if you change this directory while already having uploaded team images you need to manually copy the files to their new location.',
	'ACP_UT_IMAGE_SIZE'					=> 'Maximum team image size',
	'ACP_UT_IMAGE_SIZE_EXPLAIN'			=> 'Maximum size of each file. If this value is 0, the uploadable filesize is only limited by your PHP configuration.<br>Average image filesizes: PNG ~ 2–4 kB, GIF ~ 6–8 kB, JPG ~ 9–12 kB',
	'ACP_UT_MULTIPLE_TEAMS'				=> 'Allow multiple teams',
	'ACP_UT_MULTIPLE_TEAMS_EXPLAIN'		=> 'This will allow users to be part of more than one team. Set to “No” if you want users to be exclusive to one team.',
	'ACP_UT_PURGE_TEAM_IMAGES'			=> 'Purge team images',
	'ACP_UT_PURGE_TEAM_IMAGES_CONFIRM'	=> 'Are you sure you want to purge team images?',
	'ACP_UT_PURGE_TEAM_IMAGES_EXPLAIN'	=> 'Purge all team images that are not in use. When adding new team images, old images are automatically deleted. This option is here to get rid of those which felt through the cracks.',
	'ACP_UT_PURGE_TEAM_IMAGES_SUCCESS'	=> 'Team images have successfully been purged.',
	'ACP_UT_TAG_LENGTH'					=> 'Maximum team tag length',
	'ACP_UT_SETTINGS_SAVED'				=> 'The Ultimate Team settings have been successfully saved.',

	'ACP_UT_ERROR_DIRECTORY_NOT_EXIST'	=> 'The upload directory you specified does not exist.',
	'ACP_UT_ERROR_DIRECTORY_NOT_WRITE'	=> 'The upload directory you specified cannot be written to. Please alter the permissions to allow the webserver to write to it.',
	'ACP_UT_ERROR_TAG_LENGTH'			=> 'The maximum tag length value has to be between 1 and 20.',

	'ACP_UT_LOG_IMAGES_PURGED'		=> '<strong>Purged Ultimate Team images</strong>',
	'ACP_UT_LOG_SETTINGS_SAVED'		=> '<strong>Updated Ultimate Team settings</strong>',
	'ACP_UT_LOG_TEAM_ADDED'			=> '<strong>Added an Ultimate Team</strong<br>» %s', // Team name
	'ACP_UT_LOG_TEAM_DELETED'		=> '<strong>Deleted an Ultimate Team</strong><br>» %s', // Team name
	'ACP_UT_LOG_TEAM_EDITED'		=> '<strong>Edited an Ultimate Team</strong<br>» %s', // Team name
	'ACP_UT_LOG_TEAMS_SEARCHED'		=> '<strong>Searched for Ultimate Teams without leaders</strong>',
));
