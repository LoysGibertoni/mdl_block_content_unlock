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
	
    public function get_content()
	{		
		global $DB;
		
		$this->content = new stdClass;
		$this->content->text = 'Sistema de desbloqueio de conteúdo';
		$this->content->footer = 'Teste';
		
		return $this->content;
    }

    public function has_config()
	{
        return true;
    }
}

?>