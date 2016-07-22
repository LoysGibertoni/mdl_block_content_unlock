<?php

require_once("{$CFG->libdir}/formslib.php");
 
class block_game_content_unlock_delete_form extends moodleform
{

    function definition()
	{
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('unlocksystemdeleteheading', 'block_game_content_unlock'));

		$mform->addElement('html', get_string('unlocksystemdeletemessage', 'block_game_content_unlock'));
		
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
		$mform->addElement('hidden', 'unlocksystemid');
		$mform->setType('unlocksystemid', PARAM_INT);
		
		$this->add_action_buttons(true, 'Salvar alterações');
    }
}

?>