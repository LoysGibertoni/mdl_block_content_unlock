<?php

global $DB, $OUTPUT, $PAGE, $USER;
 
require_once('../../config.php');
require_once('block_game_content_unlock_delete_form.php');
 
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
 
$PAGE->set_url('/blocks/game_content_unlock/unlocksystemdelete.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('unlocksystemdeleteheading', 'block_game_content_unlock')); 

$settingsnode = $PAGE->settingsnav->add(get_string('gamecontentunlocksettings', 'block_game_content_unlock'));
$editurl = new moodle_url('/blocks/game_content_unlock/unlocksystemdelete.php', array('id' => $id, 'courseid' => $courseid, 'unlocksystemid' => $unlocksystemid));
$editnode = $settingsnode->add(get_string('unlocksystemdeletepage', 'block_game_content_unlock'), $editurl);
$editnode->make_active();

$deleteform = new block_game_content_unlock_delete_form();
if($deleteform->is_cancelled())
{
    $url = new moodle_url('/my/index.php');
    redirect($url);
}
else if($data = $deleteform->get_data())
{
	$oldunlocksystem = $DB->get_record('content_unlock_system', array('id' => $unlocksystemid));
	
	$record = new stdClass();
	$record->id = $oldunlocksystem->id;
	$record->coursemoduleid = $oldunlocksystem->coursemoduleid;
	$record->coursemodulevisibility = $oldunlocksystem->coursemodulevisibility;
	$record->conditions = $oldunlocksystem->conditions;
	$record->eventdescription = $oldunlocksystem->eventdescription;
	$record->blockinstanceid = $oldunlocksystem->blockinstanceid;
	$record->deleted = 1;
	$DB->update_record('content_unlock_system', $record);
	
    $url = new moodle_url('/my/index.php');
    redirect($url);
}
else
{
	$toform['unlocksystemid'] = $unlocksystemid;
	$toform['courseid'] = $courseid;
	$deleteform->set_data($toform);
	$site = get_site();
	echo $OUTPUT->header();
	$deleteform->display();
	echo $OUTPUT->footer();
}

?>