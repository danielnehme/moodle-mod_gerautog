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

use setasign\Fpdi\Fpdi;
require_once($CFG->dirroot . '/mod/gerautog/libext/fpdf/fpdf.php');
require_once($CFG->dirroot . '/mod/gerautog/libext/fpdi/autoload.php');

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

$arq = null;

$pdf = new Fpdi();
$pdf->AddPage();

$fs = get_file_storage();

$files = $fs->get_area_files($modulecontext->id, 'mod_gerautog', 'arqs', $id);
foreach ($files as $f) {
    // $f is an instance of stored_file
    echo $f->get_filename() . '<br />';
    if (strlen($f->get_filename()) > 1) $arq = $f->get_filename();
}


/*
$browser = get_file_browser();
$context =  context_system::instance();
//var_dump($context);
$component = 'mod_gerautog';
$filearea = null;
$itemid   = null;
$filename = null;
//$draftitemid = file_get_submitted_draft_itemid('arqs');
//var_dump($draftitemid);
$fileinfo = $browser->get_file_info($context, $component, $filearea, $itemid, '/', $filename);
var_dump($fileinfo);
if ($fileinfo) {
    // build a Breadcrumb trail
    $level = $fileinfo->get_parent();
    while ($level) {
        $path[] = array('name'=>$level->get_visible_name());
        $level = $level->get_parent();
    }
    $path = array_reverse($path);
    $children = $fileinfo->get_children();
    foreach ($children as $child) {
        if ($child->is_directory()) {
            echo $child->get_visible_name();
            // display contextid, itemid, component, filepath and filename
            var_dump($child->get_params());
        }
    }
}
*/


// Get first page image file.
//if (!empty($moduleinstance->arqs)) {
    // Prepare file record object.

    $fileinfo = array('contextid' => $modulecontext->id,
                      'component' => 'mod_gerautog',
                      'filearea' => 'arqs',
                      'itemid' => 1,
                      'filepath' => '/');

    //$files = $fs->get_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], '', false);

    //$files = $fs->get_area_files($modulecontext->id, 'user', 'draft', 'arqs', 'id');

    //$files = $fs->get_area_files($modulecontext->id, 'mod_gerautog', 'arqs', $id);
    //var_dump($files);
    //$file = array_shift($files);
    //var_dump($file);
    //$arq = $file->get_filename();
    //var_dump($arq);
    $book = $fs->get_file($fileinfo['contextid'], $fileinfo['component'],
                    $fileinfo['filearea'],
                    $fileinfo['itemid'], $fileinfo['filepath'],
                    $arq);
    //var_dump($book);
    // Read contents.
    if ($book) {
        $tmpfilename = $book->copy_content_to_temp('mod_gerautog', 'book_');
        $pdf->setSourceFile($tmpfilename);
        @unlink($tmpfilename);
        $tplId = $pdf->importPage(1);
        $pdf->useTemplate($tplId);
    } else {
        print_error(get_string('filenotfound', 'mod_gerautog', $this->get_instance()->arqs));
    }



    $pdf->SetFont('Arial', 'B', '24');
    // decode para traduzir acentos
    $texto=utf8_decode('É um teste Nasnuv!');
    $pdf->SetXY(70,0);
    $pdf->Cell(10,100,$texto);
    $pdf->SetFont('Arial','B',16);
    $pdf->SetXY(80,200);
    $pdf->Write(10,'www.nasnuv.com.br','https://www.nasnuv.com.br');
    $arqn = 'aut_' . $arq;
    $loc = $CFG->dataroot.'/'.$arqn;
    $pdf->Output('F',$loc);

    $dest = array('contextid'=>$modulecontext->id, 'component'=>'mod_gerautog', 'filearea'=>'temp',         'itemid'=>0, 'filepath'=>'/', 'filename'=>$arqn, 'timecreated'=>time(), 'timemodified'=>time());
    //var_dump($dest);
    //$fs->create_file_from_pathname($dest, $loc);

    //$url = moodle_url::make_file_url('/pluginfile.php', array($modulecontext->id, 'mod_gerautog', 'temp', 0, '/', $dest));
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
/*
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
