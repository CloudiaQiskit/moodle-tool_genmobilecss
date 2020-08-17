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
 * TODO - comment
 *
 * @package    tool_genmobilecss
 * @copyright  2020 Alison of Sheesania
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('toolgenmobilecss');

$pagetitle = get_string('pluginname', 'tool_genmobilecss');

$context = context_system::instance();

$url = new moodle_url("/admin/tool/genmobilecss/index.php");
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($pagetitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($pagetitle);

$step = optional_param('step', 1, PARAM_INT);

if ($step == 1) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($pagetitle);
    intro_step();
} else if ($step == 2) {
    $PAGE->requires->css(new \moodle_url('https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css'));
    echo $OUTPUT->header();
    echo $OUTPUT->heading($pagetitle);
    choose_custom_colors_step();
} else if ($step == 3) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($pagetitle);
    generate_custom_css_step();
} else if ($step == 4) {
    redirect_step();
}

echo $OUTPUT->footer();
die();

function intro_step() {
    $introform = new \tool_genmobilecss\intro_form();
    $introform->display();
}

function choose_custom_colors_step() {
    //$response = file_get_contents('https://mobileapp.moodledemo.net/build/main.css');
    $response = '.heemin { background-color: #fff; }';
    $cache = cache::make('tool_genmobilecss', 'mobilecss');
    $cache->set('mobilecss', $response);
    $colorform = new \tool_genmobilecss\color_form($response);
    $colorform->display();
}

function generate_custom_css_step() {
    $formdata = (new \tool_genmobilecss\color_form())->get_data();
    $colorstoreplace = array();
    foreach(get_object_vars($formdata) as $oldcolor => $newcolor) {
        if (preg_match('/^#[\da-f]{3,8}$/i', $newcolor)) {
            $colorstoreplace[$oldcolor] = $newcolor;
        }
    }
    $conclusionform = new \tool_genmobilecss\conclusion_form($colorstoreplace);
    $conclusionform->display();
}

function redirect_step() {
    $mobilesettingsurl = new moodle_url('/admin/settings.php', ['section' => 'mobileappearance']);
    redirect($mobilesettingsurl);
    die();
}