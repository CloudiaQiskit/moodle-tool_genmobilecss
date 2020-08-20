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
 * A form for choosing replacements for colors from the default mobile CSS file, plus adding extra custom CSS.
 *
 * @package    tool_genmobilecss
 * @copyright  2020 Alison of Sheesania
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_genmobilecss;

use \Sabberworm\CSS\Parser;
use \Sabberworm\CSS\Value\Color;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');

class color_form extends \moodleform {
    // numeric id => colorinfo
    private $colors = array();

    public function __construct(string $css = null) {
        $cache = \cache::make('tool_genmobilecss', 'colors');

        // Before the form is built, we need to parse the default CSS file to find what colors are defined in it
        if(is_null($css)) {
            // if $css is null, we're hitting this from a postback and should have already generated the color info
            // and stored it in the cache. So just grab it from cache instead of generating it again.
            $this->colors = $cache->get('colors');
        } else {
            // original color => colorinfo. Used to build up color information before converting into a colorid based
            // array
            $colorsbycolor = array();
            $cssparser = new Parser($css);
            $cssdoc = $cssparser->parse();
            foreach($cssdoc->getAllRuleSets() as $ruleset) {
                foreach($ruleset->getRules() as $rule) {
                    $value = $rule->getValue();
                    // Whenever we hit a value in the CSS which is a color...
                    if($value instanceof Color) {
                        // Add it to the array if it doesn't already exist, and increment the number of times we've seen
                        // it being used
                        $color = (string) $value;
                        if (!array_key_exists($color, $colorsbycolor)) {
                            $colorsbycolor[$color] = new color_info();
                            $colorsbycolor[$color]->color = $color;
                        }
                        $colorsbycolor[$color]->usedcount++;
                    }
                }
            }
            // Sort colors by used count, so most used colors appear first.
            uasort($colorsbycolor, function($a, $b)
            {
                return $b->usedcount - $a->usedcount;
            });
            // Put into an array indexed by an arbitrary numeric ID instead of original color. Numeric IDs are necessary
            // to represent colors in the HTML form, since raw CSS color strings can contain symbols that would get
            // stripped out by PHP when reading the submitted form data (like periods in RGBA color codes).
            foreach($colorsbycolor as $colorinfo) {
                $this->colors[] = $colorinfo;
            }
            $cache->set('colors', $this->colors);
        }
        parent::__construct();
    }

    public function definition() {
        // amd/src/colorpicker.js has the JavaScript for this form
        global $PAGE;
        $PAGE->requires->js_call_amd('tool_genmobilecss/colorpicker', 'init');
        
        // Intro text and submit buttons
        $mform = $this->_form;
        $mform->addElement('static', 'intro', '', get_string('colorformdesc', 'tool_genmobilecss'));
        $mform->addElement('static', 'intro', '', get_string('colorformcalltoaction', 'tool_genmobilecss'));
        $this->add_action_buttons(false, get_string('colorformsubmit', 'tool_genmobilecss'));

        // Text area for additional custom CSS, prefilled with any extra CSS you included the last time you generated
        // CSS with this tool
        $mform->addElement('textarea', 'customcss', get_string('customcsslabel', 'tool_genmobilecss'),
            array('rows'=>'6', 'cols'=>'50', 'style'=>'font-family: monospace;'));
        $existingcustomcss = $this->get_existing_custom_css();
        $mform->setDefault('customcss', $existingcustomcss);

        // Text fields/color pickers for choosing replacements for each color in the default CSS
        foreach($this->colors as $colorid => $colorinfo) {
            $mform->addElement('text', $colorid, get_string('replacementcolor', 'tool_genmobilecss') .
                $colorinfo->color, array('class'=>'colorpicker-text', 'data-color'=>$colorinfo->color));
            $mform->setType($colorid, PARAM_TEXT);
            $mform->addElement('static', 'description-' . $colorid, '',
                    $colorinfo->usedcount . " " . get_string('uses', 'tool_genmobilecss'));

            // Elements for previewing old + new colors
            $previewgroup = array();
            $previewgroup[] =& $mform->createElement('html',
                $this->get_color_preview_div($colorid, $colorinfo->color, False));
            $previewgroup[] =& $mform->createElement('html', '<div id="convert-message-' . $colorid . '" ' .
                'style="height: 30px; 10px; margin-right: 10px; margin-bottom: 35px; display: none;">' .
                get_string('willbeconvertedto', 'tool_genmobilecss') . '</div>');
            $previewgroup[] =& $mform->createElement('html',
                $this->get_color_preview_div($colorid, $colorinfo->color, True));
            $mform->addGroup($previewgroup, 'preview-' . $colorid, '', '', false);
        }

        $mform->addElement('hidden', 'step', '3');
        $mform->setType('step', PARAM_INT);
        $this->add_action_buttons(false, get_string('colorformsubmit', 'tool_genmobilecss'));
    }

    private function get_existing_custom_css() {
        $css_file_manager = new css_file_manager();
        $css = $css_file_manager->get_file_contents();
        if (empty($css)) {
            return '';
        }
        // Any extra custom CSS (i.e. not color overriding stuff) included by this tool is delimited by
        // /* START ADDLCSS */ /* END ADDLCSS */ comments, so parse out only that text
        $with_beginning_trimmed = explode('\* START ADDLCSS *\\', $css)[1];
        $with_end_trimmed = explode("\n\* END ADDLCSS *\\", $with_beginning_trimmed)[0];
        return $with_end_trimmed;
    }

    private function get_color_preview_div(int $colorid, string $color, bool $is_new_color_preview) {
        // Generates HTML for a small outlined box filled with the given color
        $id = $is_new_color_preview ? 'id="new-color-preview-' . $colorid . '"' : '';
        $hidden = $is_new_color_preview ? 'display: none;' : '';
        return '<div ' . $id . ' style="background-color: ' . $color . '; ' .
            'width: 30px; height: 30px; margin-right: 10px; margin-bottom: 35px; outline: solid; ' . $hidden .
            '"></div>';
    }
}

class color_info {
    public $color = '';
    public $usedcount = 0;
}