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

const setupColorPicker = ($, Pickr, input) => {
  const colorId = input.getAttribute("name");
  const originalColor = input.getAttribute("data-color");

  const convertMessageId = `#convert-message-${colorId}`;
  const newColorPreviewId = `#new-color-preview-${colorId}`;

  // The color set in the pickr is only "staged" - it won't actually be saved and included with the form POST
  // until the input's value is set to the color.
  const pickr = new Pickr({
    el: input,
    useAsButton: true,
    default: originalColor,
    theme: "classic",
    swatches: null,
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
        const newColor = color.toHEXA().toString(0);
        input.value = newColor;
        $(convertMessageId).show();
        const newColorPreview = $(newColorPreviewId);
        newColorPreview.css("background-color", newColor);
        newColorPreview.show();
      }
      pickr.hide();
    })
    .on("clear", () => {
      pickr.setColor(originalColor);
      input.value = "";
      $(convertMessageId).hide();
      $(newColorPreviewId).hide();
    })
    .on("cancel", (pickr) => {
      pickr.hide();
    });
};

const setupColorPickers = ($, Pickr) => {
  const inputElements = document.querySelectorAll(".colorpicker-text input");

  // Only load the color picker when the input first becomes visible. This prevents loading 100+ color pickers all at
  // once when the page loads
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

const setupTabInCustomCSSTextarea = ($) => {
  // from https://stackoverflow.com/a/6637396/4954731
  $(document).delegate("#id_customcss", "keydown", function (e) {
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
