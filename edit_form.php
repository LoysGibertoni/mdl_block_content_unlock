<?php
 
class block_game_content_unlock_edit_form extends block_edit_form
{
 
    protected function specific_definition($mform)
	{
 		global $COURSE;
 
		$context = context_course::instance($COURSE->id);
		if(has_capability('block/game_content_unlock:addunlocksystem', $context))
		{
			$mform->addElement('header', 'configheader', get_string('unlocksystemeditpage', 'block_game_content_unlock'));
			
			$url = new moodle_url('/blocks/game_content_unlock/unlocksystemadd.php', array('blockid' => $this->block->instance->id, 'courseid' => $COURSE->id));
			$mform->addElement('html', html_writer::link($url, get_string('unlocksystemaddpage', 'block_game_content_unlock')));
			
		}
	}
}

?>