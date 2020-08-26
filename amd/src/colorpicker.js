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
 * JavaScript for the form for picking alternate colors to override the default mobile CSS
 *
 * @package    tool_genmobilecss
 * @copyright  2020 Alison of Sheesania
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Sets up a color picker for a single text field input. Uses https://github.com/Simonwep/pickr for the color picker
const setupColorPicker = ($, Pickr, input) => {
  const colorId = input.getAttribute("name");
  const originalColor = input.getAttribute("data-color");

  // Ids for components for previewing a new color that's been selected. Will be hidden when there isn't a new color
  // selected
  const convertMessageSelector = `.path-admin-tool-genmobilecss #convert-message-${colorId}`;
  const newColorPreviewSelector = `.path-admin-tool-genmobilecss #new-color-preview-${colorId}`;

  // The color set in the pickr is only "staged" - it won't actually be saved and included with the form POST
  // until the *input's* value is set to the color.
  const pickr = new Pickr({
    el: input,
    useAsButton: true, // necessary for using a form input as a pickr
    default: originalColor, // default to showing the original color in the pickr
    theme: "classic",
    swatches: null, // don't show any color suggestions
    components: {
      preview: true,
      opacity: true,
      hue: true,
      interaction: {
        input: true,
        save: true,
        cancel: true,
        clear: true,
      },
    },
  })
    .on("save", (color) => {
      if (color) {
        // save the new color by setting it in the input
        const newColor = color.toHEXA().toString(0);
        input.value = newColor;

        // preview the new color by unhiding the relevant components and telling them about the new color
        $(convertMessageSelector).show();
        const newColorPreview = $(newColorPreviewSelector);
        newColorPreview.css("background-color", newColor);
        newColorPreview.show();
      }
      pickr.hide();
    })
    .on("clear", () => {
      // unset any new color and stop showing any previews of it
      pickr.setColor(originalColor);
      input.value = "";
      $(convertMessageSelector).hide();
      $(newColorPreviewSelector).hide();
    })
    .on("cancel", (pickr) => {
      pickr.hide();
    });
};

// Sets up color pickers for all the text fields for picking alternate colors
const setupColorPickers = ($, Pickr) => {
  const inputElements = document.querySelectorAll(".path-admin-tool-genmobilecss .colorpicker-text input");

  // Only load the color picker when the input first becomes visible. This prevents loading 100+ color pickers all at
  // once when the page loads, making it freeze briefly
  let inputVisibleObserver = new IntersectionObserver((entries, self) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        setupColorPicker($, Pickr, entry.target);
        self.unobserve(entry.target);
      }
    });
  });

  inputElements.forEach((input) => {
    inputVisibleObserver.observe(input);
  });
};

// Enable tabbing in the text area for entering custom CSS. By default, tab just brings you to the next form field.
// This code makes tab insert four spaces in the text area instead, which is handy if you're entering indented CSS.
const setupTabInCustomCSSTextarea = ($) => {
  // from https://stackoverflow.com/a/6637396/4954731
  $(document).delegate(".path-admin-tool-genmobilecss #id_customcss", "keydown", function (e) {
    var keyCode = e.keyCode || e.which;

    if (keyCode == 9) {
      e.preventDefault();
      var start = this.selectionStart;
      var end = this.selectionEnd;

      // set textarea value to: text before caret + tab + text after caret
      $(this).val(
        $(this).val().substring(0, start) +
          "    " +
          $(this).val().substring(end)
      );

      // put caret at right position again
      this.selectionStart = this.selectionEnd = start + 4;
    }
  });
};

// require.js sorcery demanded by Moodle
define([
  "jquery",
  "https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js",
], function ($, Pickr) {
  return {
    init: function () {
      setupColorPickers($, Pickr);
      setupTabInCustomCSSTextarea($);
    },
  };
});
