<?php

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
 * Slide configuration form
 *
 * @package     mod_slideshow
 * @copyright   2024 Josemaria Bolanos <admin@mako.digital>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once('edit_form.php');

$cmid = required_param('cm', PARAM_INT);
$slideid = optional_param('id', 0, PARAM_INT);

// Check the course module exists.
$cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);
// Check module exists.
$module = $DB->get_record('modules', array('id'=>$cm->module), '*', MUST_EXIST);
// Check the course exists.
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_login($course);

$returnurl = new moodle_url("/mod/$module->name/slides.php", array('id' => $cm->id));

$context = context_module::instance($cm->id);
$editoroptions = slideshow_get_editor_options($context);

$slide = new stdClass();
if ($slideid) {
    $record = $DB->get_record('slideshow_slide', array('id' => $slideid), '*', MUST_EXIST);
    $slide = file_prepare_standard_editor($record, 'content', $editoroptions, $context, $module->name, 'slide', null);
}

$urlparams = array('cm' => $cm->id, 'id' => $slideid);
$url = new moodle_url("/mod/$module->name/edit.php", $urlparams);
$PAGE->set_url($url);

$pagepath = "mod-$module->name-mod";
$PAGE->set_pagetype($pagepath);
$PAGE->set_pagelayout('admin');
$PAGE->add_body_class('limitedwidth');

$mform = new mod_slideshow_slide_edit_form($url, array('context' => $context, 'cm' => $cm));
$mform->set_data($slide);

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($fromform = $mform->get_data()) {
    $fromform = file_postupdate_standard_editor($fromform, 'content', $editoroptions, $context, $module->name, 'slide', 0);

    $fromform->timemodified = time();

    if (!empty($fromform->id)) {
        $DB->update_record('slideshow_slide', $fromform);
    } else {
        $fromform->sortorder = $DB->count_records('slideshow_slide', array('slideshow' => $cm->id)) + 1;
        $DB->insert_record('slideshow_slide', $fromform);
    }

    \core\notification::add(
        get_string('slide_saved', $module->name),
        \core\notification::SUCCESS
    );

    redirect($returnurl);
} else {
    $fullmodulename = get_string('modulename', $module->name);
    $pageheading = $pagetitle = get_string('addinganew', 'moodle', $fullmodulename);
    $PAGE->navbar->add($pageheading);

    if ($slideid) {
        $pageheading = $pagetitle = get_string('edit', $module->name);
        $PAGE->navbar->add($pageheading);
    }

    $PAGE->set_heading($course->fullname);
    $pagetitle = $pagetitle . moodle_page::TITLE_SEPARATOR . $module->name;
    $PAGE->set_title($pagetitle);
    $PAGE->set_cacheable(false);

    $PAGE->activityheader->disable();

    echo $OUTPUT->header();
    echo $OUTPUT->heading_with_help($pageheading, '', $module->name);

    $mform->display();

    echo $OUTPUT->footer();
}
