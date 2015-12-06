<?php
/**
 * i-MSCP - internet Multi Server Control Panel
 * Copyright (C) 2010-2015 by i-MSCP Team
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/***********************************************************************************************************************
 * Functions
 */

/**
 * Update customer password
 *
 * @return void
 */
function customer_updatePassword()
{
    if (!empty($_POST)) {
        $userId = $_SESSION['user_id'];

        \iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onBeforeEditUser, null, [
            'userId' => $userId
        ]);

        if (empty($_POST['current_password']) || empty($_POST['password']) || empty($_POST['password_confirmation'])) {
            set_page_message(tr('All fields are required.'), 'error');
        } else if (!_customer_checkCurrentPassword($_POST['current_password'])) {
            set_page_message(tr('Current password is invalid.'), 'error');
        } else if ($_POST['password'] !== $_POST['password_confirmation']) {
            set_page_message(tr("Passwords do not match."), 'error');
        } elseif (checkPasswordSyntax($_POST['password'])) {
            $query = 'UPDATE `admin` SET `admin_pass` = ? WHERE `admin_id` = ?';
            exec_query($query, [\iMSCP\Core\Utils\Crypt::bcrypt($_POST['password']), $userId]);

            \iMSCP\Core\Application::getInstance()->getEventManager()->trigger(
                \iMSCP\Core\Events::onAfterEditUser, null, ['userId' => $userId]
            );

            write_log($_SESSION['user_logged'] . ': updated password.', E_USER_NOTICE);
            set_page_message(tr('Password successfully updated.'), 'success');
        }
    }
}

/**
 * Checks customer current password
 *
 * @param string $password Password to check
 * @return bool
 */
function _customer_checkCurrentPassword($password)
{
    $stmt = exec_query('SELECT `admin_pass` FROM `admin` WHERE `admin_id` = ?', $_SESSION['user_id']);

    if (!$stmt->rowCount()) {
        set_page_message(tr('Unable to retrieve your password from the database.'), 'error');
        return false;
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!\iMSCP\Core\Utils\Crypt::verify($password, $row['admin_pass'])) {
        return false;
    }

    return true;
}

/***********************************************************************************************************************
 * Main
 */

require '../../application.php';

\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onClientScriptStart);

check_login('user');
customer_updatePassword();

$cfg = \iMSCP\Core\Application::getInstance()->getConfig();

$tpl = new \iMSCP\Core\Template\TemplateEngine();
$tpl->define_dynamic([
    'layout' => 'shared/layouts/ui.tpl',
    'page' => 'shared/partials/forms/password_update.tpl',
    'page_message' => 'layout'
]);
$tpl->assign([
    'TR_PAGE_TITLE' => tr('Client / Profile / Password'),
    'TR_PASSWORD_DATA' => tr('Password data'),
    'TR_CURRENT_PASSWORD' => tr('Current password'),
    'TR_PASSWORD' => tr('Password'),
    'TR_PASSWORD_CONFIRMATION' => tr('Password confirmation'),
    'TR_UPDATE' => tr('Update')
]);

generateNavigation($tpl);
generatePageMessage($tpl);

$tpl->parse('LAYOUT_CONTENT', 'page');
\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onClientScriptEnd, null, [
    'templateEngine' => $tpl
]);
$tpl->prnt();

unsetMessages();
