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
 * Class for handling the custom mobile CSS file managed by this plugin and stored in Moodle's filesystem.
 *
 * @package    tool_genmobilecss
 * @copyright  2020 Alison of Sheesania
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_genmobilecss;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

class css_file_manager {
    private $fileinfo;

    public function __construct() {
        $systemcontext = \context_system::instance();
        // There will only ever be one custom_mobile.css, so most of these values are hard-coded and arbitrary.
        $this->fileinfo = array(
                'contextid' => $systemcontext->id, // has to be the system context so it can be accessed anywhere
                'component' => 'tool_genmobilecss', // has to be managed by this plugin
                'filearea' => 'newcss', // arbitrary
                'itemid' => 0, // arbitrary
                'filepath' => '/', // arbitrary
                'filename' => 'custom_mobile.css'); // arbitrary
    }

    public function get_file() {
        $fileinfo = $this->fileinfo;
        $fs = \get_file_storage();
        return $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
    }

    public function get_file_contents() {
        $file = $this->get_file();
        if ($file) {
            return $file->get_content();
        } else {
            return '';
        }
    }

    public function get_file_info() {
        return $this->fileinfo;
    }

    public function get_file_url() {
        $fileinfo = $this->fileinfo;
        return \moodle_url::make_pluginfile_url(
            $fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename'], false);
    }
}