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
use \Sabberworm\CSS\RuleSet\DeclarationBlock;
use \Sabberworm\CSS\RuleSet\AtRuleSet;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');

class color_form extends \moodleform {
    private $colors = array();

    public function setCss(string $css) {
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
                    if ($ruleset instanceof AtRuleSet) {
                        $this->colors[$color]->rules[] = $ruleset->atRuleName() . ' -> ' . $rule->getRule();
                    } else if ($ruleset instanceof DeclarationBlock) {
                        $this->colors[$color]->rules[] =
                                implode(', ', $ruleset->getSelectors()) . ' -> ' . $rule->getRule();
                    }
                }
            }
        }

        foreach($this->colors as $key => $value) {
            echo "<div>" . $key . " - " . $value->usedcount . " - " . implode('; ', $value->rules) . "</div>";
        }
    }

    public function definition() {
    }
}

class color_info {
    public $usedcount = 0;
    public $rules = array();
}