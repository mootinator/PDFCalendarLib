<?php
/**
 * -MoonPhase - Calculte the percent moon phase.
 * Calculate the phases commonly found on a monthly calendar.
 * 
 * Reasonably accurate at 1 hour resolution.
 *
 * @author Kevin Stricker
 * 
 * Ported from http://www.voidware.com/phase.c
 * 
 */
define("PI", 3.1415926535897932384626433832795);
define("RAD",     (PI/180.0));
define("SMALL_FLOAT", 1e-12);
define("MINUTE", 0.016666666666667);
    
function trunc($number)
{
    if ($number >= 0)
    {
        return floor($number);
    }
    else
    {
        return ceil($number);
    }
}
    
    
class MoonPhase {
    function julian($year, $month, $day)
    {
        $a = $b = $c = $e = 0;
        if ($month < 3) {
            $year--;
            $month += 12;
        }
        if ($year > 1582 || ($year == 1582 && $month > 10) ||
            ($year == 1582 && $month == 10 && $day > 15)){
            $a = trunc($year / 100);
            $b = 2-$a+trunc($a/4);
            }
            $c = trunc(365.25*$year);
            $e = trunc(30.6001*($month+1));
            return $b+$c+$e+$day+1720994.5;
    }
    
    function sun_position($julian = 11567.666666667)
    {
        $n = $x = $e = $l = $dl = $v = 0.0;
        $m2 = 0.0;
        $i = 0;
        
        $n = 360.0/365.2422 * $julian;
        $i = trunc($n/360.0);
        $n = $n-$i*360.0;
        $x=$n-3.762863;
        if ($x<0) $x += 360;
        $x  *= RAD;
        $e = $x;
        do {
            $dl = $e-0.016718*sin($e)-$x;
            $e=$e-$dl/(1-.016718*cos($e));
        } while (abs($dl)>=SMALL_FLOAT);
        $v=360.0/PI*atan(1.01686011182*tan($e/2));
        $l=$v+282.596403;
        $i=trunc($l/360);
        $l=$l-$i*360.0;
        return $l;
    }
    
    function moon_position($julian = 11567.666666667, $sun=158.8689200455)
    {
        $ms = $l = $mm = $n = $ev = $sms = $z = $x = $lm = $bm = $ae = $ec = 0.0;
        $d = 0.0;
        $ds = $as = $dm = 0.0;
        $i = 0;
        
        $ms = 0.985647332099*$julian - 3.762863;
        if ($ms < 0) $ms += 360.0;
        $l = 13.176396*$julian + 64.975464;
        $i = trunc($l/360);
        $l = $l - $i*360.0;
        if ($l < 0) $l += 360.0;
        $mm = $l-0.1114041*$julian-349.383063;
        $i = trunc($mm/360);
        $mm -= $i*360.0;
        $n = 151.950429 - 0.0529539*$julian;
        $i = trunc($n/360);
        $n -= $i*360.0;
        $ev = 1.2739*sin((2*($l-$sun)-$mm)*RAD);
        $sms = sin($ms*RAD);
        $ae = 0.1858*$sms;
        $mm += $ev-$ae- 0.37*$sms;
        $ec = 6.2886*sin($mm*RAD);
        $l += $ev+$ec-$ae+ 0.214*sin(2*$mm*RAD);
        $l= 0.6583*sin(2*($l-$sun)*RAD)+$l;
        return $l;
    }
    
    function moon_phase($year, $month, $day, $hour)
    {
        $julian = $this->julian($year, $month, $day+$hour/24.0)-2444238.5;
        $ls = $this->sun_position($julian);
        $lm = $this->moon_position($julian, $ls);
        
        $t = $lm - $ls;
        if ($t < 0) $t += 360;
        return (1.0 - cos(($lm - $ls)*RAD))/2;
    }
    
    function phase_changes($year, $month, $hours_step = 0.25)
    {
        $ts = mktime(0,0,0,$month,1,$year);
        $days_in_month = date('t',$ts);
        
        $phase_max = 0;
        $phase_min = 1;
        
        $phase_last = 0;
        $day_last = 0;
        $hour_last = 0;
        $minute_last = 0;
        $first = true;
        
        $return = array();
        
        for($day = 0; $day <= $days_in_month + 1; $day++)
        {
            for($hour = 0; $hour < 24; $hour += $hours_step)
            {
                $phase = $this->moon_phase($year, $month, $day, $hour);
                if (!$first)
                {
                    /* Full */
                    if ($phase > $phase_last && $phase > $phase_max)
                    {
                        $phase_max = $phase;
                    }
                    else if ($phase_max > 0)
                    {
                        if  ($day_last > 0 && $day_last <= $days_in_month)
                            $return[] = array('day' => $day_last, 'hour' => $hour_last, 'minute' => $minute_last, 'phase' => 'Full Moon');
                        $phase_max = 0;
                    }
                    
                    /* Quarters */
                    if ($phase >= 0.5 && $phase_last < 0.5)
                    {
                        if  ($day_last > 0 && $day_last <= $days_in_month)
                            $return[] = array('day' => $day_last, 'hour' => $hour_last, 'minute' => $minute_last, 'phase' => 'First Quarter');
                    }
                    if ($phase <= 0.5 && $phase_last > 0.5)
                    {
                        if  ($day_last > 0 && $day_last <= $days_in_month)
                            $return[] = array('day' => $day_last, 'hour' => $hour_last, 'minute' => $minute_last, 'phase' => 'Last Quarter');
                    }
                    
                    /* New */
                    if ($phase < $phase_last && $phase < $phase_min)
                    {
                        $phase_min = $phase;
                    }
                    else if($phase_min < 1)
                    {
                        if ($day_last > 0 && $day_last <= $days_in_month)
                            $return[] = array('day' => $day_last, 'hour' => $hour_last, 'minute' => $minute_last, 'phase' => 'New Moon');
                        $phase_min = 1;
                    }
                }
                $phase_last = $phase;
                $day_last = $day;
                $hour_last = trunc($hour);
                $minute_last = round(($hour - $hour_last)/MINUTE);
                $first = false;
            } 
        }
        return $return;
    }
}

?>
