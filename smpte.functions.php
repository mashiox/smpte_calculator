<?php
/**
 * Written by Matt Walther
 * Licensed under GPL v2. 
 */

/**
 * Calcaulates a SMPTE Drop-frame Timecode given some hour, minute, second, and frame. This algorithm derived from http://andrewduncan.net/timecodes/
 * @param integer $hour Default 1. Hour runtime. Domain: h >= 1.
 * @param integer $minute Default 0. Minute runtime. Domain 0 <= m <= 59.
 * @param integer $second Default 0. Second runtime. Domain 0 <= s <= 29.
 * @param integer $frame Default 0. Frame runtime. Domain 0 <= f <= 29.
 * @return array Returns an array of drop-frame timecodes. Keys include: 'hour', 'minute', 'second', 'second', 'frame', 'framenumber'.
 */
function smpte_dropframe_timecode($hour = 1, $minute  = 0 , $second = 0, $frame = 0 ){
    $totalMinutes = totalMinutes($hour, $minute);
    $frameNumber = frameNumber($hour, $minute, $second, $frame, $totalMinutes);
    $D = floor($frameNumber/17982);
    $M = $frameNumber % 17982;
    
    if ($M === 1 || $M === 0){
        $MD = 0;    // NOTE: This poor variable naming does NOT indicate M times D!
    }
    else {
        $MD = floor( ($M-2) / 1798 );
    }
    
    $frameNumber += 18*$D + 2*$MD;
    $frameQuotient = floor($frameNumber/30);
    $frames = $frameNumber % 30;
    $seconds = $frameQuotient % 60; // NOTE: Keep in mind, these are totally new variables named in the plural. 'minutes' =/= 'minute'
    $minutes = floor($frameQuotient/60) % 60;
    $hours = floor( floor( $frameQuotient/60 ) / 60 ) % 24;
    return array(
        'hour'         => $hours,
        'minute'       => $minutes,
        'second'       => $seconds,
        'frame'        => $frames,
        'framenumber'  => $frameNumber
    );
}

/**
 * Returns non-drop-frame frame number. 
 * @param integer $hour Hour runtime
 * @param integer $minute Minute runtime
 * @param integer $second Second runtime
 * @param integer $frame Frame runtime
 * @param mixed $totalMinutes Default boolean false. If already calculated, input an integer as the sum of the total minutes from the input set. hours*60 + minutes.
 * @return integer The non-drop-frame frame number.
 */
function frameNumber($hour, $minute, $second, $frame, $totalMinutes = false){
    if (!$totalMinutes){
        $totalMinutes = totalMinutes($hour, $minute);
    }
    return 108000*$hour + 1800*$minute + 30*$second + $frame - 2*($totalMinutes - (floor($totalMinutes/10)) );
}

/**
 * The total minutes. Let's get mathematical:  totalMinutes: Z^2 -> Z. This is one-to-one, and I think onto too. Proof left as exercise to reader.
 * @param integer $hour An hour number
 * @param integer $minute A minute number
 * @return integer The total number of minutes. 
 */
function totalMinutes($hour, $minute){
    return 60*$hour + $minute;
}

/**
 * Counts the time interval from frame Zero to inputted drop-frame frame Number
 * @param integer $frameNumber 29.97 DF frame number
 * @return integer Time in seconds
 */
function timeInSeconds($frameNumber){
    if ($frameNumber === 0){
        $frameNumber++;
    }
    return ($frameNumber/29.97);
}

/**
 * Calculates the 29.97 DF frame number from the time in seconds. 
 * @param integer $seconds
 * @return float DF frame number.
 */
function frameNumber_fromSeconds($seconds){
    return $seconds * 29.97;
}
?>
