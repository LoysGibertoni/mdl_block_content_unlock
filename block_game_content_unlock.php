<?php

defined('MOODLE_INTERNAL') || die();

class block_game_content_unlock extends block_base
{

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
		
		$this->content->text = "Não há debloqueios disponíveis no momento!";
	
		if($this->page->course->id != 1) // Pagina inicial
		{

			if(user_has_role_assignment($USER->id, 5))
			{
				
				$eventslist = report_eventlist_list_generator::get_non_core_event_list();
				$eventsarray = array();
				foreach($eventslist as $value)
				{
					$description = explode("\\", explode(".", strip_tags($value['fulleventname']))[0]);
					$eventsarray[$value['eventname']] = $description[0];
				}
				
				$us = $DB->get_records('content_unlock_system', array('deleted' => 0, 'blockinstanceid' => $this->instance->id));
				if(!empty($us))
				{
					$unlocklist = '';
										
					$course = $DB->get_record('course', array('id' => $this->page->course->id));
					$info = get_fast_modinfo($course);
										
					foreach($us as $unlocksystem)
					{
						$ccm = get_course_and_cm_from_cmid($unlocksystem->coursemoduleid);
						if($this->page->course->id != $ccm[0]->id)
						{
							continue;
						}
						
						$cm = $info->get_cm($unlocksystem->coursemoduleid);
						$eventdescription = is_null($unlocksystem->eventdescription) ? $eventsarray[$unlocksystem->conditions] : $unlocksystem->eventdescription;
						$unlocklist = $unlocklist . '<li>' . $cm->name . ' (' . get_string('modulename', $cm->modname) . ') por ' . $eventdescription . '</li>';
					}
					
					if(strlen($unlocklist) > 0)
					{
						$this->content->text = 'Você pode desbloquear:<ul>' . $unlocklist . '</ul>';
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
					$sql = "SELECT c.id as id, s.coursemoduleid as coursemoduleid, s.eventdescription as eventdescription, s.conditions as conditions
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
							$lastunlockslist = $lastunlockslist . '<li>' . $cm->name . ' (' . get_string('modulename', $cm->modname) . ') por ' . $eventdescription . '</li>';
						}
						$this->content->text = $this->content->text . 'Você desbloqueou recentemente:<ul>' . $lastunlockslist . '</ul>';
					}
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