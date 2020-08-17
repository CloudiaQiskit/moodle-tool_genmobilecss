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

define([
  "jquery",
  "https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js",
], function ($, Pickr) {
  return {
    init: function () {
      const inputElements = document.querySelectorAll(
        ".colorpicker-text input"
      );
      inputElements.forEach((input) => {
        const originalColor = input.getAttribute("name");
        if (!originalColor) {
          return;
        }
        // Substring necessary to remove a hash from a hex color
        const convertMessageId = `#convert-message-${originalColor.substring(
          1
        )}`;
        const newColorPreviewId = `#new-color-preview-${originalColor.substring(
          1
        )}`;

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
      });
    },
  };
});
