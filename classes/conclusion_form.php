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
 * TODO: comment
 *
 * @package    tool_genmobilecss
 * @copyright  2020 Alison of Sheesania
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_genmobilecss;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');

class conclusion_form extends \moodleform {
    private $cssurl = '';

    public function __construct(array $colorstoreplace = null) {
        global $CFG;

        $context = \context_system::instance();
        $fs = \get_file_storage();
        $fileinfo = array(
                'contextid' => $context->id,
                'component' => 'tool_genmobilecss',
                'filearea' => 'newcss',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => 'custom_mobile.css');
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
        if ($file) {
            $file->delete();
        }
        $fs->create_file_from_string($fileinfo, 'hello heemins');

        $fileurl = \moodle_url::make_pluginfile_url(
                $fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename'], false);

        $currentcsslastchar = substr($CFG->mobilecssurl, -1);
        if (strcmp($currentcsslastchar, '0') == 0) {
            $this->cssurl = $fileurl . '#1';
        } else {
            $this->cssurl = $fileurl . '#0';
        }

        parent::__construct();
    }

    public function definition() {
        $mform = $this->_form;
        $mform->addElement('static', 'url', get_string('urldesc', 'tool_genmobilecss'), $this->cssurl);
        $mform->addElement('static', 'instructions', '', get_string('urlinstructions', 'tool_genmobilecss'));
        $this->add_action_buttons(false, get_string('gotosettings', 'tool_genmobilecss'));
    }
}