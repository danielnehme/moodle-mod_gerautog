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

$PAGE->set_url($url);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_cm($cm);

if ($action) {
    $url->param('action', $action);
}

if (!$url->get_param('action')) {
	echo $OUTPUT->header();

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
    $pdf = new TcpdfFpdi();
    $cxt = context_module::instance($id);
    $fs = get_file_storage();
		$draftitemid = file_get_submitted_draft_itemid('arqs');
		file_prepare_draft_area($draftitemid, $cxt->id, 'mod_gerautog', 'arqs', 1, array('subdirs' => false,  'maxfiles' => 1,'accepted_types' => array('.pdf')));
		$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
    foreach ($files as $f) {
        if (strlen($f->get_filename()) > 1) {
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
          foreach ($files as $f) {
              if (strlen($f->get_filename()) > 1) {
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

			// need send email
			if (!empty($data->emailto))
			{
				$page = optional_param('page', 0, PARAM_INT);
				$perpage = optional_param('perpage', 20, PARAM_INT);
				$sort = optional_param('sort', '', PARAM_ACTION);
				$direction = optional_param('dir', 'ASC', PARAM_ACTION);

				// Get Oour users
				$fields = array(
						'courserole' => 1,
						'systemrole' => 0,
						'realname' => 1,
						'username' => 1,
				);
				$ufiltering = new user_filtering($fields);
				list($sql, $params) = $ufiltering->get_sql_filter();
				$usercount = get_users(false);
				$usersearchcount = get_users(false, '', true, null, '', '', '', '', '',
												'*', $sql, $params);

				if(empty($sort)) $sort = 'lastname';

				$users = get_users_listing($sort, $direction, $page*$perpage,
								$perpage, '', '', '', $sql, $params);
				$columns = array('firstname', 'lastname', 'email', 'city', 'lastaccess');
				foreach($columns as $column) {
						$direction = ($sort == $column and $direction == "ASC") ? "DESC" : "ASC";
						$$column = html_writer::link('view.php?sort='.$column.'&dir='.
								$direction, get_string($column));
				}

				$found = false;
				// email confirmation
				foreach($users as $user) {
					if ($data->emailto == $user->email)
					{
						$found = true;
						$str = get_string('emailto', 'mod_gerautog');
						echo $str . ": " . $data->emailto . '<br />';

						$subject = get_string('emailsubject', 'gerautog');
						$message = get_string('emailtext', 'gerautog') . "\n";
						$messagehtml = text_to_html($message);

						$hash = $file->get_contenthash();

						$dirn1 = $hash[0] . $hash[1];
						$dirn2 = $hash[2] . $hash[3];

						$relativefilepath = "filedir" . DIRECTORY_SEPARATOR . $dirn1 . DIRECTORY_SEPARATOR . $dirn2. DIRECTORY_SEPARATOR . $hash;

						$ret = email_to_user($user, $USER->email, $subject, $message, $messagehtml, $relativefilepath , $file->get_filename());
						if($ret){
							echo $OUTPUT->box(get_string('emailsent', 'gerautog') . '<br>',
															'generalbox', 'notice');
						}
						else {
							echo $OUTPUT->box(get_string('emailnotsent', 'gerautog') . '<br>',
															'generalbox', 'notice');
						}
					}
				}
				if(!$found){
					$str = get_string('emailnotfound', 'mod_gerautog');
					echo $str . '<br />';
				}
			}
    } else {
    print_error(get_string('filenotfound', 'mod_gerautog'));
    }

		// message on the new page
		$str = get_string('openwindow', 'mod_gerautog');
		echo html_writer::tag('p', $str, array('style' => 'text-align:center'));

    $linkname = get_string('getbook', 'mod_gerautog');
    $link = new moodle_url('/mod/gerautog/view.php',array('id' => $id, 'action' => 'get'));
    $button2 = new single_button($link, $linkname);
    $button2->add_action(new popup_action('click', $link, 'view' . $id,array('height' => 600, 'width' => 800)));

    echo html_writer::tag('div', $OUTPUT->render($button2), array('style' => 'text-align:center'));
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
  foreach ($files as $f) {
    if (strlen($f->get_filename()) > 1) {
        $arq = $f;
    }
  }
  send_stored_file($arq, 10, 0, false, array('dontdie' => true));
}
