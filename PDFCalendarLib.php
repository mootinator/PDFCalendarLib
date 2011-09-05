<?php
/**
 * PDFCalendarLib
 * 
 * Wrapper for FPDF to display a monthly calendar.
 *
 * @author Kevin Stricker
 */
require_once('FPDF/fpdf.php');
require_once('MoonPhase.php');
class PDFCalendarLib {
    var $pdf;
    var $daynames       = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    var $date;
    var $days_in_month;
    var $month;
    var $year;
    var $title;
    var $positions      = array();
    var $cellWidth;
    var $fontSize;
    var $fontHeight;
    var $weekStarts     = 0;
    var $titleFont      = 'Arial';
    var $headerFont     = 'Arial';
    var $numberFont     = 'Arial';
    var $eventFont      = 'Arial';
    
    function PDFCalendarLib($month, $year, $title, $orientation='L',$unit='mm',$format='LETTER')
    {
        $ts = mktime(0,0,0,$month,1,$year);
        $this->date = getDate($ts);
        $this->days_in_month = date('t',$ts);
        $this->month = $month;
        $this->year = $year;
        $this->title = $title;   
        $this->pdf = new FPDF($orientation,$unit,$format);
        $this->pdf->AliasNbPages();
	$this->pdf->AddPage();
    }
    
    function DrawGrid()
    {
        $weekday_of_first = ($this->date["wday"] + 7 - $this->weekStarts) % 7;
        $gridWidth = $this->pdf->w - $this->pdf->rMargin - $this->pdf->lMargin;
        
        $this->cellWidth = $gridWidth / 7;
        $num_of_rows = ceil(($this->days_in_month + $weekday_of_first) / 7.0);
        $wkdayfntsz = $this->pdf->hPt /40;
        $wkdayfntht = $this->pdf->h /40 * 1.1;
        $numfntsz = $this->pdf->hPt / 60;
        $numfntht = $this->pdf->h / 60 * 1.1;
        $this->fontSize = $this->pdf->hPt / 80;
        $this->fontHeight = $this->pdf->h / 80 * 1.1;
        
	$this->pdf->SetFont($this->titleFont,'B',$wkdayfntsz);
        $this->pdf->Cell(0,$wkdayfntht,$this->title . ' - '. $this->date["month"] . ' ' . $this->date["year"],0,0,'C');
        $this->pdf->SetY($wkdayfntht * 2 + $this->pdf->tMargin);
	$this->pdf->SetFillColor(0,0,0);
	$this->pdf->SetTextColor(255,255,255);
        
        
        /* Render the weekday header */
        $this->pdf->SetFont($this->headerFont,'B',$wkdayfntsz);
	for($i = 0; $i < 7; $i++){
	    $this->pdf->Cell($this->cellWidth,$wkdayfntht,$this->daynames[($i + $this->weekStarts) % 7],1,0,'C',1);
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
                    $this->pdf->SetFont($this->numberFont,'B',$numfntsz);
                    $this->pdf->Cell($this->cellWidth,$numfntht,$day,0,0,'R');
                    $this->pdf->Ln();
                    $this->positions[] = array("X" => $x_offset, "Y" => $this->pdf->GetY());
                    $day++;
                }
            }
        }
    }
    
    function AddToDay($day, $message, $htmlcolor = '#000')
    {
        list($r, $g, $b) = $this->html2rgb($htmlcolor);
        $this->pdf->SetTextColor($r, $g, $b);
        $this->pdf->SetY($this->positions[$day - 1]["Y"]);
        $this->pdf->SetX($this->positions[$day - 1]["X"]);
        $this->pdf->SetFont($this->eventFont, '', $this->fontSize);
        $this->pdf->MultiCell($this->cellWidth,$this->fontHeight,$message,0,'L');
        $this->positions[$day - 1]["Y"] = $this->pdf->GetY();
    }
    
    function AddMoonPhases()
    {
        $moon = new MoonPhase();
        $phases = $moon->phase_changes($this->year, $this->month);
        foreach($phases as $phase)
        {
            $this->AddToDay($phase['day'], $phase['phase']);
        }
    }
    
    function Output($name, $dest)
    {
        $this->pdf->Output($name, $dest);
    }
    
    function html2rgb($color) {
        if ($color[0] == '#')
            $color = substr($color, 1);

        if (strlen($color) == 6)
            list($r, $g, $b) = array($color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]);
        elseif (strlen($color) == 3)
            list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
        else
            return false;

        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);

        return array($r, $g, $b);
    }
}

?>
