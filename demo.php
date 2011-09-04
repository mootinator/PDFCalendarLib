<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("PdfCalendarLib.php");
$cal = new PDFCalendarLib(9, 2011, "My Calendar", 'L', 'mm', 'LETTER');
$cal->AddToDay(22, "Appointment");
$cal->AddToDay(22, "Another Appointment Which is long and requires wrapping.");
$cal->AddToDay(23, "Out of Order");
$cal->AddToDay(22, ""); // spacer
$cal->AddToDay(22, "Number three.");
$cal->AddMoonPhases();
$cal->Output("demo.pdf", "F");
?>
