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
 * Manage page for local_alias
 * @package   local_alias
 * @copyright 2022, Van Huynh
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/alias/classes/alias_manager.php');
require_once($CFG->dirroot . '/local/alias/classes/form/search.php');

require_login();
$systemcontext = context_system::instance();
require_capability('local/alias:managealias', $systemcontext);

$PAGE->set_url(new moodle_url('/local/alias/manage.php'));

$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('manage_alias', 'local_alias'));
$PAGE->set_title(get_string('manage_alias', 'local_alias'));
$PAGE->requires->js_call_amd('local_alias/confirm');

$manager = new alias_manager();
$currentpage = optional_param('page', 0, PARAM_INT);
$query = optional_param('q', '', PARAM_NOTAGS);
$perpage = 3;
$mform = new search();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/alias/manage.php', get_string('cancelled_search_form', 'local_alias'));
} else if ($fromform = $mform->get_data()) {
    if ($fromform->query) {
        redirect($CFG->wwwroot . "/local/alias/manage.php?q=$fromform->query",
            get_string('submitted_search_form', 'local_alias'));
    }
}

if ($query !== '') {
    $mform->set_data(['query' => $query]);
}

$urls = $manager->get_aliases($currentpage, $query);

echo $OUTPUT->header();

$templatecontext = [
    'editurl' => new moodle_url('/local/alias/edit.php'),
    "empty" => count($urls['aliases']) == 0,
    "urls" => array_values($urls['aliases']),
    "filter_not_found_text" => get_string('filter_not_found_text', 'local_alias'),
    'create_button_text' => get_string('create_button_text', 'local_alias'),
    'edit_button_text' => get_string('edit_button_text', 'local_alias'),
    'delete_button_text' => get_string('delete_button_text', 'local_alias'),
    "form" => $mform->render(),
];

echo $OUTPUT->render_from_template('local_alias/manage', $templatecontext);
if (isset($urls['pages']) && $urls['count'] > $perpage) {
    $baseurl = new moodle_url('/local/alias/manage.php', ['page' => $currentpage, 'q' => $query]);
    echo $OUTPUT->paging_bar($urls['count'], $currentpage, $perpage, $baseurl);
}
echo $OUTPUT->footer();
