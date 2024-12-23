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
    $PAGE->set_slideshowlayout('popup');
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
echo $OUTPUT->header();
// $content = file_rewrite_pluginfile_urls($slideshow->content, 'pluginfile.php', $context->id, 'mod_slideshow', 'content', $slideshow->revision);
// $formatoptions = new stdClass;
// $formatoptions->noclean = true;
// $formatoptions->overflowdiv = true;
// $formatoptions->context = $context;
// $content = format_text($content, $slideshow->contentformat, $formatoptions);
// echo $OUTPUT->box($content, "generalbox center clearfix");

if (!isset($options['printlastmodified']) || !empty($options['printlastmodified'])) {
    $strlastmodified = get_string("lastmodified");
    echo html_writer::div("$strlastmodified: " . userdate($slideshow->timemodified), 'modified');
}

echo $OUTPUT->footer();
