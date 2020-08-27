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
 * Form that generates custom CSS, stores it in a file, and tell the user how to set it as their custom mobile CSS.
 *
 * @package    tool_genmobilecss
 * @copyright  2020 Alison of Sheesania
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_genmobilecss;

use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Value\Color;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\AtRuleSet;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');

class conclusion_form extends \moodleform {
    /** @var string The URL to the generated custom CSS file */
    private $cssurl = '';

    /**
     *
     * Executed when the form is first created to generate a custom CSS file with the color overrides + additional
     * CSS the user picked out, then save the file and grab its URL. When the form is actually displayed, then the URL
     * and additional instructions will be shown to the user.
     *
     * @param array $colorstoreplace Map of original colors => the colors to replace them with
     * @param string $addlcss Any additional CSS the user wanted to include in the custom CSS file
     */
    public function __construct(array $colorstoreplace = null, string $addlcss = '') {
        // If $colorstoreplace is null, we're hitting this from a postback and don't need to worry about it.
        if (!is_null($colorstoreplace)) {
            $coloroverridecss = $this->generate_color_overrides($colorstoreplace);
            $newcss = $this->add_addl_css($coloroverridecss, $addlcss);
            $this->cssurl = $this->write_css_file($newcss);
        }

        parent::__construct();
    }

    /**
     * Displays the URL to the generated CSS, some instructions, and a button for going to the admin page where the user
     * can set the file for custom mobile CSS.
     */
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('static', 'url', get_string('urldesc', 'tool_genmobilecss'), $this->cssurl);
        $mform->addElement('static', 'instructions', '', get_string('urlinstructions', 'tool_genmobilecss'));
        $mform->addElement('hidden', 'step', '4');
        $mform->setType('step', PARAM_INT);
        $this->add_action_buttons(false, get_string('gotosettings', 'tool_genmobilecss'));
    }

    /**
     * Find and replace colors the user wanted to override in the default CSS.
     *
     * @param array $colorstoreplace Map of original colors => the colors to replace them with
     * @return string String of the new CSS with colors to override replaced. It will be the same as the default CSS,
     *      just with different colors.
     */
    private function generate_color_overrides(array $colorstoreplace) {
        // Grab the default CSS from the cache.
        $cache = \cache::make('tool_genmobilecss', 'mobilecss');
        $defaultcss = $cache->get('mobilecss');
        $newcss = $defaultcss;

        // Replace all instances of each color the user wanted to replace with the alternate color they picked.
        foreach ($colorstoreplace as $oldcolor => $newcolor) {
            $newcss = str_replace($oldcolor, $newcolor, $newcss);
        }

        return $newcss;
    }

    /**
     * Append any additional custom CSS the user entered to the color override CSS.
     *
     * @param string $coloroverridecss The CSS overriding default colors with the new ones the user picked
     * @param string $addlcss The additional custom CSS the user entered. Possibly an empty string
     * @return string The CSS strings properly concatenated into a full CSS file
     */
    private function add_addl_css(string $coloroverridecss, string $addlcss) {
        // Additional CSS comes after the replaced colors CSS so it's easier to override rules from the default CSS.
        return
            "\* This is an automatically generated file. DO NOT EDIT *\\\n" .
            "\n" .
            $coloroverridecss .
            "\n\n" .
            "\* START ADDLCSS *\\\n" .
            $addlcss .
            "\n" .
            "\* END ADDLCSS *\\\n";
    }

    /**
     * Write the generated CSS to a file in this plugin's file area and return the file's URL
     *
     * @param string $css The CSS to write to the file
     * @return string The URL to the saved file
     */
    private function write_css_file(string $css) {
        global $CFG;

        $cssfilemanager = new css_file_manager();
        $file = $cssfilemanager->get_file();
        if ($file) {
            $file->delete();
        }

        $fileinfo = $cssfilemanager->get_file_info();
        $fs = get_file_storage();
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