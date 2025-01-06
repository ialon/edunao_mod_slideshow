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
 * Slideshow module version information
 *
 * @package    mod_slideshow
 * @copyright  2024 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/slideshow/lib.php');
require_once($CFG->dirroot.'/mod/slideshow/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
$p       = optional_param('p', 0, PARAM_INT);  // Slideshow instance ID
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

if ($p) {
    if (!$slideshow = $DB->get_record('slideshow', array('id'=>$p))) {
        throw new \moodle_exception('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('slideshow', $slideshow->id, $slideshow->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('slideshow', $id)) {
        throw new \moodle_exception('invalidcoursemodule');
    }
    $slideshow = $DB->get_record('slideshow', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/slideshow:view', $context);

// Completion and trigger events.
slideshow_view($slideshow, $course, $cm, $context);

$PAGE->set_url('/mod/slideshow/view.php', array('id' => $cm->id));

$options = empty($slideshow->displayoptions) ? [] : (array) unserialize_array($slideshow->displayoptions);

$activityheader = ['hidecompletion' => false];
if (empty($options['printintro'])) {
    $activityheader['description'] = '';
}

if ($inpopup and $slideshow->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_pagelayout('popup');
    $PAGE->set_title($course->shortname.': '.$slideshow->name);
    $PAGE->set_heading($course->fullname);
} else {
    $PAGE->add_body_class('limitedwidth');
    $PAGE->set_title($course->shortname.': '.$slideshow->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($slideshow);
    if (!$PAGE->activityheader->is_title_allowed()) {
        $activityheader['title'] = "";
    }
}
$PAGE->activityheader->set_attrs($activityheader);

$PAGE->requires->js_call_amd('mod_slideshow/presentation', 'init', [['cmid' => $cm->id]]);

echo $OUTPUT->header();

$slideshtml = '';
$slides = $DB->get_records('slideshow_slide', array('slideshow' => $cm->id, 'hidden' => 0), 'sortorder');

foreach ($slides as $slide) {
    $content = file_rewrite_pluginfile_urls($slide->content, 'pluginfile.php', $context->id, 'mod_slideshow', 'content', $slideshow->revision);
    $formatoptions = new stdClass;
    $formatoptions->noclean = true;
    $formatoptions->overflowdiv = true;
    $formatoptions->context = $context;
    $content = format_text($content, $slide->contentformat, $formatoptions);

    $classes = 'slide';
    if (!empty($slideshtml)) {
        $classes .= ' hidden';
    }

    $slideshtml .= html_writer::div($content, $classes);
}

$previcon = $OUTPUT->pix_icon('t/collapsed_rtl', get_string('prev', 'slideshow'));
$prevbutton = html_writer::link('#', $previcon, ['class' => 'prev disabled', 'title' => get_string('prev', 'slideshow')]);
$nexticon = $OUTPUT->pix_icon('t/collapsed', get_string('next', 'slideshow'));
$nextbutton = html_writer::link('#', $nexticon, ['class' => 'next' . (count($slides) == 1 ? ' disabled' : ''), 'title' => get_string('next', 'slideshow')]);

$navbuttons = html_writer::span($prevbutton . $nextbutton, 'navbuttons');
$currentslide = html_writer::span('1/' . count($slides), 'currentslide');

$fullicon = $OUTPUT->pix_icon('e/fullscreen', get_string('fullscreen', 'slideshow'));
$fullscreen = html_writer::link('#', $fullicon, ['class' => 'fullscreen']);

$slideshtml .= html_writer::div($navbuttons . $currentslide . $fullscreen, 'slidecontrols');

echo $OUTPUT->box($slideshtml, "slidecontainer generalbox center clearfix", 'slideshow-' . $cm->id);

echo $OUTPUT->footer();
