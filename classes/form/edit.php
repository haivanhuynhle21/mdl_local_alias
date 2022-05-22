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
 * Edit/create form.
 *
 * @package   local_alias
 * @copyright 2022, Van Huynh
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

/**
 * Edit/create form.
 *
 * @package   local_alias
 * @copyright 2022, Van Huynh
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit extends moodleform {

    /**
     * Edit/create form definition
     * @return void
     * @throws coding_exception
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'friendly', get_string('friendly_url', 'local_alias'));
        $mform->setType('friendly', PARAM_NOTAGS);

        $mform->addElement('text', 'destination', get_string('destination_url', 'local_alias'));
        $mform->setType('destination', PARAM_NOTAGS);

        $mform->addRule('friendly', get_string('err_required', 'local_alias'), 'required', null, 'client');
        $mform->addRule('destination', get_string('err_required', 'local_alias'), 'required', null, 'client');

        $this->add_action_buttons();
    }

}
