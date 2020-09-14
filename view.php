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
 * Prints an instance of mod_gerautog.
 *
 * @package     mod_gerautog
 * @copyright   2020 Nasnuv <tecnologia@nasnuv.com.br>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//global $CFG;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

require_once($CFG->dirroot . '/mod/gerautog/lib.php');
require_once($CFG->dirroot . '/mod/gerautog/email_form.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/grade/querylib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/course/moodleform_mod.php');

use core_availability\info;
use core_availability\info_module;
use core\message\inbound\private_files_handler;

require_once($CFG->libdir . '/filelib.php');
//require_once($CFG->libdir . '/tcpdf/tcpdf.php');
//require_once($CFG->libdir . '/pdflib.php');

//use setasign\Fpdi\Fpdi;
//require_once($CFG->dirroot . '/mod/gerautog/libext/fpdf/fpdf.php');
//require_once($CFG->dirroot . '/mod/gerautog/libext/fpdi/autoload.php');
use setasign\Fpdi\TcpdfFpdi;
require_once($CFG->libdir.'/pdflib.php');
require_once($CFG->dirroot.'/mod/assign/feedback/editpdf/fpdi/autoload.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$g  = optional_param('g', 0, PARAM_INT);


$action = optional_param('action', '', PARAM_ALPHA);


if ($id) {
    $cm             = get_coursemodule_from_id('gerautog', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('gerautog', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($g) {
    $moduleinstance = $DB->get_record('gerautog', array('id' => $n), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('gerautog', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'mod_gerautog'));
}


require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

require_capability('mod/gerautog:view', $modulecontext);

$url = new moodle_url('/mod/gerautog/view.php', array ('id' => $cm->id));
/*
$event = \mod_gerautog\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('gerautog', $moduleinstance);
$event->trigger();
*/

//$PAGE->set_url('/mod/gerautog/view.php', array('id' => $cm->id));
$PAGE->set_url($url);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_cm($cm);

if ($action) {
    $url->param ('action', $action);
}

if (!$url->get_param('action')) {

  echo $OUTPUT->header();

  //var_dump($cm);
  //var_dump($course);
  //var_dump($moduleinstance);
  //var_dump($modulecontext);

  $data = new stdClass();
  $data->course = $cm->course;
  $data->id = $cm->id;

  $mform = new gerautog_email_form();

  if (!$mform->get_data()) {
      $mform->set_data($data);
      $mform->display();
  }
  else {
		global $USER;
		$usercontext = context_user::instance($USER->id);
    $data = $mform->get_data();


    $arq = null;

    //$pdf = new Fpdi();
    $pdf = new TcpdfFpdi();

    $cxt = context_module::instance($id);
    $fs = get_file_storage();
    //$files = $fs->get_area_files($cxt->id, 'mod_gerautog', 'arqs', $id);
		$draftitemid = file_get_submitted_draft_itemid('arqs');
		file_prepare_draft_area($draftitemid, $cxt->id, 'mod_gerautog', 'arqs', 1, array('subdirs' => false,  'maxfiles' => 1,'accepted_types' => array('.pdf')));
		$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
    //var_dump($files);
    foreach ($files as $f) {
        // $f is an instance of stored_file
        //echo $f->get_filename() . '<br />';
        if (strlen($f->get_filename()) > 1) {
            //echo $f->get_filename() . '<br />';
            $arq = $f;
        }
    }

  	$book = $fs->get_file($arq->get_contextid(), $arq->get_component(), $arq->get_filearea(), $arq->get_itemid(), $arq->get_filepath(), $arq->get_filename());

    // Read contents.
    if ($book) {
      $tmpfilename = $book->copy_content_to_temp('mod_gerautog', 'book_');
      $pageCount = $pdf->setSourceFile($tmpfilename);
      @unlink($tmpfilename);

      for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
				// import a page
        $templateId = $pdf->importPage($pageNo);
				$pdf->AddPage();
				// use the imported page and adjust the page size
				$pdf->useTemplate($templateId, ['adjustPageSize' => true]);
				if ($pageNo == 2) {
          $pdf->SetFont('freesans', 'I', '12');
          if($data->message) $texto=$data->message;
          else $texto='É um teste Nasnuv!';
          $pdf->SetXY(80,130);
          $pdf->MultiCell(50,0,$texto,0,'L');


          $draftitemid = file_get_submitted_draft_itemid('autog');
          file_prepare_draft_area($draftitemid, $modulecontext->id, 'mod_gerautog', 'autog', null, array('subdirs' => false, 'maxfiles' => 1,'accepted_types' => array('image')));

          $fs = get_file_storage();
          $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
					//var_dump($files);
          foreach ($files as $f) {
              //echo $f->get_filename() . '<br />';
              if (strlen($f->get_filename()) > 1) {
                  //echo $f->get_filename() . '<br />';
                  $arq_img = $f;
              }
          }

          $imgarq = $fs->get_file($arq_img->get_contextid(), $arq_img->get_component(), $arq_img->get_filearea(), $arq_img->get_itemid(), $arq_img->get_filepath(), $arq_img->get_filename());

          if ($imgarq) {
              $ext = pathinfo($arq_img->get_filename(), PATHINFO_EXTENSION);
              $tmpfilename = $imgarq->copy_content_to_temp('mod_gerautog', 'img_');
              try{
                  $pdf->Image($tmpfilename,90,150,0,0,$ext);
              }
              catch (Exception $e){
                  $msge = $e->getMessage();
                  $msgc = "FPDF error: Interlacing not supported: " . $tmpfilename;
                  if (strcmp($msge,$msgc)==0) {
                      $str = get_string('pngerror', 'mod_gerautog');
                      echo html_writer::tag('p', $str, array('style' => 'text-align:center'));
                  }
                  @unlink($tmpfilename);
              }
              @unlink($tmpfilename);
          }
				}
      }

      $arqn = 'aut_' . $arq->get_filename();
      $fs->delete_area_files($usercontext->id,'mod_gerautog','temp');
      $fileinfo = array('contextid' => $usercontext->id,
                          'component' => 'mod_gerautog',
                          'filearea' => 'temp',
                          'itemid' => 1,
                          'filepath' => '/',
                          'mimetype' => 'application/pdf',
                          'userid' => $USER->id,
                          'filename' => $arqn
                  );
      $file = $fs->create_file_from_string($fileinfo, $pdf->Output('', 'S'));
			//var_dump($file);

    } else {
    print_error(get_string('filenotfound', 'mod_gerautog'));
    }

		/*
		case self::OUTPUT_SEND_EMAIL:
		                    $this->send_certificade_email($issuecert);
		                    echo $OUTPUT->header();
		                    echo $OUTPUT->box(get_string('emailsent', 'simplecertificate') . '<br>' . $OUTPUT->close_window_button(),
		                                    'generalbox', 'notice');
		                    echo $OUTPUT->footer();
		                break;

		*/
		$str = get_string('openwindow', 'mod_gerautog');
		echo html_writer::tag('p', $str, array('style' => 'text-align:center'));
    $linkname = get_string('getbook', 'mod_gerautog');
    $link = new moodle_url('/mod/gerautog/view.php',array('id' => $id, 'action' => 'get'));
    $button = new single_button($link, $linkname);
    $button->add_action(new popup_action('click', $link, 'view' . $id,array('height' => 600, 'width' => 800)));

    echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:center'));
	}
  echo $OUTPUT->footer();
}
else {
	/************* OPEN PDF ****************/
	global $USER;
	$usercontext = context_user::instance($USER->id);
	$fs = get_file_storage();
	$fileinfo = array('contextid' => $usercontext->id,
	                    'component' => 'mod_gerautog',
	                    'filearea' => 'temp',
	                    'itemid' => 1
	            );
	$files = $fs->get_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid']);
  //var_dump($files);
	foreach ($files as $f) {
	    if (strlen($f->get_filename()) > 1) {
	        //echo $f->get_filename() . '<br />';
	        $arq = $f;
	    }
	}
	send_stored_file($arq, 10, 0, false, array('dontdie' => true));
	//send_stored_file($arq, 10, 0, true, array('filename' => $arq->get_filename(), 'dontdie' => true));
}
