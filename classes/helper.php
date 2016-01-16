<?php

defined('MOODLE_INTERNAL') || die();

class block_game_content_unlock_helper
{

    public static function observer(\core\event\base $event)
	{
		/*global $DB;
		
		$course = $DB->get_record('course', array('id' => 2));
		$info = get_fast_modinfo($course, $event->userid);
		$cm = $info->get_cm(2);
		$cm->set_user_visible(false);
		
		print_object($cm->uservisible);*/

    }
	
    protected static function is_student($userid) {
        return user_has_role_assignment($userid, 5);
    }

}
