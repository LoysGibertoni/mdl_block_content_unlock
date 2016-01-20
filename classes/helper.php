<?php

defined('MOODLE_INTERNAL') || die();

class block_game_content_unlock_helper
{

    public static function observer(\core\event\base $event)
	{


    }
	
    protected static function is_student($userid) {
        return user_has_role_assignment($userid, 5);
    }

}
