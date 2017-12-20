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
 * Manages trusted RPs
 *
 * @package    local_openid_idp
 * @copyright  2011 MuchLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/openid_idp/lib/openid_helper.php');
require_once($CFG->dirroot . '/local/openid_idp/config_form.php');

require_login();

$action = optional_param('action', null, PARAM_ACTION);
$userid = optional_param('userid', 0, PARAM_INT);

if ($userid != 0) {
    $PAGE->set_context(get_context_instance(CONTEXT_USER, $userid));
    $PAGE->set_url(new moodle_url('/local/openid_idp/config.php', array('userid' => $userid)));
    $PAGE->set_pagelayout('admin');
    if ($userid != $USER->id) {
        require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));
        $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
        $PAGE->navigation->extend_for_user($user);
        if ($node = $PAGE->settingsnav->get('userviewingsettings'.$user->id)) {
            $node->forceopen = true;
        }
    } else {
        require_capability('local/openid_idp:logintoremote', get_context_instance(CONTEXT_SYSTEM));
    }
} else {
    require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));
    admin_externalpage_setup('openid_idp');
}
$baseparams = array();
if ($userid != 0) {
    $baseparams['userid'] = $userid;
}

$PAGE->set_heading(get_string('pluginname', 'local_openid_idp'));
$PAGE->set_title(get_string('pluginname', 'local_openid_idp'));

$helper = openid_helper::get_instance();

$extensions = $helper->get_extensions();

if ($action == 'delete') {
    $trust_id = required_param('id', PARAM_INT);
    $confirm = optional_param('confirm', false, PARAM_BOOL);
    $trust = $DB->get_record('local_openid_idp_trusted_rps', array('id' => $trust_id), '*', MUST_EXIST);
    if ($trust->userid != $userid) {
        print_error('invalid_id', 'local_openid_idp');
    }
    if ($confirm) {
        $DB->delete_records('local_openid_idp_trusted_rps', array('id' => $trust_id));
        redirect(new moodle_url('/local/openid_idp/config.php', $baseparams), get_string('rp_deleted', 'local_openid_idp', s($trust->url)));
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('confirm_delete', 'local_openid_idp', s($trust->url)),
                              new moodle_url('/local/openid_idp/config.php', $baseparams + array('action' => 'delete', 'confirm' => 'yes', 'id' => $trust_id)),
                              new moodle_url('/local/openid_idp/config.php', $baseparams));
        echo $OUTPUT->footer();
    }
    die;
} else if ($action == 'edit') {
    if ($userid != 0) {
        print_error('cannot_edit', 'local_openid_idp');
    }
    $trust_id = required_param('id', PARAM_INT);
    $trust = $DB->get_record('local_openid_idp_trusted_rps', array('id' => $trust_id), '*', MUST_EXIST);
    if ($trust->userid != 0) {
        print_error('invalid_id', 'local_openid_idp');
    }
    $form = new trustedrp_form(new moodle_url('/local/openid_idp/config.php', array('action' => 'edit', 'id' => $trust_id)), $trust);
    if ($form->is_cancelled()) {
        redirect(new moodle_url('/local/openid_idp/config.php'));
    } else if (($data = $form->get_data())) {
        $rec = new stdClass;
        $rec->id = $trust_id;

        unset($data->action);
        unset($data->id);
        unset($data->submitbutton);
        $rec->options = serialize($data);
        $DB->update_record('local_openid_idp_trusted_rps', $rec);
        redirect(new moodle_url('/local/openid_idp/config.php'), get_string('rp_modified', 'local_openid_idp', s($trust->url)));
    } else {
        $form->set_data(unserialize($trust->options));
        echo $OUTPUT->header();
        $form->display();
        echo $OUTPUT->footer();
    }
    die;
} else if ($action == 'add') {
    if ($userid != 0) {
        print_error('cannot_edit', 'local_openid_idp');
    }
    $form = new trustedrp_form(new moodle_url('/local/openid_idp/config.php', array('action' => 'add')));
    if ($form->is_cancelled()) {
        redirect(new moodle_url('/local/openid_idp/config.php'));
    } else if (($data = $form->get_data())) {
        $rec = new stdClass;
        $rec->url = $data->url;
        $rec->userid = 0;

        unset($data->url);
        unset($data->action);
        unset($data->submitbutton);
        $rec->options = serialize($data);
        $DB->insert_record('local_openid_idp_trusted_rps', $rec);
        redirect(new moodle_url('/local/openid_idp/config.php'), get_string('rp_added', 'local_openid_idp', s($rec->url)));
    } else {
        echo $OUTPUT->header();
        $form->display();
        echo $OUTPUT->footer();
    }
    die;
}

echo $OUTPUT->header();

echo html_writer::start_tag('p');

print_string('trusted_rp_description', 'local_openid_idp');

if ($userid == 0) {
    print_string('trusted_rp_description_global', 'local_openid_idp');
}

echo html_writer::end_tag('p');

$trustedrptable = new html_table;
$trustedrptable->head = array(get_string('rp_url', 'local_openid_idp'));
foreach ($extensions as $extension) {
    if (function_exists('trustedrptable_init_'.$extension)) {
        call_user_func('trustedrptable_init_'.$extension, $trustedrptable);
    }
}
$trustedrptable->head[] = '';

$trustedrptable->data = array();

$trustedrps = $DB->get_recordset('local_openid_idp_trusted_rps', array('userid' => $userid), 'url ASC');

foreach($trustedrps as $trustedrp) {
    $row = new html_table_row();
    $row->cells[] = s($trustedrp->url);
    foreach ($extensions as $extension) {
        if (function_exists('trustedrptable_add_data_'.$extension)) {
            call_user_func('trustedrptable_add_data_'.$extension, $trustedrp, $row);
        }
    }
    $managelinks = array();
    if ($userid == 0) {
        $url = new moodle_url('/local/openid_idp/config.php', array('action' => 'edit', 'id' => $trustedrp->id));
        $managelinks[] = html_writer::link($url, html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'alt' => get_string('edit'), 'title' => get_string('edit'))));
    }
    $url = new moodle_url('/local/openid_idp/config.php', $baseparams + array('action' => 'delete', 'id' => $trustedrp->id));
    $managelinks[] = html_writer::link($url, html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('delete'), 'title' => get_string('delete'))));
    $row->cells[] = implode(' ', $managelinks);
    $trustedrptable->data[] = $row;
}

if (!empty($trustedrptable->data)) {
    echo html_writer::table($trustedrptable);
} else {
    echo html_writer::start_tag('p');
    print_string('no_trusted_rps', 'local_openid_idp');
    echo html_writer::end_tag('p');
}

if ($userid == 0) {
    $button = new single_button(new moodle_url('/local/openid_idp/config.php', array('action' => 'add')), get_string('add_rp', 'local_openid_idp'), 'GET');
    echo $OUTPUT->render($button);
} else {
    echo html_writer::start_tag('p');
    print_string('user_add_rp', 'local_openid_idp');
    echo html_writer::end_tag('p');
}

echo $OUTPUT->footer();
