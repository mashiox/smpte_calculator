<!--
    This is a working example of the SMPTE 29.97 dropframe timecode calcuator included in this repo.
    Copyright (c) 2014 Matt Walther
    Licensed under GPL v2. 
-->
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>29.97 SMPTE Dropframe Calculator</title>
        <style>
            html{
                font-family: sans-serif;
            }
            table{
                border-spacing: 0px;
                border-collapse: collapse;
                width: 50%;
                margin: 0 auto;
            }
            tr{}
            td,th{
                padding: 10px 20px;
                border-bottom: 1px solid black;
            }
            div#wrapper{
                text-align: center;
                width: 50%;
                margin: 0 auto;
            }
            div#container{
                margin: 0 auto;
                width: 50%;
                text-align: right;
            }
            div#left{
                white-space: nowrap;
                float: left;
            }
            div#right{
                white-space: nowrap;
            }
            table tr:nth-of-type(even){
                background-color: #F1F2F1;
            }
            table tr.lightblue{
                background-color: lightblue;
            }
            table tr#timecode:hover {
                background-color: lightblue;
            }
        </style>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script>
            $(document).ready(function(){
                $("table tr#timecode").click(function(){
                    $(this).toggleClass('lightblue');
                });
            });
        </script>
    </head>
    <body>
        <div id="wrapper">
            <h2>29.97 Calculator</h2>
            <p><a href="index.php">Home</a> &lambda; <a href="http://mashio.net">Mashio.NET</a> &lambda; <a href="https://github.com/mashiox/smpte_calculator">GitHub</a> </p>
        <?php
        /**
         * Division Algorithm
         * a = dq + r
         * q = a div d = floor(a/d)
         * r = a mod d
         * The dropcode alorithm drops the first (zeroth) and second (first) frame from the time sequence.
         * We must manipulate the division algorithm to `redefine` the quotient of some frame number when divided by 17982 (significance of 17982 unknown)
         * We do this by keeping the remainder of a mod d the same and forcing the quotient to be zero when i should be 0 or 1, and the dividend to be by the remainder.
         * In this way we preserve the correctness of the division algorithm, while achieving the desired result: dropped frames.
         * 
         * Input Domain:
         * hour member of Z+
         * 0 <= minute <= 59 (Subset of Z)
         * 0 <= second <= 59 (Subset of Z)
         * 0 <= frame <= 29 (Subset of Z)
         */
        // http://andrewduncan.net/timecodes/ 
        // http://forum.doom9.org/archive/index.php/t-132432.html
        require_once 'smpte.functions.php';
        if (isset($_POST['h'],$_POST['m'],$_POST['s'],$_POST['f']) /*&& !isset($_GET['single'])*/){
            if (empty($_POST['h']))
                $_POST['h'] = 1;
            if (empty($_POST['m']))
                $_POST['m'] = 0;
            if (empty($_POST['s']))
                $_POST['s'] = 0;
            if (empty($_POST['f']))
                $_POST['f'] = 0;
            $inputValid = true;
            foreach ($_POST as $key => $value) {
                if (!ctype_digit(strval($_POST[$key]))){ // True if input is not an integer. Thank you Simon Neaves!
                    $inputValid = FALSE;
                }
                else {  // The input is an integer, is it in the domain?
                    switch ($key){
                        case 'h':
                            if ($value < 1){
                                $inputValid = FALSE;
                            }
                            break;
                        case 'm':
                        case 's':
                            if ( $value < 0 || $value > 59 ){
                                $inputValid = FALSE;
                            }
                            break;
                        case 'f':
                            if ( $value < 0 || $value > 29 ){
                                $inputValid = FALSE;
                            }
                            break;
                    }
                }
            }
            if ($inputValid){
                $h = $_POST['h']; $m = $_POST['m']; $s = $_POST['s']; $f = $_POST['f'];
                
                echo 
                '
                    <table style="boarder:1px solid black;">
                        <tr>
                            <th>Frame number</th>
                            <th>NDF</th>
                            <th>DF</th>
                            <th>Frame number</th>
                        </tr>
                ';
                if (!isset($_GET['single'])){ // Many timecodes
                    for ( $i = 1 ; $i <= $_POST['h'] ; $i++ ){
                        if ($_POST['h'] - $i === 0){
                            $minuteEnd = $_POST['m'];
                        }
                        else {
                            $minuteEnd = 59;
                        }
                        for ( $j = 0 ; $j <= $minuteEnd ; $j++ ){
                            if ( $_POST['h']-$i === 0 && $_POST['m']-$j === 0){
                                $secondEnd = $_POST['s'];
                            }
                            else {
                                $secondEnd = 59;
                            }
                            for ($k = 0 ; $k <= $secondEnd ; $k++){
                                if ($_POST['h']-$i === 0 && $_POST['m']-$j === 0 && $_POST['s']-$k === 0){
                                    $frameEnd = $_POST['f'];
                                }
                                else {
                                    $frameEnd = 29;
                                }
                                for ($l = 0 ; $l <= $frameEnd ; $l++){
                                    /*$totalMinutes = 60*$i + $j;
                                    $frameNumber = 108000*$i + 1800*$j + 30*$k + $l - 2*($totalMinutes - (floor($totalMinutes/10)) );
                                    $D = floor($frameNumber/17982);
                                    $M = $frameNumber % 17982;
                                    if ($M === 0 || $M === 1){  // This works via the division algorithm. Proof above. 
                                        $MD = 0;
                                    }
                                    else {
                                        $MD = floor(($M-2)/1798);
                                    }
                                    $frameNumber += 18*$D + 2*$MD;
                                    $frameQuot = floor($frameNumber/30);
                                    $frames = $frameNumber % 30;
                                    $seconds = $frameQuot % 60;
                                    $minutes = floor($frameQuot/60) % 60;
                                    $hours = floor(floor($frameQuot/60)/60) % 24;*/
                                    $totalMinutes = totalMinutes($i, $j);
                                    $frameNumber = frameNumber($i, $j, $k, $l, $totalMinutes) - 107892;
                                    $timecode = smpte_dropframe_timecode($i, $j, $k, $l);
                                    echo ' 
                                        <tr id="timecode">
                                            <td>'.$frameNumber.'</td>
                                            <td>'.sprintf("%02d", $i).':'.sprintf("%02d", $j).':'.sprintf("%02d", $k).','.sprintf("%02d", $l).'</td>
                                            <td>'.sprintf("%02d", $timecode['hour']).':'.sprintf("%02d", $timecode['minute']).':'.sprintf("%02d", $timecode['second']).';'.sprintf("%02d", $timecode['frame']).'</td>
                                            <td>'.($timecode['framenumber']-108000).'</td>
                                        </tr>
                                    ';
                                }
                            }
                        }
                    } // end for
                }
                else { // Single timecode.
                    $totalMinutes = totalMinutes($h, $m);
                    $frameNumber = frameNumber($h, $m, $s, $f, $totalMinutes) - 107892;
                    $timecode = smpte_dropframe_timecode($h, $m, $s, $f);
                    echo '
                        <tr id="timecode">
                            <td>'.$frameNumber.'</td>
                            <td>'.sprintf("%02d", $h).':'.sprintf("%02d", $m).':'.sprintf("%02d", $s).':'.sprintf("%02d", $f).'</td>
                            <td>'.sprintf("%02d", $timecode['hour']).':'.sprintf("%02d", $timecode['minute']).':'.sprintf("%02d", $timecode['second']).':'.sprintf("%02d", $timecode['frame']).'</td>
                            <td>'.($timecode['framenumber']-108000).'</td>
                        </tr>
                    ';
                }
                echo
                '
                    </table>
                    </div>
                ';
                
            } // end valid input == true
            else {
                echo '<p>Invalid input entered! <a href="index.php">Go back!</a></p>';
            }
            
        }
        else {
            echo '
                <p>Running time starts at 01:00:00:00</p>
                <p>Enter the duration time:</p>
                <div id="container">
                    <div id="left">
                        <form method="post" action="index.php">
                            H: <input name="h" type="text" placeholder="01"><br>
                            M: <input name="m" type="text" placeholder="00"><br>
                            S: <input name="s" type="text" placeholder="00"><br>
                            F: <input name="f" type="text" placeholder="00"><br>
                               <input type="submit" value="Give me ALL the timecodes" />
                        </form>
                    </div>
                    
                    <div id="right">
                        <form method="post" action="index.php?single">
                            <input name="h" type="text" placeholder="01"><br>
                            <input name="m" type="text" placeholder="00"><br>
                            <input name="s" type="text" placeholder="00"><br>
                            <input name="f" type="text" placeholder="00"><br>
                            <input type="submit" value="Give me a SINGLE timecode" />
                        </form>
                    </div>
                </div>
            ' ;
        }
        ?>
        
                    </div>
    </body>
</html>
