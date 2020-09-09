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


/*
$event = \mod_gerautog\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('gerautog', $moduleinstance);
$event->trigger();
*/

$PAGE->set_url('/mod/gerautog/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();


//var_dump($cm);
//var_dump($course);
//var_dump($moduleinstance);
//var_dump($modulecontext);

$data = new stdClass();
$data->course = $cm->course;
$data->id = $cm->id;
//echo $data->id;

$mform = new gerautog_email_form();

//var_dump($moduleinstance);
//var_dump($modulecontext);

if (!$mform->get_data()) {
    $mform->set_data($data);
    //$mform->set_data(array('id' => $id));
    $mform->display();
}
else {
    $data = $mform->get_data();
//$name = $mform->get_new_filename('autog');
//var_dump($mform);
//var_dump($data);
//echo $data->message;

//$uploaded_file_path = $_FILES['user_file']['tmp_name'];
//$filename = $_FILES['autog']['name'];
//var_dump($_FILES);

$arq = null;

//$pdf = new Fpdi();
$pdf = new TcpdfFpdi();
$pdf->AddPage();

/*
global $USER;
$draftitemid = file_get_submitted_draft_itemid('autog');
$usercontext = context_user::instance($USER->id);
$fs = get_file_storage();
$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
//var_dump($files);
foreach ($files as $f) {
    echo $f->get_filename() . '<br />';
    if (strlen($f->get_filename()) > 1) $arq = $f->get_filename();
}
*/

//$url = moodle_url::make_pluginfile_url($arq->get_contextid(), $arq->get_component(), $arq->get_filearea(), $arq->get_itemid(), $arq->get_filepath(), $arq->get_filename(), false);

$cxt = context_module::instance($id);
$fs = get_file_storage();
$files = $fs->get_area_files($cxt->id, 'mod_gerautog', 'arqs', $id);
//var_dump($files);
foreach ($files as $f) {
    // $f is an instance of stored_file
    //echo $f->get_filename() . '<br />';
    if (strlen($f->get_filename()) > 1) {
        echo $f->get_filename() . '<br />';
        $arq = $f;
    }
}

// Get first page image file.
//if (!empty($moduleinstance->arqs)) {
    // Prepare file record object.
/*
    $fileinfo = array('contextid' => $cxt->id,
                      'component' => 'mod_gerautog',
                      'filearea' => 'arqs',
                      'itemid' => 1,
                      'filepath' => '/temp/mod_gerautog/');
*/
    //$files = $fs->get_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], '', false);

    //$files = $fs->get_area_files($modulecontext->id, 'user', 'draft', 'arqs', 'id');

    //$files = $fs->get_area_files($modulecontext->id, 'mod_gerautog', 'arqs', $id);
    //var_dump($files);
    //$file = array_shift($files);
    //var_dump($file);
    //$arq = $file->get_filename();
    //var_dump($arq);
    $book = $fs->get_file($arq->get_contextid(), $arq->get_component(), $arq->get_filearea(), $arq->get_itemid(), $arq->get_filepath(), $arq->get_filename());

/*        $fileinfo['contextid'], $fileinfo['component'],
                    $fileinfo['filearea'],
                    $fileinfo['itemid'], $fileinfo['filepath'],
                    $arq);
*/
    //var_dump($book);
    // Read contents.
    if ($book) {
        $tmpfilename = $book->copy_content_to_temp('mod_gerautog', 'book_');
        $pdf->setSourceFile($tmpfilename);
        @unlink($tmpfilename);
        $tplId = $pdf->importPage(1);
        $pdf->useTemplate($tplId);


    $pdf->SetFont('freesans', 'B', '24');
    // decode para traduzir acentos
    if($data->message) $texto=utf8_decode($data->message);
    else $texto=utf8_decode('É um teste Nasnuv!');
    $pdf->SetXY(70,0);
    $pdf->Cell(10,100,$texto);

    //$cxt = context_module::instance($id);
    //$fs = get_file_storage();
    //$files = $fs->get_area_files($cxt->id, 'mod_gerautog', 'autog', $id);
    //var_dump($files);ile migrate - update flag
    global $USER;
    $draftitemid = file_get_submitted_draft_itemid('autog');
    file_prepare_draft_area($draftitemid, $modulecontext->id, 'mod_gerautog', 'autog', null, array('subdirs' => false, 'maxbytes' => $COURSE->maxbytes, 'maxfiles' => 1,'accepted_types' => array('image')));
    $usercontext = context_user::instance($USER->id);
    $fs = get_file_storage();
    $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
    foreach ($files as $f) {
        //echo $f->get_filename() . '<br />';
        if (strlen($f->get_filename()) > 1) {
            echo $f->get_filename() . '<br />';
            $arq_img = $f;
        }
    }
    /*
    $fileinfo = array('contextid' => $modulecontext->id,
                      'component' => 'mod_gerautog',
                      'filearea' => 'autog',
                      'itemid' => 1,
                      'filepath' => '/temp/mod_gerautog/');
    */
    $imgarq = $fs->get_file($arq_img->get_contextid(), $arq_img->get_component(), $arq_img->get_filearea(), $arq_img->get_itemid(), $arq_img->get_filepath(), $arq_img->get_filename());
    //var_dump($imgarq);

    if ($imgarq) {
        $ext = pathinfo($arq_img->get_filename(), PATHINFO_EXTENSION);
        $tmpfilename = $imgarq->copy_content_to_temp('mod_gerautog', 'img_');
        try{
            $pdf->Image($tmpfilename,90,6,0,0,$ext);
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
    //$img = moodle_url::make_pluginfile_url($arq_img->get_contextid(), $arq_img->get_component(), $arq_img->get_filearea(), $arq_img->get_itemid(), $arq_img->get_filepath(), $arq_img->get_filename(), false);
    //echo $img;
    //echo html_writer::tag('img', '', array('src' => $img));
    //if($data->autog) $pdf->Image('nasnuvem.png',90,6);

    $pdf->SetFont('freesans','B',16);
    $pdf->SetXY(80,200);
    $pdf->Write(10,'www.nasnuv.com.br','https://www.nasnuv.com.br');
    $arqn = 'aut_' . $arq->get_filename();
    //$loc = $CFG->dataroot.'/temp/mod_gerautog/'.$arqn;
    //$pdf->Output('I',$loc);
    //$pdf->Output($loc, 'F');
    $fileinfo = array('contextid' => $usercontext->id,
                        'component' => 'mod_gerautog',
                        'filearea' => 'temp',
                        'itemid' => 9,
                        'filepath' => '/',
                        'mimetype' => 'application/pdf',
                        'userid' => $USER->id,
                        'filename' => $arqn
                );
    $file = $fs->create_file_from_string($fileinfo, $pdf->Output('', 'S'));
    /*
    if ($file) {
        $tmpfilename = $file->copy_content_to_temp('mod_gerautog', 'aut_');
        echo $tmpfilename;
        @unlink($tmpfilename);
    }
    */
    $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
    echo $url;
    echo html_writer::tag('a', $url, array('href' => $url));
    //$dest = array('contextid'=>$modulecontext->id, 'component'=>'mod_gerautog', 'filearea'=>'temp', 'itemid'=>1, 'filepath'=>'/', 'filename'=>$arqn, 'timecreated'=>time(), 'timemodified'=>time());
    //var_dump($dest);
    //$fs->create_file_from_pathname($dest, $loc);

    //$url = moodle_url::make_file_url('/pluginfile.php', array($file->get_contextid(), 'mod_gerautog', 'temp', $file->get_itemid(), $file->get_filepath(), $arqn));
    //echo $url;
    //echo html_writer::link($url, $dest);


//}

/*
$attempts = $this->get_attempts();
if ($attempts) {
    echo $this->print_attempts($attempts);
}
*/


$str = get_string('openwindow', 'mod_gerautog');
echo html_writer::tag('p', $str, array('style' => 'text-align:center'));

} else {
    print_error(get_string('filenotfound', 'mod_gerautog'));
}

}
/*

$url = moodle_url::make_file_url('/pluginfile.php', array($file->get_contextid(), 'mod_assignment', 'submission',
            $file->get_itemid(), $file->get_filepath(), $filename));


$linkname = get_string('getcertificate', 'mod_gerautog');

$link = new moodle_url('/mod/gerautog/view.php',array('id' => $id, 'action' => 'get'));
$button = new single_button($link, $linkname);
$button->add_action(new popup_action('click', $link, 'view' . $id,array('height' => 600, 'width' => 800)));

echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:center'));
*/


//$pdf->Image($tmpfilename, 0, 0, $this->get_instance()->width, $this->get_instance()->height);
// Writing text.
//$pdf->SetXY($this->get_instance()->certificatetextx, $this->get_instance()->certificatetexty);
//$pdf->writeHTMLCell(0, 0, '', '', $this->get_certificate_text($issuecert, $this->get_instance()->certificatetext), 0, 0, 0, true, 'C');



// ver createpdf do simplecertificate
/*
	$filename='mypdffile.pdf';
    $loc=$CFG->dataroot.'/'.$filename;
  	$homepage = $newtext;
  	$pdf = new TCPDF();     // ‘L’ for Landscape mode, P for Portrait mode
	$pdf->AddPage();
  	$pdf->SetFont("freeserif", '', 11); // Font settings
  	$pdf->writeHTML($homepage, true, false, true, false, '');
  	$pdf->Output($loc, "F");
*/


echo $OUTPUT->footer();
