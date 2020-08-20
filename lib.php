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
 * Interface between tool_genmobilecss and the Moodle core libraries
 *
 * @package    tool_genmobilecss
 * @copyright  2020 Alison of Sheesania
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function for serving files from the tool_genmobilecss file area. Used to serve the custom CSS file, which is stored
 * in the plugin's file area.
 *
 * The parameters are required by Moodle, but are all ignored in favor of just serving the custom CSS file.
 */
function tool_genmobilecss_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // No access restrictions - anyone can access the custom mobile CSS file, whether they're logged in or not
    $css_file_manager = new \tool_genmobilecss\css_file_manager();
    $file = $css_file_manager->get_file();
    send_stored_file($file, 86400, 0, false, array());
}