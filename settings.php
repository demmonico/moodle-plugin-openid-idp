<?php
// This file is NOT part of Moodle - http://moodle.org/
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This software is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Add link to settings page from the settings block.
 *
 * @package    local_openid_idp
 * @copyright  2011 MuchLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('authsettings', new admin_externalpage('openid_idp',
            get_string('pluginname', 'local_openid_idp'),
            new moodle_url('/local/openid_idp/config.php')));
}
