<?php
// This file is part of Moodle - http://moodle.org/
//
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
 * Content unlock block functions definitions.
 *
 * @package    block_game_content_unlock
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function content_unlock_generate_events_list($showeventname = false)
{
	global $DB;
	
	$eventsarray = array();

	$eventsarray['\block_game_content_unlock\event\content_unlocked'] = ($showeventname === true ? (\block_game_content_unlock\event\content_unlocked::get_name() . " (\block_game_content_unlock\event\content_unlocked)") : \block_game_content_unlock\event\content_unlocked::get_name());

	$game_achievements_installed = $DB->record_exists('block', array('name' => 'game_achievements'));
	if($game_achievements_installed)
	{
		$eventsarray['\block_game_achievements\event\achievement_reached'] = ($showeventname === true ? (\block_game_achievements\event\achievement_reached::get_name() . " (\block_game_achievements\event\achievement_reached)") : \block_game_achievements\event\achievement_reached::get_name());
	}

	$game_points_installed = $DB->record_exists('block', array('name' => 'game_points'));
	if($game_points_installed)
	{
		$eventsarray['\block_game_points\event\points_earned'] = ($showeventname === true ? (\block_game_points\event\points_earned::get_name() . " (\block_game_points\event\points_earned)") : \block_game_points\event\points_earned::get_name());
	}
	
	$eventslist = report_eventlist_list_generator::get_non_core_event_list();
	foreach($eventslist as $value)
	{
		$description = explode("\\", explode(".", strip_tags($value['fulleventname']))[0]);
		$eventsarray[$value['eventname']] = ($showeventname === true ? ($description[0] . " (" . $value['eventname'] . ")") : $description[0]);
	}
	
	return $eventsarray;
}

function content_unlock_get_block_conditions_text($unlock_system)
{
	global $DB;

	$conditions_text = array();
	$unlock_system_block_conditions = $DB->get_records('content_unlock_condition', array('unlocksystemid' => $unlock_system->id));

	foreach($unlock_system_block_conditions as $unlock_system_block_condition)
	{
		if($unlock_system_block_condition->type == 0) // By points
		{
			$block_info = null;
			if(isset($unlock_system_block_condition->prpointsystemid))
			{
				$block_instance_id = $DB->get_field('points_system', 'blockinstanceid', array('id' => $unlock_system_block_condition->prpointsystemid));
				$block_info = $DB->get_record('block_instances', array('id' => $block_instance_id));
			}
			else
			{
				$block_info = $DB->get_record('block_instances', array('id' => $unlock_system_block_condition->prblockid));
			}
			$instance = block_instance('game_points', $block_info);
			
			$conditions_text[] = get_string('block_conditions_reach', 'block_game_content_unlock') . ' ' . $unlock_system_block_condition->prpoints . ' ' . get_string('block_conditions_points', 'block_game_content_unlock') . ' (' . ($unlock_system_block_condition->prgrupal ? get_string('block_conditions_grupal', 'block_game_content_unlock') : get_string('block_conditions_individual', 'block_game_content_unlock')) . ') ' . get_string('block_conditions_on', 'block_game_content_unlock') . ' ' . (isset($unlock_system_block_condition->prblockid) ? get_string('block_conditions_block', 'block_game_content_unlock') . ' ' . $instance->title  : get_string('block_conditions_pointsystem', 'block_game_content_unlock') . ' ' . $unlock_system_block_condition->prpointsystemid . ' (' . get_string('block_conditions_block', 'block_game_content_unlock') . ' ' . $instance->title . ')' );
		}
		else if($unlock_system_block_condition->type == 1) // By content unlock
		{
			$condition_unlock_system= $DB->get_record('content_unlock_system', array('id' => $unlock_system_block_condition->urunlocksystemid));

			$course = $DB->get_record('course', array('id' => $this->page->course->id));
			$info = get_fast_modinfo($course);
			$cm = $info->get_cm($condition_unlock_system->coursemoduleid);

			$block_info = $DB->get_record('block_instances', array('id' => $condition_unlock_system->blockinstanceid));
			$instance = block_instance('game_content_unlock', $block_info);
			
			
			$conditions_text[] = ($unlock_system_block_condition->urmust ? get_string('block_conditions_have', 'block_game_content_unlock') : get_string('block_conditions_havenot', 'block_game_content_unlock')) . ' ' . ($condition_unlock_system->coursemodulevisibility ? get_string('block_conditions_unlocked', 'block_game_content_unlock') : get_string('block_conditions_locked', 'block_game_content_unlock')) . ' ' . get_string('block_conditions_resource', 'block_game_content_unlock') . ' ' . $cm->name . ' (' . get_string('block_conditions_block', 'block_game_content_unlock') . ' ' . $instance->title . ')';
		}
		else // By achievement reached
		{
			$condition_achievement = $DB->get_record('achievements', array('id' => $unlock_system_block_condition->arachievementid));

			$block_info = $DB->get_record('block_instances', array('id' => $condition_achievement->blockinstanceid));
			$instance = block_instance('game_achievements', $block_info);
			
			$conditions_text[] = get_string('block_conditions_reach', 'block_game_content_unlock') . ' ' . get_string('block_conditions_achievement', 'block_game_content_unlock') . ' ' . (isset($condition_achievement->name) ? $condition_achievement->name . ' (' . $condition_achievement->id . ')' : $condition_achievement->id)   . ' (' . get_string('block_conditions_block', 'block_game_content_unlock') . ' ' . $instance->title . ')';
		}

	}

	return implode(' ' . ($unlock_system->connective == AND_CONNECTIVE ? get_string('block_conditions_and', 'block_game_content_unlock') : get_string('block_conditions_or', 'block_game_content_unlock')) . ' ', $conditions_text);
}

