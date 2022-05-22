<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Edit page for local_alias
 * @package   local_alias
 * @copyright 2022, Van Huynh
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/alias/classes/form/edit.php');
require_once($CFG->dirroot . '/local/alias/classes/alias_manager.php');

$PAGE->set_url(new moodle_url('/local/alias/edit.php'));
require_login();
$systemcontext = context_system::instance();
require_capability('local/alias:managealias', $systemcontext);
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('edit_alias', 'local_alias'));
$PAGE->set_title(get_string('edit_alias', 'local_alias'));

$aliasid = optional_param('aliasid', null, PARAM_INT);

$mform = new edit();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/alias/manage.php', get_string('cancelled_form', 'local_alias'));
} else if ($fromform = $mform->get_data()) {
    $manager = new alias_manager();
    if ($fromform->id) {
        $manager->update_alias($fromform->id, $fromform->friendly, $fromform->destination);
        redirect($CFG->wwwroot . '/local/alias/manage.php',
            get_string('updated_form', 'local_alias'));
    } else {
        $manager->create_alias($fromform->friendly, $fromform->destination);
        redirect($CFG->wwwroot . '/local/alias/manage.php',
            get_string('created_form', 'local_alias'));
    }
}

if ($aliasid) {
    $manager = new alias_manager();
    $alias = $manager->get_alias_by_id($aliasid);
    $mform->set_data($alias);
    if (!$alias) {
        throw new invalid_parameter_exception(get_string('err_invalid_alias_id', 'local_alias'));
    }
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
