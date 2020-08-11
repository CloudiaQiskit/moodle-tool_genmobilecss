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
 * JavaScript library for the @@newmodule@@ module.
 */

M.tool_genmobilecss = M.tool_genmobilecss || {};

M.tool_genmobilecss.helper = {
  gY: null,

  /**
   * @param Y the YUI object
   * @param opts an array of options
   */
  init: function (Y, opts) {
    M.tool_genmobilecss.helper.gY = Y;
    console.log("hello I am module.js");

    const inputElement = document.querySelector("#id_fff");
    const pickr = new Pickr({
      el: inputElement,
      useAsButton: true,
      default: "#42445A",
      theme: "classic",
      lockOpacity: true,

      swatches: null,

      components: {
        preview: true,
        opacity: true,
        hue: true,

        interaction: {
          hex: true,
          rgba: false,
          hsva: false,
          input: true,
          save: true,
        },
      },
    })
      .on("init", (pickr) => {
        inputElement.value = pickr.getSelectedColor().toHEXA().toString(0);
      })
      .on("save", (color) => {
        inputElement.value = color.toHEXA().toString(0);
        pickr.hide();
      });
  },
};
