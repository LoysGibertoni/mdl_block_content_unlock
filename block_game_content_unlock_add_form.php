<?php

require_once("{$CFG->libdir}/formslib.php");
 
class block_game_content_unlock_add_form extends moodleform
{
 
	function __construct($courseid)
	{
		$this->courseid = $courseid;
		parent::__construct();
	}
 
    function definition()
	{
		global $DB;
 
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('unlocksystemaddheading', 'block_game_content_unlock'));

		$eventslist = report_eventlist_list_generator::get_non_core_event_list();
		$eventsarray = array();
		foreach($eventslist as $value)
		{
			$description = explode("\\", explode(".", strip_tags($value['fulleventname']))[0]);
			$eventsarray[$value['eventname']] = $description[0] . " (" . $value['eventname'] . ")";
		}
		$mform->addElement('select', 'event', 'Evento', $eventsarray, null);
		$mform->addRule('event', null, 'required', null, 'client');
		
		$mform->addElement('text', 'description', 'Descrição');
		
		$coursemodulessarray = array();
		$course = $DB->get_record('course', array('id' => $this->courseid));
		$info = get_fast_modinfo($course);
		foreach($info->cms as $cm)
		{
			$coursemodulessarray[$cm->id] = $cm->name;
		}
		$mform->addElement('select', 'coursemodule', 'Módulo', $coursemodulessarray, null);
		$mform->addRule('coursemodule', null, 'required', null, 'client');
		
		$mform->addElement('hidden', 'blockid');
		$mform->addElement('hidden', 'courseid');
		
		$this->add_action_buttons(true, 'Adicionar');
    }
}

?>