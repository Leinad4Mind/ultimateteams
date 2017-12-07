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
	'ACL_CAT_ULTIMATETEAMS'	=> 'Ultimate Teams',

	'ACL_U_UT_VIEW'			=> 'Can view the teams list',
	'ACL_U_UT_VIEW_TEAM'	=> 'Can view a team’s page',
	'ACL_U_UT_ADD'			=> 'Can add a new team',
	'ACL_U_UT_DELETE'		=> 'Can delete a team of which (s)he is leader',
	'ACL_U_UT_EDIT'			=> 'Can edit a team of which (s)he is leader',

	'ACL_M_UT_DELETE'		=> 'Can delete teams',
	'ACL_M_UT_EDIT'			=> 'Can edit teams',
));
