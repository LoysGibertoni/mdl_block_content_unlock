<?php
 
class block_game_content_unlock_edit_form extends block_edit_form
{
 
    protected function specific_definition($mform)
	{
 		global $COURSE, $DB, $USER;
 
		$context = context_course::instance($COURSE->id);
		if(has_capability('block/game_content_unlock:addunlocksystem', $context))
		{
			$mform->addElement('header', 'configheader', get_string('unlocksystemeditpage', 'block_game_content_unlock'));
			
			$mform->addElement('text', 'config_title', 'Título do bloco');
			$mform->setType('config_title', PARAM_TEXT);
			
			$mform->addElement('select', 'config_lastunlocksnumber', 'Número de últimos desbloqueios exibidos', array(0, 1, 2, 3, 4, 5, 6), null);
			$mform->addRule('config_lastunlocksnumber', null, 'required', null, 'client');
			$mform->setDefault('config_lastunlocksnumber', 1);
			$mform->setType('config_lastunlocksnumber', PARAM_TEXT);
			
			$eventslist = report_eventlist_list_generator::get_non_core_event_list();
			$eventsarray = array();
			foreach($eventslist as $value)
			{
				$description = explode("\\", explode(".", strip_tags($value['fulleventname']))[0]);
				$eventsarray[$value['eventname']] = $description[0] . " (" . $value['eventname'] . ")";
			}
			
			$course = $DB->get_record('course', array('id' => $COURSE->id));
			$info = get_fast_modinfo($course);
			
			$sql = "SELECT *
				FROM {content_unlock_system} s
					INNER JOIN {content_unlock_processor} p ON s.id = p.unlocksystemid
				WHERE p.processorid = :processorid
					AND s.blockinstanceid = :blockinstanceid
					AND s.deleted = 0";
			$params['processorid'] = $USER->id;
			$params['blockinstanceid'] = $this->block->instance->id;
			$unlock_systems = $DB->get_records_sql($sql, $params);
	
			$html = '<table><tr><th>ID</th><th>Condições</th><th>Módulo</th><th>Visibilidade</th><th>Descrição</th><th>Editar</th><th>Remover</th></tr>';
			foreach($unlock_systems as $value)
			{
				$urledit = new moodle_url('/blocks/game_content_unlock/unlocksystemedit.php', array('courseid' => $COURSE->id, 'unlocksystemid' => $value->id));
				$urlremove = new moodle_url('/blocks/game_content_unlock/unlocksystemdelete.php', array('courseid' => $COURSE->id, 'unlocksystemid' => $value->id));
				
				// Evitar que sistemas de outros cursos sejam exibidos - mudar?
				$ccm = get_course_and_cm_from_cmid($value->coursemoduleid);
				if($COURSE->id != $ccm[0]->id)
				{
					continue;
				}
				
				$cm = $info->get_cm($value->coursemoduleid);
				$html = $html . '<tr><td>' . $value->id . '</td><td>' . $eventsarray[$value->conditions] . '</td><td>' . $cm->name . '</td><td>' . $value->coursemodulevisibility . '</td><td>' . $value->eventdescription . '</td><td>' . html_writer::link($urledit, 'Editar') . '</td><td>' . html_writer::link($urlremove, 'Remover') . '</td></tr>';
			}
			
			$url = new moodle_url('/blocks/game_content_unlock/unlocksystemadd.php', array('blockid' => $this->block->instance->id, 'courseid' => $COURSE->id));
			$html = $html . '</table>' . html_writer::link($url, get_string('unlocksystemaddpage', 'block_game_content_unlock'));
			$mform->addElement('html', $html);
			
		}
	}
}

?>