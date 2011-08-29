<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PDFCalendarLib
 *
 * @author Kevin Stricker
 */
require_once('FPDF/fpdf.php');
class PDFCalendarLib {
    var $pdf;
    var $daynames       = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    var $date;
    var $days_in_month;
    var $positions      = array();
    
    function PDFCalendarLib($month, $year, $title)
    {
        $ts = mktime(0,0,0,$month,1,$year);
        $this->date = getDate($ts);
        $this->days_in_month = date('t',$ts);
        
        $this->pdf = new FPDF($orientation='L',$unit='mm',$format='LETTER');
        $weekday_of_first = $this->date["wday"];
        
        $this->pdf->AliasNbPages();
	$this->pdf->AddPage();
	$this->pdf->SetFont('Arial','B',15);
        $this->pdf->Cell(0,10,$title . ' - '. $this->date["month"] . ' ' . $this->date["year"] ,0,0,'C');
        $this->pdf->SetY(25);
	$this->pdf->SetFillColor(0,0,0);
	$this->pdf->SetTextColor(255,255,255);
        
        /* Render the weekday header */
	foreach($this->daynames as $pdf_day){
	    $this->pdf->Cell(37,8,$pdf_day,1,0,'C',1);
	}
	$this->pdf->Ln();
	$this->pdf->SetFillColor(255,255,255);
	$this->pdf->SetTextColor(0,0,0);
        
        /* Render the grid */
        $num_of_rows = ceil(($this->days_in_month + $weekday_of_first) / 7.0);
        for($j=0;$j<$num_of_rows;$j++){
		for($i=0;$i<7;$i++){
			$this->pdf->Cell(37,floor(162/$num_of_rows),'',1,0,'R');
		}
		$this->pdf->Ln();
	}
        
        /* Render the day numbers */
        $day = 1;
        for ( $i = 1; $i <= $num_of_rows; $i++ ){
            $y_spacing = floor(162/$num_of_rows);
            $y_offset=33 + $y_spacing * ($i-1);
            for ( $j = 0; $j < 7; $j++ ){ 
                if (($i == 1 && $weekday_of_first <= $j) || ($i > 1 && $day <= $this->days_in_month)){
                    $x_offset = 10 + 37 * $j;
                    $this->pdf->SetY($y_offset);
                    $this->pdf->SetX($x_offset);
                    $this->pdf->SetFont('Arial','B','10');
                    $this->pdf->Cell(36,4,$day,0,0,'R');
                    $this->pdf->Ln();
                    $this->positions[] = array("X" => $x_offset, "Y" => $this->pdf->GetY());
                    $day++;
                }
            }
        }
    }
    
    function AddToDay($day, $message)
    {
        $this->pdf->SetY($this->positions[$day - 1]["Y"]);
        $this->pdf->SetX($this->positions[$day - 1]["X"]);
        $this->pdf->SetFont('Arial', '', '8');
        $this->pdf->MultiCell(37,3,$message,0,'L');
        $this->positions[$day - 1]["Y"] = $this->pdf->GetY();
    }
    
    function Output($name, $dest)
    {
        $this->pdf->Output($name, $dest);
    }
}

?>
