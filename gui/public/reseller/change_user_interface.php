<?php
/**
 * i-MSCP - internet Multi Server Control Panel
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "VHCS - Virtual Hosting Control System".
 *
 * The Initial Developer of the Original Code is moleSoftware GmbH.
 * Portions created by Initial Developer are Copyright (C) 2001-2006
 * by moleSoftware GmbH. All Rights Reserved.
 *
 * Portions created by the ispCP Team are Copyright (C) 2006-2010 by
 * isp Control Panel. All Rights Reserved.
 *
 * Portions created by the i-MSCP Team are Copyright (C) 2010-2017 by
 * i-MSCP - internet Multi Server Control Panel. All Rights Reserved.
 */

// Include needed libraries
require 'imscp-lib.php';

iMSCP_Events_Aggregator::getInstance()->dispatch(iMSCP_Events::onResellerScriptStart);

// Check for login
check_login('reseller');

// Switch back to admin
if (isset($_SESSION['logged_from']) && isset($_SESSION['logged_from_id']) && isset($_GET['action']) &&
	$_GET['action'] == 'go_back'
) {
	change_user_interface($_SESSION['user_id'], $_SESSION['logged_from_id']);
} elseif (isset($_SESSION['user_id']) && isset($_GET['to_id'])) { // Switch to customer
	$toUserId = intval($_GET['to_id']);

	// Admin logged as reseller:
	if (isset($_SESSION['logged_from']) && isset($_SESSION['logged_from_id'])) {
		$fromUserId = $_SESSION['logged_from_id'];
	} else { // reseller to customer
		$fromUserId = $_SESSION['user_id'];

		if (who_owns_this($toUserId, 'client') != $fromUserId) {
			showBadRequestErrorPage();
		}
	}

	change_user_interface($fromUserId, $toUserId);
} else {
	showBadRequestErrorPage();
}