function content_unlock_satisfies_conditions($conditions, $courseid, $userid)
{
	global $DB;
	
	if(isset($conditions))
	{
		$tree = new \core_availability\tree(json_decode($conditions));
		$course = $DB->get_record('course', array('id' => $courseid));
		$info = new content_unlock_conditions_info($course);
		$result = $tree->check_available(false, $info, true, $userid);
		return $result->is_available();
	}
	
	return true;
}

function content_unlock_satisfies_block_conditions($unlock_system, $courseid, $userid)
{
	global $DB;
	$unlock_system_conditions = $DB->get_records('content_unlock_condition', array('unlocksystemid' => $unlock_system->id));
	$satisfies_conditions = $unlock_system->connective == AND_CONNECTIVE ? true : false;
	if(empty($unlock_system_conditions))
	{
		$satisfies_conditions = true;
	}
	else
	{
		foreach($unlock_system_conditions as $unlock_system_condition)
		{
			if($unlock_system_condition->type == 0) // Restrição por pontos
			{
				$points = 0;
				if(isset($unlock_system_condition->prblockid)) // Se a restrição for por pontos no bloco
				{
					if($unlock_system_condition->prgrupal)
					{
						$user_groups = groups_get_all_groups($courseid, $userid, $unlock_system->groupingid);
						foreach($user_groups as $user_group)
						{
							$group_points = get_block_group_points($unlock_system_condition->prblockid, $user_group->id);
							if($group_points > $points)
							{
								$points = $group_points;
							}
						}
					}
					else
					{
						$points = get_points($unlock_system_condition->prblockid, $userid);
					}
				}
				else // Se a restrição for por pontos em um sistema de pontos específico
				{
					if($unlock_system_condition->prgrupal)
					{
						$user_groups = groups_get_all_groups($courseid, $userid, $unlock_system->groupingid);
						foreach($user_groups as $user_group)
						{
							$group_points = get_points_system_group_points($unlock_system_condition->prpointsystemid, $user_group->id);
							if($group_points > $points)
							{
								$points = $group_points;
							}
						}
					}
					else
					{
						$points = get_points_system_points($unlock_system_condition->prpointsystemid, $userid);
					}
				}
				
				
				if($points >= $unlock_system_condition->prpoints) // Se satisfaz a condição
				{
					if($unlock_system->connective == OR_CONNECTIVE) // E se o conectivo for OR
					{
						$satisfies_conditions = true;
						break;
					}
				}
				else // Se não satisfaz a condição
				{
					if($unlock_system->connective == AND_CONNECTIVE) // E se o conectivo for AND
					{
						$satisfies_conditions = false;
						break;
					}
				}
			}
			else if($unlock_system_condition->type == 1) // Restrição por conteúdo desbloqueado
			{
				$sql = "SELECT count(u.id) as times
					FROM
						{content_unlock_log} u
					INNER JOIN {logstore_standard_log} l ON u.logid = l.id
					WHERE l.userid = :userid
						AND  u.unlocksystemid = :unlocksystemid
					GROUP BY l.userid";
				$params['unlocksystemid'] = $unlock_system_condition->urunlocksystemid;
				$params['userid'] = $userid;
				
				$times = $DB->get_field_sql($sql, $params);

				if(!isset($times))
				{
					$times = 0;
				}
				
				if(($unlock_system_condition->urmust && $times > 0) || (!$unlock_system_condition->urmust && $times == 0)) // Se satisfaz a condição
				{
					if($unlock_system->connective == OR_CONNECTIVE) // E se o conectivo for OR
					{
						$satisfies_conditions = true;
						break;
					}
				}
				else // Se não satisfaz a condição
				{
					if($unlock_system->connective == AND_CONNECTIVE) // E se o conectivo for AND
					{
						$satisfies_conditions = false;
						break;
					}
				}
			}
			else // Restrição por conquista atingida
			{
				$unlocked_achievement = $DB->record_exists('achievements_log', array('userid' => $userid, 'achievementid' => $unlock_system_condition->arachievementid));
				if($unlocked_achievement) // Se satisfaz a condição
				{
					if($unlock_system->connective == OR_CONNECTIVE) // E se o conectivo for OR
					{
						$satisfies_conditions = true;
						break;
					}
				}
				else // Se não satisfaz a condição
				{
					if($unlock_system->connective == AND_CONNECTIVE) // E se o conectivo for AND
					{
						$satisfies_conditions = false;
						break;
					}
				}
			}
		}
	}
	
	return $satisfies_conditions;
}

class content_unlock_conditions_info extends \core_availability\info
{
    public function __construct($course = null)
	{
        global $SITE;
        if (!$course) {
            $course = $SITE;
        }
        parent::__construct($course, true, null);
    }

    protected function get_thing_name()
	{
        return 'Conditions';
    }

    public function get_context()
	{
        return \context_course::instance($this->get_course()->id);
    }

    protected function get_view_hidden_capability()
	{
        return 'moodle/course:viewhiddensections';
    }

    protected function set_in_database($availability)
	{
    }
	
	public function get_modinfo() {
        return get_fast_modinfo($this->course);
    }
}
