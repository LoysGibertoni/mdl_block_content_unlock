<?php

require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot . '/blocks/game_content_unlock/lib.php');

class block_game_content_unlock_add_form extends moodleform
{
 
	function __construct($courseid)
	{
		$this->courseid = $courseid;
		parent::__construct();
	}
 
    function definition()
	{
		global $DB, $COURSE;
 
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('unlocksystemaddheading', 'block_game_content_unlock'));

		$eventsarray = content_unlock_generate_events_list();
		$mform->addElement('select', 'event', 'Evento', $eventsarray, null);
		$mform->addRule('event', null, 'required', null, 'client');
		$mform->setType('event', PARAM_TEXT);
		
		$mform->addElement('text', 'description', 'Descrição');
		$mform->setType('description', PARAM_TEXT);
		
		$coursemodulessarray = array();
		$course = $DB->get_record('course', array('id' => $this->courseid));
		$info = get_fast_modinfo($course);
		foreach($info->cms as $cm)
		{
			$coursemodulessarray[$cm->id] = $cm->name;
		}
		$mform->addElement('select', 'coursemodule', 'Módulo', $coursemodulessarray, null);
		$mform->addRule('coursemodule', null, 'required', null, 'client');
		$mform->setType('coursemodule', PARAM_INT);
		
		$mform->addElement('select', 'mode', 'Modo', array(0 => 'Por visibilidade', 1 => 'Por grupo'), null);
		$mform->addRule('mode', null, 'required', null, 'client');
		$mform->setType('mode', PARAM_INT);

		$mform->addElement('html', '<hr></hr>');

		$mform->addElement('select', 'coursemodulevisibility', 'Visibilidade', array(0 => 'Ocultar', 1 => 'Mostrar'), null);
		$mform->setType('coursemodulevisibility', PARAM_INT);
		$mform->disabledIf('coursemodulevisibility', 'mode', 'neq', 0);
		
		$mform->addElement('hidden', 'blockid');
		$mform->setType('blockid', PARAM_INT);
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
		
		$this->add_action_buttons(true, 'Adicionar');
    }
}

?>