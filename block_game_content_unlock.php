<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/game_content_unlock/lib.php');

class block_game_content_unlock extends block_base
{
	public static $resource_events = array('\block_game_points\event\points_earned', '\block_game_achievements\event\achievement_reached', '\block_game_content_unlock\event\content_unlocked');

    public function init()
	{
        $this->title = get_string('title', 'block_game_content_unlock');
    }

	public function applicable_formats()
	{
        return array(
            'all'    => true
        );
    }
	
	public function instance_allow_multiple()
	{
	  return true;
	}
	
    public function get_content()
	{		
		global $DB, $USER;
		$this->content = new stdClass;
		
		$this->content->text = "<p>Não há debloqueios disponíveis no momento!</p>";
	
		if(user_has_role_assignment($USER->id, 5))
		{
			$eventsarray = content_unlock_generate_events_list();
			
			$us = $DB->get_records('content_unlock_system', array('deleted' => 0, 'blockinstanceid' => $this->instance->id));
			if(!empty($us))
			{
				$unlocklist = '';
									
				$course = $DB->get_record('course', array('id' => $this->page->course->id));
				$info = get_fast_modinfo($course);
									
				foreach($us as $unlocksystem)
				{
					$sql = "SELECT *
							FROM {content_unlock_log} c
								INNER JOIN {logstore_standard_log} l ON c.logid = l.id
							WHERE l.userid = :userid
								AND c.unlocksystemid = :unlocksystemid";	
					$params['userid'] = $USER->id;
					$params['unlocksystemid'] = $unlocksystem->id;
					$unlocked_content = $DB->record_exists_sql($sql, $params);
					if($unlocked_content)
					{
						continue;
					}

					$ccm = get_course_and_cm_from_cmid($unlocksystem->coursemoduleid);
					if($this->page->course->id != $ccm[0]->id)
					{
						continue;
					}
					
					if(!(content_unlock_satisfies_conditions($unlocksystem->restrictions, $this->page->course->id, $USER->id) && (in_array($unlocksystem->conditions, self::$resource_events) || content_unlock_satisfies_block_conditions($unlocksystem, $this->page->course->id, $USER->id))))
					{
						continue;
					}

					$cm = $info->get_cm($unlocksystem->coursemoduleid);
					$eventdescription = is_null($unlocksystem->eventdescription) ? $eventsarray[$unlocksystem->conditions] : $unlocksystem->eventdescription;
					$unlocklist = $unlocklist . '<li>' . $cm->name . ' (' . get_string('modulename', $cm->modname) . ') por ' . (in_array($unlocksystem->conditions, self::$resource_events) ? content_unlock_get_block_conditions_text($unlocksystem) : $eventdescription) . '</li>';
				}
				
				if(strlen($unlocklist) > 0)
				{
					$this->content->text = '<p>Você pode desbloquear:<ul>' . $unlocklist . '</ul></p>';
				}
				
			}
			
			if(isset($this->config))
			{
				$lastunlocksnumber = isset($this->config->lastunlocksnumber) ? $this->config->lastunlocksnumber : 1;
			}
			else
			{
				$lastunlocksnumber = 0;
			}
			
			if($lastunlocksnumber > 0)
			{
				$sql = "SELECT c.id as id, s.coursemoduleid as coursemoduleid, s.eventdescription as eventdescription, s.conditions as conditions, s.connective as connective
					FROM
						{content_unlock_log} c
					INNER JOIN {logstore_standard_log} l ON c.logid = l.id
					INNER JOIN {content_unlock_system} s ON c.unlocksystemid = s.id
					WHERE l.userid = :userid
						AND l.courseid = :courseid
						AND s.blockinstanceid = :blockinstanceid
					ORDER BY c.id DESC";	

				$params['userid'] = $USER->id;
				$params['courseid'] = $this->page->course->id;
				$params['blockinstanceid'] = $this->instance->id;

				$lastunlocks = $DB->get_records_sql($sql, $params, 0, $lastunlocksnumber);
				
				if(!empty($lastunlocks))
				{
					$lastunlockslist = '';
					foreach($lastunlocks as $lu)
					{
						$ccm = get_course_and_cm_from_cmid($lu->coursemoduleid);
						$course = $DB->get_record('course', array('id' => $ccm[0]->id));
						$info = get_fast_modinfo($course);
						$cm = $info->get_cm($lu->coursemoduleid);
						
						$eventdescription = is_null($lu->eventdescription) ? $eventsarray[$lu->conditions] : $lu->eventdescription;
						$lastunlockslist = $lastunlockslist . '<li>' . $cm->name . ' (' . get_string('modulename', $cm->modname) . ') por ' . (in_array($lu->conditions, self::$resource_events) ? content_unlock_get_block_conditions_text($lu) : $eventdescription) . '</li>';
					}
					$this->content->text = $this->content->text . '<p>Você desbloqueou recentemente:<ul>' . $lastunlockslist . '</ul></p>';
				}
			}
			
		}
			 
		return $this->content;
    }

	public function specialization()
	{
		if(isset($this->config))
		{
			if(empty($this->config->title))
			{
				$this->title = get_string('title', 'block_game_content_unlock');            
			}
			else
			{
				$this->title = $this->config->title;
			}
		}
	}
	
    public function has_config()
	{
        return true;
    }
}

?>