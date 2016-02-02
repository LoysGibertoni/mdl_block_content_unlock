<?php

global $DB, $OUTPUT, $PAGE, $USER;
 
require_once('../../config.php');
require_once('block_game_content_unlock_add_form.php');
 
global $DB;
 
// Required variables
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
 
// Optional variables
$id = optional_param('id', 0, PARAM_INT);
 
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_game_content_unlock', $courseid);
}
 
require_login($course);
 
$PAGE->set_url('/blocks/game_content_unlock/unlocksystemadd.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('unlocksystemaddheading', 'block_game_content_unlock')); 

$settingsnode = $PAGE->settingsnav->add(get_string('gamecontentunlocksettings', 'block_game_content_unlock'));
$editurl = new moodle_url('/blocks/game_points/unlocksystemadd.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));
$editnode = $settingsnode->add(get_string('unlocksystemaddpage', 'block_game_content_unlock'), $editurl);
$editnode->make_active();

$addform = new block_game_content_unlock_add_form($courseid);
if($addform->is_cancelled())
{
    $url = new moodle_url('/my/index.php');
    redirect($url);
}
else if($data = $addform->get_data())
{
	$record = new stdClass();
	$record->coursemoduleid = $data->coursemodule;
	$record->coursemodulevisibility = $data->coursemodulevisibility;
	$record->conditions = $data->event;
	$record->eventdescription = empty($data->description) ? null : $data->description;
	$record->blockinstanceid = $blockid;
	$usid = $DB->insert_record('content_unlock_system', $record);
	
	$record = new stdClass();
	$record->unlocksystemid = $usid;
	$record->processorid = $USER->id;
	$DB->insert_record('content_unlock_processor', $record);
	
	$visibility = $data->coursemodulevisibility == 0 ? 1 : 0;
	set_coursemodule_visible($data->coursemodule, $visibility);
	
    $url = new moodle_url('/my/index.php');
    redirect($url);
}
else
{
	$toform['blockid'] = $blockid;
	$toform['courseid'] = $courseid;
	$addform->set_data($toform);
	$site = get_site();
	echo $OUTPUT->header();
	$addform->display();
	echo $OUTPUT->footer();
}

?>