<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("PdfCalendarLib.php");
$cal = new PDFCalendarLib(9, 2011, "My Calendar", 'L', 'mm', 'LETTER');

/* Customizations */
$cal->daynames = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');
$cal->eventFont = 'Times';
$cal->weekStarts = 1;

/* Required */
$cal->DrawGrid();

/* Add Events */
$cal->AddToDay(22, "Appointment");
$cal->AddToDay(22, "Another Appointment Which is long and requires wrapping.", '#0a0');
$cal->AddToDay(23, "Out of Order");
$cal->AddToDay(22, ""); // spacer
$cal->AddToDay(22, "Number three.");
$cal->AddMoonPhases();
$cal->Output("demo.pdf", "F");
?>
