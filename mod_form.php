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

        $mform->addElement('filemanager', 'files', get_string('setting_fileupload', 'mod_gerautog'), null, $this->get_filemanager_options_array()); // Params: 1. type of the element, 2. (html) elementname, 3. label.
        $mform->addHelpButton('files', 'setting_fileupload', 'mod_gerautog');

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

    //if (empty($entry->id)) {
    //    $entry = new stdClass;
    //    $entry->id = null;
    //}

    // Loads the old file in the filemanager.
    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            $contextid = $this->context->id;
            $draftitemid = file_get_submitted_draft_itemid('files');
            file_prepare_draft_area($draftitemid, $contextid, 'mod_pdfannotator', 'content', $entry->id, $this->get_filemanager_options_array());
            $defaultvalues['files'] = $draftitemid;
            $this->_form->disabledIf('files', 'update', 'notchecked', 2);
        }
    }

    private function get_filemanager_options_array()
    {
        global $COURSE;

        return array('subdirs' => true, 'maxbytes' => $COURSE->maxbytes, 'maxfiles' => 1,
                'accepted_types' => array('.mod_pdfannotator'));
    }


    public function validation($data, $files)
    {
        global $USER;

        $errors = parent::validation($data, $files);

        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data['files'], 'sortorder, id', false)) {
            $errors['files'] = get_string('required');
            return $errors;
        }
        if (count($files) == 1) {
            // No need to select main file if only one picked.
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
}
