<?php
/**
 *
 * Ultimate Teams. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Mr. Goldy
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mrgoldy\ultimateteams;

class constants
{
	# Everybody can join the team without approval, aswell as being invited
	const UT_TEAM_TYPE_OPEN = 1;

	# People can apply to join the team or get invited
	const UT_TEAM_TYPE_REQUEST = 2;

	# People can only join on invitation
	const UT_TEAM_TYPE_CLOSED = 3;

	#===============================================#

	# User has joined
	const UT_USER_STATUS_JOINED = 1;

	# User has applied
	const UT_USER_STATUS_REQUESTED = 2;

	# User is invited
	const UT_USER_STATUS_INVITED = 3;
}
