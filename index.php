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
 * Main page for the custom mobile CSS generation tool.
 *
 * @package    tool_genmobilecss
 * @copyright  2020 Alison of Sheesania
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

// Set up this page as a Moodle admin page.
admin_externalpage_setup('toolgenmobilecss');

$pagetitle = get_string('pluginname', 'tool_genmobilecss');

$context = context_system::instance();

$url = new moodle_url("/admin/tool/genmobilecss/index.php");
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($pagetitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($pagetitle);

// Step is used to control what step of the CSS generation process we're currently on, and thus which form to show.
// Each form has a hidden field with the next step's number, which will cause it to advance to the next step when it's
// ...submitted.
$step = optional_param('step', 1, PARAM_INT);

if ($step == 1) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($pagetitle);
    download_default_css_step();
} else if ($step == 2) {
    // CSS required for color pickers. Has to be queued up before header stuff is printed out.
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

/**
 * Display the intro form with a button for downloading the current default mobile CSS file.
 */
function download_default_css_step() {
    $downloadform = new \tool_genmobilecss\download_form();
    $downloadform->display();
}

/**
 * Download the current default Moodle Mobile CSS file, cache it for later processing, and then display the form for
 * choosing alternate colors.
 */
function choose_custom_colors_step() {
    $response = file_get_contents('https://mobileapp.moodledemo.net/build/main.css');
    $cache = cache::make('tool_genmobilecss', 'mobilecss');
    $cache->set('mobilecss', $response);
    $colorform = new \tool_genmobilecss\color_form($response);
    $colorform->display();
}

/**
 * Get the replacement colors + additional CSS the user picked on the preceding color form, and then display the
 * form that generates new custom CSS and makes it available to the user.
 */
function generate_custom_css_step() {
    // Find what replacement colors the user picked on the previous form. The format is colorid => new color.
    $formdata = (new \tool_genmobilecss\color_form())->get_data();
    // Get cached information about colors in the default CSS file. Needed to look up what original color each colorid
    // ...represents.
    $cache = cache::make('tool_genmobilecss', 'colors');
    $colorinfo = $cache->get('colors');
    $colorstoreplace = array();

    foreach (get_object_vars($formdata) as $colorid => $newcolor) {
        // If the form field is one with a hex color code - i.e. one of the replacement color fields and not one of the
        // ...other form items...
        if (preg_match('/^#[\da-f]{3,8}$/i', $newcolor)) {
            // Look up what original color this colorid represents, then map the old color to the new replacement color
            // ...in $colorstoreplace.
            $oldcolor = $colorinfo[$colorid]->color;
            $colorstoreplace[$oldcolor] = $newcolor;
        }
    }

    // Also grab the form field for additional custom CSS.
    $addlcss = '';
    if (property_exists($formdata, 'customcss')) {
        $addlcss = $formdata->customcss;
    }

    // Okay, now we can actually start working on generating the new CSS file!
    $conclusionform = new \tool_genmobilecss\conclusion_form($colorstoreplace, $addlcss);
    $conclusionform->display();
}

/**
 * Redirect to the admin settings page where custom mobile CSS can be set. Called after new custom CSS has been
 * generated and the user clicks a button to go to the settings page.
 */
function redirect_step() {
    $mobilesettingsurl = new moodle_url('/admin/settings.php', ['section' => 'mobileappearance']);
    redirect($mobilesettingsurl);
    die();
}