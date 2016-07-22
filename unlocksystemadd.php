<?php

global $DB, $OUTPUT, $PAGE, $USER;
 
require_once('../../config.php');
require_once('block_game_content_unlock_add_form.php');
require_once($CFG->dirroot.'/group/lib.php');
 
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
$editurl = new moodle_url('/blocks/game_content_unlock/unlocksystemadd.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));
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
	$record->mode = $data->mode;
	$record->coursemodulevisibility = $data->mode == 0 ? $data->coursemodulevisibility : null;
	$record->conditions = $data->event;
	$record->eventdescription = empty($data->description) ? null : $data->description;
	$record->blockinstanceid = $blockid;
	$record->restrictions = empty($data->availabilityconditionsjson) ? null : $data->availabilityconditionsjson;
	$usid = $DB->insert_record('content_unlock_system', $record);
	
	$record = new stdClass();
	$record->unlocksystemid = $usid;
	$record->processorid = $USER->id;
	$DB->insert_record('content_unlock_processor', $record);
	
	if($data->mode == 0) // By visibility mode
	{
		$visibility = $data->coursemodulevisibility == 0 ? 1 : 0;
		set_coursemodule_visible($data->coursemodule, $visibility);
	}
	else // By group mode
	{
		$group_data = new stdClass();
		$group_data->name = 'us' . $usid;
		$group_data->courseid = $courseid;
		$groupid = groups_create_group($group_data);

		$record = new stdClass();
		$record->id = $usid;
		$record->groupid = $groupid;
		$DB->update_record('content_unlock_system', $record);

		$availability = $DB->get_field('course_modules', 'availability', array('id' => $data->coursemodule));
		$record = new stdClass();
		$record->id = $data->coursemodule;
		// REGEX: ^{.*(,{\"op\".*}).*}$
		if($availability)
		{
			$record->availability = '{"op":"&","c":[' . $availability . ', {"type":"group","id":' . $groupid . '}],"showc":[true,false]}';
		}
		else
		{
			$record->availability = '{"op":"&","c":[{"type":"group","id":' . $groupid . '}],"showc":[false]}';
		}
		$DB->update_record('course_modules', $record);
		rebuild_course_cache($courseid);
	}
	
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