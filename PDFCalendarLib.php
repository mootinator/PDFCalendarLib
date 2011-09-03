<?php
/**
 * PDFCalendarLib
 * 
 * Wrapper for FPDF to display a monthly calendar.
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
    var $cellWidth;
    var $fontSize;
    var $fontHeight;
    
    function PDFCalendarLib($month, $year, $title, $orientation='L',$unit='mm',$format='LETTER')
    {
        $ts = mktime(0,0,0,$month,1,$year);
        $this->date = getDate($ts);
        $this->days_in_month = date('t',$ts);
        
        $this->pdf = new FPDF($orientation,$unit,$format);
        $weekday_of_first = $this->date["wday"];
        
        $this->pdf->AliasNbPages();
	$this->pdf->AddPage();
        
        $gridWidth = $this->pdf->w - $this->pdf->rMargin - $this->pdf->lMargin;
        
        
        $this->cellWidth = $gridWidth / 7;
        $num_of_rows = ceil(($this->days_in_month + $weekday_of_first) / 7.0);
        
       
        $wkdayfntsz = $this->pdf->hPt /40;
        $wkdayfntht = $this->pdf->h /40 * 1.1;
        $numfntsz = $this->pdf->hPt / 60;
        $numfntht = $this->pdf->h / 60 * 1.1;
        $this->fontSize = $this->pdf->hPt / 80;
        $this->fontHeight = $this->pdf->h / 80 * 1.1;
        
	$this->pdf->SetFont('Arial','B',$wkdayfntsz);
        $this->pdf->Cell(0,$wkdayfntht,$title . ' - '. $this->date["month"] . ' ' . $this->date["year"],0,0,'C');
        $this->pdf->SetY($wkdayfntht * 2 + $this->pdf->tMargin);
	$this->pdf->SetFillColor(0,0,0);
	$this->pdf->SetTextColor(255,255,255);
        
        
        /* Render the weekday header */
	foreach($this->daynames as $pdf_day){
	    $this->pdf->Cell($this->cellWidth,$wkdayfntht,$pdf_day,1,0,'C',1);
	}
	$this->pdf->Ln();
	$this->pdf->SetFillColor(255,255,255);
	$this->pdf->SetTextColor(0,0,0);
        
        $gridHeight = $this->pdf->h - $this->pdf->GetY() - $this->pdf->bMargin;
        $cellHeight = ($gridHeight) / $num_of_rows;
        
        /* Render the grid */
        $gridTop = $this->pdf->GetY();
        for($j=0;$j<$num_of_rows;$j++){
		for($i=0;$i<7;$i++){
			$this->pdf->Cell($this->cellWidth,$cellHeight,'',1,0,'R');
		}
		$this->pdf->Ln();
	}
        
        /* Render the day numbers */
        $day = 1;
        for ( $i = 1; $i <= $num_of_rows; $i++ ){
            $y_offset=$gridTop + $cellHeight * ($i-1);
            for ( $j = 0; $j < 7; $j++ ){ 
                if (($i == 1 && $weekday_of_first <= $j) || ($i > 1 && $day <= $this->days_in_month)){
                    $x_offset =  $this->pdf->lMargin + $this->cellWidth * $j;
                    $this->pdf->SetY($y_offset);
                    $this->pdf->SetX($x_offset);
                    $this->pdf->SetFont('Arial','B',$numfntsz);
                    $this->pdf->Cell($this->cellWidth,$numfntht,$day,0,0,'R');
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
        $this->pdf->SetFont('Arial', '', $this->fontSize);
        $this->pdf->MultiCell($this->cellWidth,$this->fontHeight,$message,0,'L');
        $this->positions[$day - 1]["Y"] = $this->pdf->GetY();
    }
    
    function Output($name, $dest)
    {
        $this->pdf->Output($name, $dest);
    }
}

?>
