<?php

require_once("{$CFG->libdir}/formslib.php");
 
class block_game_content_unlock_edit_form extends moodleform
{
 
	function __construct($id, $courseid)
	{
		$this->id = $id;
		$this->courseid = $courseid;
		parent::__construct();
	}
 
    function definition()
	{
		global $DB, $COURSE;
 
		$unlocksystem = $DB->get_record('content_unlock_system', array('id' => $this->id));
 
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('unlocksystemeditheading', 'block_game_content_unlock'));

		$eventslist = report_eventlist_list_generator::get_non_core_event_list();
		$eventsarray = array();
		foreach($eventslist as $value)
		{
			$description = explode("\\", explode(".", strip_tags($value['fulleventname']))[0]);
			$eventsarray[$value['eventname']] = $description[0] . " (" . $value['eventname'] . ")";
		}
		$select = $mform->addElement('select', 'event', 'Evento', $eventsarray, null);
		$mform->addRule('event', null, 'required', null, 'client');
		$mform->setType('event', PARAM_TEXT);
		$select->setSelected($unlocksystem->conditions);
		
		$mform->addElement('text', 'description', 'Descrição');
		$mform->setType('description', PARAM_TEXT);
		$mform->setDefault('description', $unlocksystem->eventdescription);
		
		$coursemodulessarray = array();
		$course = $DB->get_record('course', array('id' => $this->courseid));
		$info = get_fast_modinfo($course);
		foreach($info->cms as $cm)
		{
			$coursemodulessarray[$cm->id] = $cm->name;
		}
		$select = $mform->addElement('select', 'coursemodule', 'Módulo', $coursemodulessarray, null);
		$mform->addRule('coursemodule', null, 'required', null, 'client');
		$mform->setType('coursemodule', PARAM_INT);
		$select->setSelected($unlocksystem->coursemoduleid);
		
		$select = $mform->addElement('select', 'mode', 'Modo', array(0 => 'Por visibilidade', 1 => 'Por grupo'), null);
		$mform->addRule('mode', null, 'required', null, 'client');
		$mform->setType('mode', PARAM_INT);
		$select->setSelected($unlocksystem->mode);

		$mform->addElement('html', '<hr></hr>');

		$select = $mform->addElement('select', 'coursemodulevisibility', 'Visibilidade', array(0 => 'Ocultar', 1 => 'Mostrar'), null);
		$mform->setType('coursemodulevisibility', PARAM_INT);
		$mform->disabledIf('coursemodulevisibility', 'mode', 'neq', 0);
		$select->setSelected($unlocksystem->coursemodulevisibility);
		
		// Restrictions
		$mform->addElement('header', 'availabilityconditionsheader', get_string('restrictaccess', 'availability'));
		$mform->addElement('textarea', 'availabilityconditionsjson', get_string('accessrestrictions', 'availability'));
		$mform->setDefault('availabilityconditionsjson', $unlocksystem->restrictions);
		\core_availability\frontend::include_all_javascript($COURSE, null);
		$mform->setType('availabilityconditionsjson', PARAM_TEXT);
		
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
		$mform->addElement('hidden', 'unlocksystemid');
		$mform->setType('unlocksystemid', PARAM_INT);
		
		$this->add_action_buttons(true, 'Salvar alterações');
    }
}

?>