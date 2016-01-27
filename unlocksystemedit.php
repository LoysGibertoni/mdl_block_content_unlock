<?php

global $DB, $OUTPUT, $PAGE, $USER;
 
require_once('../../config.php');
require_once('block_game_content_unlock_edit_form.php');
 
global $DB;
 
// Required variables
$courseid = required_param('courseid', PARAM_INT);
$unlocksystemid = required_param('unlocksystemid', PARAM_INT);
 
// Optional variables
$id = optional_param('id', 0, PARAM_INT);
 
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_game_content_unlock', $courseid);
}
 
require_login($course);
 
$PAGE->set_url('/blocks/game_content_unlock/unlocksystemedit.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('unlocksystemeditheading', 'block_game_content_unlock')); 

$settingsnode = $PAGE->settingsnav->add(get_string('gamecontentunlocksettings', 'block_game_content_unlock'));
$editurl = new moodle_url('/blocks/game_content_unlock/unlocksystemedit.php', array('id' => $id, 'courseid' => $courseid, 'unlocksystemid' => $unlocksystemid));
$editnode = $settingsnode->add(get_string('unlocksystemeditpage', 'block_game_content_unlock'), $editurl);
$editnode->make_active();

$editform = new block_game_content_unlock_edit_form($unlocksystemid, $courseid);
if($editform->is_cancelled())
{
    $url = new moodle_url('/my/index.php');
    redirect($url);
}
else if($data = $editform->get_data())
{
	$oldpointsystem = $DB->get_record('content_unlock_system', array('id' => $unlocksystemid));
	
	$record = new stdClass();
	$record->id = $oldpointsystem->id;
	$record->coursemoduleid = $oldpointsystem->coursemoduleid;
	$record->conditions = $oldpointsystem->conditions;
	$record->eventdescription = $oldpointsystem->eventdescription;
	$record->blockinstanceid = $oldpointsystem->blockinstanceid;
	$record->deleted = 1;
	$DB->update_record('content_unlock_system', $record);
	
	$record = new stdClass();
	$record->coursemoduleid = $data->coursemodule;
	$record->conditions = $data->event;
	$record->eventdescription = empty($data->description) ? null : $data->description;
	$record->blockinstanceid = $oldpointsystem->blockinstanceid;
	$usid = $DB->insert_record('content_unlock_system', $record);
	
	$record = new stdClass();
	$record->unlocksystemid = $usid;
	$record->processorid = $USER->id;
	$DB->insert_record('content_unlock_processor', $record);
	
	set_coursemodule_visible($data->coursemodule, 0);
	
    $url = new moodle_url('/my/index.php');
    redirect($url);
}
else
{
	$toform['unlocksystemid'] = $unlocksystemid;
	$toform['courseid'] = $courseid;
	$editform->set_data($toform);
	$site = get_site();
	echo $OUTPUT->header();
	$editform->display();
	echo $OUTPUT->footer();
}

?>