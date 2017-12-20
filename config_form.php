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
 * Configuration form for trusted RPs
 *
 * @package    local_openid_idp
 * @copyright  2011 MuchLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/formslib.php');

class trustedrp_form extends moodleform {
    public function definition() {
        $mform =& $this->_form;

        $strrequired = get_string('required');

        $helper = openid_helper::get_instance();

        if (isset($this->_customdata->url)) {
            $mform->addElement('static', 'url', get_string('rp_url', 'local_openid_idp'), s($this->_customdata->url));
        } else {
            $mform->addElement('text', 'url', get_string('rp_url', 'local_openid_idp'));
            $mform->setType('url', PARAM_URL);
            $mform->addRule('url', $strrequired, 'required', null, 'client');
        }

        $extensions = $helper->get_extensions();

        foreach ($extensions as $extension) {
            if (function_exists('trustedrp_form_'.$extension)) {
                call_user_func('trustedrp_form_'.$extension, $mform, $this->_customdata);
            }
        }

        $this->add_action_buttons();
    }
}
