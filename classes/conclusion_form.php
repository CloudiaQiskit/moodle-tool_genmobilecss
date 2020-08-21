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
 * Form that generates custom CSS, stores it in a file, and instructs the user how to set it as their custom mobile
 * CSS file
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

    public function __construct(array $colorstoreplace = null, string $addlcss = '') {
        // If $colorstoreplace is null, we're hitting this from a postback and don't need to worry about it.
        if (!is_null($colorstoreplace)) {
            // Build the part of the generated CSS that overrides default colors with what the user picked.
            $coloroverridecss = $this->generate_color_overrides($colorstoreplace);
            // Add in any extra custom CSS the user put in the form.
            $newcss = $this->add_addl_css($coloroverridecss, $addlcss);
            // Write this CSS to a file and get the URL.
            $this->cssurl = $this->write_css_file($newcss);
        }

        parent::__construct();
    }

    public function definition() {
        // The form just displays the URL to the generated CSS, some instructions, and a button for going to the admin
        // ...page where they can set custom mobile CSS.
        $mform = $this->_form;
        $mform->addElement('static', 'url', get_string('urldesc', 'tool_genmobilecss'), $this->cssurl);
        $mform->addElement('static', 'instructions', '', get_string('urlinstructions', 'tool_genmobilecss'));
        $mform->addElement('hidden', 'step', '4');
        $mform->setType('step', PARAM_INT);
        $this->add_action_buttons(false, get_string('gotosettings', 'tool_genmobilecss'));
    }

    private function generate_color_overrides(array $colorstoreplace) {
        // Grab the default CSS from the cache.
        $cache = \cache::make('tool_genmobilecss', 'mobilecss');
        $oldcss = $cache->get('mobilecss');
        $cssparser = new Parser($oldcss);
        $cssdoc = $cssparser->parse();

        $newcss = new Document();

        foreach ($cssdoc->getAllRuleSets() as $ruleset) {
            foreach ($ruleset->getRules() as $rule) {
                $value = $rule->getValue();
                // If this CSS rule involves a color...
                if ($value instanceof Color) {
                    $color = (string) $value;
                    // Check if this was one of the colors the user wanted to replace. If so, build a new rule with the
                    // ...same selectors and rule, but using the alternate color instead. Then add it to our custom CSS.
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
                        $newrule->setIsImportant(true); // To ensure the default color will get overridden.

                        $newruleset->addRule($newrule);
                        $newcss->append($newruleset);
                    }
                }
            }
        }
        // Render the CSS we've built to a string.
        return $newcss->render();
    }

    private function add_addl_css(string $coloroverridecss, string $addlcss) {
        return
            "\* This is an automatically generated file. DO NOT EDIT *\\\n" .
            "\n" .
            "\* START ADDLCSS *\\\n" .
            $addlcss .
            "\n" .
            "\* END ADDLCSS *\\\n" .
            "\n" .
            $coloroverridecss;
    }

    private function write_css_file(string $css) {
        global $CFG;

        $cssfilemanager = new css_file_manager();
        $file = $cssfilemanager->get_file();
        if ($file) {
            $file->delete();
        }

        $fileinfo = $cssfilemanager->get_file_info();
        $fs = \get_file_storage();
        $fs->create_file_from_string($fileinfo, $css);

        $fileurl = $cssfilemanager->get_file_url();

        // Moodle Mobile only reloads the custom CSS file if the URL has changed. To ensure the URL changes, we can add
        // ...a meaningless anchor to the end of the new URL with a value different from the current custom CSS URL
        // ...setting (see https://docs.moodle.org/dev/Moodle_Mobile_Themes#Updating_your_theme_in_the_app).
        $currentcsslastchar = substr($CFG->mobilecssurl, -1);
        if (strcmp($currentcsslastchar, '0') == 0) {
            return $fileurl . '#1';
        } else {
            return $fileurl . '#0';
        }
    }
}