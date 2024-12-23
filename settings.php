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
 * Slideshow module admin settings and defaults
 *
 * @package    mod_slideshow
 * @copyright  2024 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_OPEN, RESOURCELIB_DISPLAY_POPUP));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_OPEN);

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configmultiselect('slideshow/displayoptions',
        get_string('displayoptions', 'slideshow'), get_string('configdisplayoptions', 'slideshow'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('slideshowmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('slideshow/printintro',
        get_string('printintro', 'slideshow'), get_string('printintroexplain', 'slideshow'), 0));
    $settings->add(new admin_setting_configcheckbox('slideshow/printlastmodified',
        get_string('printlastmodified', 'slideshow'), get_string('printlastmodifiedexplain', 'slideshow'), 1));
    $settings->add(new admin_setting_configselect('slideshow/display',
        get_string('displayselect', 'slideshow'), get_string('displayselectexplain', 'slideshow'), RESOURCELIB_DISPLAY_OPEN, $displayoptions));
    $settings->add(new admin_setting_configtext('slideshow/popupwidth',
        get_string('popupwidth', 'slideshow'), get_string('popupwidthexplain', 'slideshow'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('slideshow/popupheight',
        get_string('popupheight', 'slideshow'), get_string('popupheightexplain', 'slideshow'), 450, PARAM_INT, 7));
}
