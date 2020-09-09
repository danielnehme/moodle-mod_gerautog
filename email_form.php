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
 * Prints an instance of mod_gerautog.
 *
 * @package     mod_gerautog
 * @copyright   2020 Nasnuv <tecnologia@nasnuv.com.br>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Description of email form
 *
 * @author Daniel Muller
 */
class gerautog_email_form extends moodleform {

    public function definition() {
        global $COURSE;

        $mform = $this->_form;

        // Course module id.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Email to...
        $mform->addElement('text', 'emailto', get_string('emailto', 'mod_gerautog'), array('size' => '40', 'maxsize' => '200'));
        $mform->setType('emailto', PARAM_TEXT);
        //$mform->addRule('emailto', null, 'required', null, 'client');
        $mform->addHelpButton('emailto', 'emailto', 'mod_gerautog');

        // Textarea for message to the reader.
        $mform->addElement('textarea', 'message', get_string('message_book', 'mod_gerautog'), 'wrap="virtual" rows="5" cols="109"');
        $mform->addHelpButton('message', 'message_book', 'mod_gerautog');

        // Get autograph image
        $mform->addElement('filemanager', 'autog', get_string('autog_book', 'mod_gerautog'), null, $this->get_filemanager_options_array());
        //$mform->addRule('autog', null, 'required', null, 'client');
        $mform->addHelpButton('autog', 'autog_book', 'mod_gerautog');
//var_dump($mform);
        // Add submit and cancel buttons.
        $this->add_action_buttons(false, get_string('send', 'mod_gerautog'));
    }

    public function data_preprocessing(&$data) {

        parent::data_preprocessing($data);
        if ($this->current->instance) {
            $contextid = $this->context->id;
            $draftitemid = file_get_submitted_draft_itemid('autog');
            file_prepare_draft_area($draftitemid, $contextid, 'mod_gerautog', 'autog', 1, $this->get_filemanager_options_array());
            $data['autog'] = $draftitemid;
        }
    }

    public function data_postprocessing($data) {

        // File manager always creata a Files folder, so certimages is never empty.
        // I must check if it has a file or it's only a empty files folder reference.
        if (isset($data->autog) && !empty($data->autog)
            && !$this->check_has_files('autog')) {
                $data->autog = null;
        }
    }


    private function check_has_files($itemname) {
        global $USER;

        $draftitemid = file_get_submitted_draft_itemid($itemname);
        file_prepare_draft_area($draftitemid, $this->context->id, 'mod_gerautog', 'autog', null,
                                $this->get_filemanager_options_array());

        // Get file from users draft area.
        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
        //var_dump($files);
        return (count($files) > 0);
    }

    private function get_filemanager_options_array()
    {
        global $COURSE;

        return array('subdirs' => true, 'maxbytes' => $COURSE->maxbytes, 'maxfiles' => 1,
                'accepted_types' => array('.jpg','.png'));
    }
}
