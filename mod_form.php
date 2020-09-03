<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The main mod_gerautog configuration form.
 *
 * @package     mod_gerautog
 * @copyright   2020 Nasnuv <tecnologia@nasnuv.com.br>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_gerautog
 * @copyright  2020 Nasnuv <tecnologia@nasnuv.com.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mod_gerautog_mod_form extends moodleform_mod
{

    /**
     * Defines forms elements
     */
    public function definition()
    {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('gerautogname', 'mod_gerautog'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'gerautogname', 'mod_gerautog');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the rest of mod_gerautog settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        //$mform->addElement('static', 'label1', 'gerautogsettings', get_string('gerautogsettings', 'mod_gerautog'));
        //$mform->addElement('header', 'gerautogfieldset', get_string('gerautogfieldset', 'mod_gerautog'));



        // Add a filemanager for drag-and-drop file upload.
        // $fileoptions = array('subdirs' => 0, 'maxbytes' => 0, 'areamaxbytes' => 10485760, 'maxfiles' => 1,
        // 'accepted_types' => '.pdf', 'return_types' => 1 | 2);
        // FILE_INTERNAL | FILE_EXTERNAL was replaced by 1|2, because moodle doesnt't identify FILE_INTERNAL, FILE_EXTERNAL here.
        /*
        $filemanageroptions = array();
        $filemanageroptions['accepted_types'] = array('.pdf');
        $filemanageroptions['maxbytes'] = 0;
        $filemanageroptions['maxfiles'] = 1; // Upload only one file.
        $filemanageroptions['subdirs'] = 0;
        $filemanageroptions['return_types'] = FILE_INTERNAL | FILE_EXTERNAL;
        */

        $mform->addElement('filemanager', 'arqs', get_string('setting_fileupload', 'mod_gerautog'), null, $this->get_filemanager_options_array());
        $mform->addHelpButton('arqs', 'setting_fileupload', 'mod_gerautog');


        // Issue options.

        $mform->addElement('header', 'issueoptions', get_string('issueoptions', 'mod_gerautog'));

        // Email to teachers ?
        $mform->addElement('selectyesno', 'emailauthors', get_string('emailauthors', 'mod_gerautog'));
        $mform->setDefault('emailauthors', 0);
        $mform->addHelpButton('emailauthors', 'emailauthors', 'mod_gerautog');

        // Email Others.
        $mform->addElement('text', 'emailothers', get_string('emailothers', 'mod_gerautog'), array('size' => '40', 'maxsize' => '200'));
        $mform->setType('emailothers', PARAM_TEXT);
        $mform->addHelpButton('emailothers', 'emailothers', 'mod_gerautog');

        // Email From.
        $mform->addElement('text', 'emailfrom', get_string('emailfrom', 'mod_gerautog'), array('size' => '40', 'maxsize' => '200'));
        $mform->setDefault('emailfrom', $CFG->supportname);
        $mform->setType('emailfrom', PARAM_EMAIL);
        $mform->addHelpButton('emailfrom', 'emailfrom', 'mod_gerautog');
        $mform->setAdvanced('emailfrom');

        // Delivery Options (Email, Download,...).
        $deliveryoptions = array(
            0 => get_string('openbrowser', 'mod_gerautog'),
            1 => get_string('download', 'mod_gerautog'),
            2 => get_string('emailbook', 'mod_gerautog'),
            3 => get_string('nodelivering', 'mod_gerautog'),
            4 => get_string('emailoncompletion', 'mod_gerautog'),
        );

        $mform->addElement('select', 'delivery', get_string('delivery', 'mod_gerautog'), $deliveryoptions);
        $mform->setDefault('delivery', 0);
        $mform->addHelpButton('delivery', 'delivery', 'mod_gerautog');

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }


/*
    // Loads the old file in the filemanager.
    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            $contextid = $this->context->id;
            $draftitemid = file_get_submitted_draft_itemid('arqs');
            file_prepare_draft_area($draftitemid, $contextid, 'mod_gerautog', 'content', 1, $this->get_filemanager_options_array());
            $defaultvalues['arqs'] = $draftitemid;
            //$this->_form->disabledIf('arqs', 'update', 'notchecked', 2);
        }
    }
*/

    /**
     * Prepares the form before data are set
     *
     * Additional wysiwyg editor are prepared here, the introeditor is prepared automatically by core.
     * Grade items are set here because the core modedit supports single grade item only.
     *
     * @param array $data to be set
     * @return void
     */
    public function data_preprocessing(&$data) {
        global $CFG;
        //require_once(dirname(__FILE__) . '/locallib.php');
        parent::data_preprocessing($data);
        if ($this->current->instance) {
            $contextid = $this->context->id;
            $draftitemid = file_get_submitted_draft_itemid('arqs');
            file_prepare_draft_area($draftitemid, $contextid, 'mod_gerautog', 'arqs', 1, $this->get_filemanager_options_array());
            $data['arqs'] = $draftitemid;

            // Prepare certificate text.
            //$data['certificatetext'] = array('text' => $data['certificatetext'], 'format' => FORMAT_HTML);

        } else { // Load default.
            //$data['certificatetext'] = array('text' => '', 'format' => FORMAT_HTML);
        }

        // Completion rules.
        //$data['completiontimeenabled'] = !empty($data['requiredtime']) ? 1 : 0;

    }



    public function data_postprocessing($data) {

        // File manager always creata a Files folder, so certimages is never empty.
        // I must check if it has a file or it's only a empty files folder reference.
        if (isset($data->arqs) && !empty($data->arqs)
            && !$this->check_has_files('arqs')) {
                $data->arqs = null;

        }
    }


    private function check_has_files($itemname) {
        global $USER;

        $draftitemid = file_get_submitted_draft_itemid($itemname);
        file_prepare_draft_area($draftitemid, $this->context->id, 'mod_gerautog', 'arqs', null,
                                $this->get_filemanager_options_array());

        // Get file from users draft area.
        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);

        return (count($files) > 0);
    }


    private function get_filemanager_options_array()
    {
        global $COURSE;

        return array('subdirs' => true, 'maxbytes' => $COURSE->maxbytes, 'maxfiles' => 1,
                'accepted_types' => array('.pdf'));
    }

    /**
     * Some basic validation
     *
     * @param $data
     * @param $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);


        return $errors;
    }
    /*
    public function validation($data, $files)
    {
        global $USER;

        $errors = parent::validation($data, $files);

        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data['arqs'], 'sortorder, id', false)) {
            $errors['arqs'] = get_string('required');
            return $errors;
        }
        if (count($files) == 1) {
            // No need to select main file if only one picked.
            var_dump($data);
            // Save file
            $fileinfo = array('contextid' => $this->context->id,
                              'component' => 'mod_gerautog',
                              'filearea' => 'arqs',
                              'itemid' => 1,
                              'filepath' => '/');
            $data['arqs'] = $this->save_upload_file($data['arqs'], $fileinfo);
            //file_save_draft_area_files($data['arqs'], $this->context->id, 'mod_gerautog', 'arqs', 1, $this->get_filemanager_options_array());
            //var_dump($data);

            return $errors;
        } else if (count($files) > 1) {
            $mainfile = false;
            foreach ($files as $file) {
                if ($file->get_sortorder() == 1) {
                    $mainfile = true;
                    break;
                }
            }
            // Set a default main file.
            if (!$mainfile) {
                $file = reset($files);
                file_set_sortorder($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), 1);
            }
        }
        return $errors;
    }
*/

    /**
     * Save upload files in $fileinfo array and return the filename
     *
     * @param string $formitemid Upload file form id
     * @param array $fileinfo The file info array, where to store uploaded file
     * @return string filename
     */
    private function save_upload_file($formitemid, array $fileinfo) {
        // Clear file area.
        if (empty($fileinfo['itemid'])) {
            $fileinfo['itemid'] = '';
        }

        $fs = get_file_storage();
        $fs->delete_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid']);
        file_save_draft_area_files($formitemid, $fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                                $fileinfo['itemid']);
        // Get only files, not directories.
        $files = $fs->get_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], '',
                                    false);
        $file = array_shift($files);
        return $file->get_filename();
    }
}
