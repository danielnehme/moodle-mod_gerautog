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
 * Plugin strings are defined here.
 *
 * @package     mod_gerautog
 * @category    string
 * @copyright   2020 Nasnuv <tecnologia@nasnuv.com.br>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['missingidandcmid'] = 'No course data!';

$string['pluginname'] = 'Autograph Generator';
$string['modulename'] = 'Autograph Generator';
$string['modulenameplural'] = 'Autographs Generators';
$string['gerautogname'] = 'Name';
$string['gerautogname_help'] = 'Activity name';
$string['setting_fileupload'] = 'Select a pdf file';
$string['setting_fileupload_help'] = "You can only change the selected file until the annotator has been created by a click on 'Save'.";

$string['pluginadministration'] = 'Autograph Generator administration';

// Form Sections.
$string['issueoptions'] = 'Issue Options';
$string['emailauthors'] = 'Email Author';
$string['emailothers'] = 'Email Others';
$string['emailfrom'] = 'Email From name';
$string['delivery'] = 'Delivery';

$string['emailauthors_help'] = 'If enabled, then teachers are alerted with an email whenever students receive a certificate.';
$string['emailothers_help'] = 'Enter the email addresses here, separated by a comma, of those who should be alerted with an email whenever students receive a certificate.';
$string['emailfrom_help'] = 'Alternate email form name';
$string['delivery_help'] = 'Choose here how you would like your students to get their certificate.
<ul>
<li>Open in Browser: Opens the certificate in a new browser window.</li>
<li>Force Download: Opens the browser file download window.</li>
<li>Email Certificate: Choosing this option sends the certificate to the student as an email attachment.</li>
<li>After a user receives their certificate, if they click on the certificate link from the course homepage, they will see the date they received their certificate and will be able to review their received certificate.</li>
</ul>';

// Delivery options.
$string['openbrowser'] = 'Open in new window';
$string['download'] = 'Force download';
$string['emailbook'] = 'Email';
$string['nodelivering'] = 'No delivering, user will receive this certificate using others ways';
$string['emailoncompletion'] = 'Email on course completion';

$string['filenotfound'] = 'File not found';

$string['openwindow'] = 'Click the link below to get your book:';
$string['getbook'] = 'View book';

// Email form
$string['emailto'] = "Reader's email";
$string['emailto_help'] = "Reader's email to send book.";
$string['message_book'] = 'Message to reader';
$string['message_book_help'] = 'Message to the reader to put in the book.';
$string['autog_book'] = "Author's autograph image";
$string['autog_book_help'] = "Author's autograph to put in the book.";
$string['send'] = 'Send';

$string['pngerror'] = 'PNG interlacing not supported.';
