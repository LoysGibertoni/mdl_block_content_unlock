<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info.php');
require_once($CFG->dirroot.'/group/lib.php');

class block_game_content_unlock_helper
{

    public static function observer(\core\event\base $event)
	{
		global $DB;
		
        if(!block_game_content_unlock_helper::is_student($event->userid)) {
            return;
        }
		
		$uss = $DB->get_records_sql("SELECT * FROM {content_unlock_system} WHERE deleted = ? AND ".$DB->sql_compare_text('conditions')." = ". $DB->sql_compare_text('?'), array('deleted' => 0, 'conditions' => $event->eventname));
		foreach($uss as $unlocksystem)
		{
			$ccm = get_course_and_cm_from_cmid($unlocksystem->coursemoduleid);
			if($event->courseid != $ccm[0]->id)
			{
				continue;
			}
			
			if(!block_game_content_unlock_helper::is_available($unlocksystem->restrictions, $event->courseid, $event->userid))
			{
				continue;
			}
			
			$blockcontextid = $DB->get_field('block_instances', 'parentcontextid', array('id' => $unlocksystem->blockinstanceid));
			if(!$blockcontextid) // Acontece se o bloco for apagado
			{
				continue;
			}
			
			$blockcontext = context::instance_by_id($blockcontextid);
			$context = context::instance_by_id($event->contextid);
			if(strpos($context->path, $blockcontext->path) !== 0) // Se o o contexto atual não estiver na hierarquia do contexto do bloco
			{
				continue;
			}
			
			$sql = "SELECT count(c.id)
				FROM {content_unlock_log} c
					INNER JOIN {logstore_standard_log} l ON c.logid = l.id
				WHERE l.userid = :userid
					AND c.unlocksystemid = :unlocksystemid";
			$params['userid'] = $event->userid;
			$params['unlocksystemid'] = $unlocksystem->id;
			
			if($DB->count_records_sql($sql, $params) > 0)
			{
				continue;
			}
			
			$manager = get_log_manager();
			$selectreaders = $manager->get_readers('\core\log\sql_reader');
			if ($selectreaders) {
				$reader = reset($selectreaders);
			}
			$selectwhere = "eventname = :eventname
				AND component = :component
				AND action = :action
				AND target = :target
				AND crud = :crud
				AND edulevel = :edulevel
				AND contextid = :contextid
				AND contextlevel = :contextlevel
				AND contextinstanceid = :contextinstanceid
				AND userid = :userid 
				AND anonymous = :anonymous
				AND timecreated = :timecreated";
			$params['eventname'] = $event->eventname;
			$params['component'] = $event->component;
			$params['action'] = $event->action;
			$params['target'] = $event->target;
			$params['crud'] = $event->crud;
			$params['edulevel'] = $event->edulevel;
			$params['contextid'] = $event->contextid;
			$params['contextlevel'] = $event->contextlevel;
			$params['contextinstanceid'] = $event->contextinstanceid;
			$params['userid'] = $event->userid;
			$params['anonymous'] = $event->anonymous;
			$params['timecreated'] = $event->timecreated;

			$logid = $reader->get_events_select($selectwhere, $params, '', 0, 0);
			$logid = array_keys($logid)[0];
			
			$record = new stdClass();
			$record->logid = $logid;
			$record->unlocksystemid = $unlocksystem->id;
			$DB->insert_record('content_unlock_log', $record);
			
			if($unlocksystem->mode == 0) // By visibility mode
			{
				if($unlocksystem->coursemodulevisibility == 1)
				{
					set_section_visible($event->courseid, $ccm[1]->sectionnum, 1);
				}
				set_coursemodule_visible($unlocksystem->coursemoduleid, $unlocksystem->coursemodulevisibility);
			}
			else // By group mode
			{
				groups_add_member($unlocksystem->groupid, $event->userid);
			}
		}
    }
	
	private static function is_available($restrictions, $courseid, $userid)
	{
		global $DB;
		
		if(isset($restrictions))
		{
			$tree = new \core_availability\tree(json_decode($restrictions));
			$course = $DB->get_record('course', array('id' => $courseid));
			$info = new \core_availability\mock_info($course, $userid);
			$result = $tree->check_available(false, $info, true, $userid);
			return $result->is_available();
		}
		
		return true;
	}
	
    protected static function is_student($userid) {
        return user_has_role_assignment($userid, 5);
    }

}
