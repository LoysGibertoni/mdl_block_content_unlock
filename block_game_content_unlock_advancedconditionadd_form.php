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
 * Unlock system add advanced condition form definition.
 *
 * @package    block_game_content_unlock
 * @copyright  20016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/formslib.php");

class block_game_content_unlock_advancedconditionadd_form extends moodleform
{
 
    function definition()
	{
		global $DB, $COURSE;
 
		$mform =& $this->_form;
		$mform->addElement('header','displayinfo', get_string('advancedconditionaddheading', 'block_game_content_unlock'));
		
		$mform->addElement('hidden', 'unlocksystemid');
		$mform->setType('unlocksystemid', PARAM_INT);
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
    }

	public function definition_after_data()
	{
		global $DB, $USER;
        parent::definition_after_data();
				
        $mform =& $this->_form;
		
        $unlocksystemid_element = $mform->getElement('unlocksystemid');
        $unlocksystemid = $unlocksystemid_element->getValue();
		
		$courseid_element = $mform->getElement('courseid');
        $courseid = $courseid_element->getValue();

		$mform->addElement('textarea', 'whereclause', get_string("advancedconditionaddselect", 'block_game_content_unlock'));
		$mform->addRule('whereclause', null, 'required', null, 'client');

		$options = array(
			0 => get_string('advancedconditionaddtrueifzero', 'block_game_content_unlock'),
			1 => get_string('advancedconditionaddtrueifnotzero', 'block_game_content_unlock'),
			2 => get_string('advancedconditionaddtrueifegthan', 'block_game_content_unlock')
		);
		$mform->addElement('select', 'trueif',  get_string('advancedconditionaddtrueif', 'block_game_content_unlock'), $options, null);
		$mform->addRule('trueif', null, 'required', null, 'client');

		$mform->addElement('text', 'count',  get_string('advancedconditionaddcount', 'block_game_content_unlock'));
		$mform->disabledIf('count', 'trueif', 'neq', 2);

		$this->add_action_buttons(true, get_string('advancedconditionaddbutton', 'block_game_content_unlock'));
    }
}

?>