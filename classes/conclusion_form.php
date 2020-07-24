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
use \Sabberworm\CSS\CSSList\Document;
use \Sabberworm\CSS\RuleSet\DeclarationBlock;
use \Sabberworm\CSS\RuleSet\AtRuleSet;
use \Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');

class conclusion_form extends \moodleform {
    private $cssurl = '';

    public function __construct(array $colorstoreplace = null) {
        if (!is_null($colorstoreplace)) {
            $newcss = $this->generate_css($colorstoreplace);
            $this->cssurl = $this->write_css_file($newcss);
        }

        parent::__construct();
    }

    public function definition() {
        $mform = $this->_form;
        $mform->addElement('static', 'url', get_string('urldesc', 'tool_genmobilecss'), $this->cssurl);
        $mform->addElement('static', 'instructions', '', get_string('urlinstructions', 'tool_genmobilecss'));
        $mform->addElement('hidden', 'step', '4');
        $mform->setType('step', PARAM_INT);
        $this->add_action_buttons(false, get_string('gotosettings', 'tool_genmobilecss'));
    }

    private function generate_css(array $colorstoreplace) {
        $cache = \cache::make('tool_genmobilecss', 'mobilecss');
        $oldcss = $cache->get('mobilecss');
        $cssparser = new Parser($oldcss);
        $cssdoc = $cssparser->parse();

        $newcss = new Document();

        foreach($cssdoc->getAllRuleSets() as $ruleset) {
            foreach($ruleset->getRules() as $rule) {
                $value = $rule->getValue();
                if($value instanceof Color) {
                    $color = (string) $value;
                    if (array_key_exists($color, $colorstoreplace)) {
                        $newcolor = Color::parse(new ParserState($colorstoreplace[$color], Settings::create()));

                        $newruleset = null;
                        if ($ruleset instanceof AtRuleSet) {
                            $newruleset = new AtRuleSet($ruleset->atRuleName(), $ruleset->atRuleArgs());
                        } else if ($ruleset instanceof DeclarationBlock) {
                            $newruleset = new DeclarationBlock();
                            $newruleset->setSelectors($ruleset->getSelectors());
                        }

                        $newrule = new Rule($rule->getRule());
                        $newrule->addValue($newcolor);
                        $newrule->setIsImportant(true);

                        $newruleset->addRule($newrule);
                        $newcss->append($newruleset);
                    }
                }
            }
        }
        return $newcss->render();
    }

    private function write_css_file(string $css) {
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
        $fs->create_file_from_string($fileinfo, $css);

        $fileurl = \moodle_url::make_pluginfile_url(
                $fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename'], false);

        $currentcsslastchar = substr($CFG->mobilecssurl, -1);
        if (strcmp($currentcsslastchar, '0') == 0) {
            return $fileurl . '#1';
        } else {
            return $fileurl . '#0';
        }
    }
}