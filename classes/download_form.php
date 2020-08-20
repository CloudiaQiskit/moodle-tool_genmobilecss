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
 * An initial form with information about the plugin + a button to start downloading the default CSS file
 *
 * @package    tool_genmobilecss
 * @copyright  2020 Alison of Sheesania
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_genmobilecss;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');

class download_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('static', 'intro', '', get_string('introtext', 'tool_genmobilecss'));
        $mform->addElement('static', 'intro', '', get_string('introstep1', 'tool_genmobilecss'));
        $mform->addElement('static', 'intro', '', get_string('introstep2', 'tool_genmobilecss'));
        $mform->addElement('static', 'intro', '', get_string('introstep3', 'tool_genmobilecss'));
        $mform->addElement('static', 'intro', '', get_string('introcalltoaction', 'tool_genmobilecss'));
        $mform->addElement('hidden', 'step', '2');
        $mform->setType('step', PARAM_INT);
        $this->add_action_buttons(false, get_string('downloadmobilecss', 'tool_genmobilecss'));
    }
}