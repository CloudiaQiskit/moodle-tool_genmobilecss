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

use \Sabberworm\CSS\Parser;
use \Sabberworm\CSS\Value\Color;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');

class color_form extends \moodleform {
    private $colors = array();

    public function __construct(string $css = null) {
        $cache = \cache::make('tool_genmobilecss', 'colors');

        if(is_null($css)) {
            $this->colors = $cache->get('colors');
        } else {
            $cssparser = new Parser($css);
            $cssdoc = $cssparser->parse();
            foreach($cssdoc->getAllRuleSets() as $ruleset) {
                foreach($ruleset->getRules() as $rule) {
                    $value = $rule->getValue();
                    if($value instanceof Color) {
                        $color = (string) $value;
                        if (!array_key_exists($color, $this->colors)) {
                            $this->colors[$color] = new color_info();
                        }
                        $this->colors[$color]->usedcount++;
                    }
                }
            }
            uasort($this->colors, function($a, $b)
            {
                return $b->usedcount - $a->usedcount;
            });
            $cache->set('colors', $this->colors);
        }
        parent::__construct();
    }

    public function definition() {
        global $PAGE;
        $PAGE->requires->js_call_amd('tool_genmobilecss/colorpicker', 'init');
        
        $mform = $this->_form;
        $mform->addElement('static', 'intro', '', get_string('colorformdesc', 'tool_genmobilecss'));
        foreach($this->colors as $colorname => $colorinfo) {
            $mform->addElement('text', $colorname, $colorname, array('class'=>'colorpicker-text'));
            $mform->setType($colorname, PARAM_TEXT);
            $infogroup = array();
            $infogroup[] =& $mform->createElement('html',
                    '<div style="background-color: ' . $colorname . '; ' .
                    'width: 30px; height: 30px; margin-right: 10px; margin-bottom: 10px; outline: solid"></div>');
            $infogroup[] =& $mform->createElement('static', 'description-' . $colorname, '',
                    $colorinfo->usedcount . " " . get_string('uses', 'tool_genmobilecss'));
            $mform->addGroup($infogroup, 'info-' . $colorname, '', '', false);
        }
        $mform->addElement('hidden', 'step', '3');
        $mform->setType('step', PARAM_INT);
        $this->add_action_buttons(false, get_string('colorformsubmit', 'tool_genmobilecss'));
    }
}

class color_info {
    public $usedcount = 0;
}