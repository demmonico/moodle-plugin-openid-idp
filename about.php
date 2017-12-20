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
 * About page for the OpenID identity provider plugin
 *
 * @package    local_openid_idp
 * @copyright  2011 MuchLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    require_once('../../config.php');
}

require_once($CFG->dirroot . '/local/openid_idp/lib/openid_helper.php');

$helper = openid_helper::get_instance();

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url($helper->make_url());
$PAGE->set_heading(get_string('pluginname', 'local_openid_idp'));
$PAGE->set_title(get_string('pluginname', 'local_openid_idp'));
echo $OUTPUT->header();
print_string('description', 'local_openid_idp');
echo $OUTPUT->footer();
