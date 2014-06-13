<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>The Hoffulator</title>
        <style>
            table{
                border-spacing: 0px;
                border-collapse: collapse;
                width: 50%;
                margin: 0 auto;
            }
            tr{
                
            }
            td,th{
                padding: 10px 20px;
                border-bottom: 1px solid black;
            }
            tr:nth-of-type(even){
                background-color: #fbfbfb;
            }
            div#wrapper{
                width: 50%;
                margin: 0 auto;
            }
        </style>
    </head>
    <body>
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
         */
        // http://andrewduncan.net/timecodes/ 
        // http://forum.doom9.org/archive/index.php/t-132432.html
        if (isset($_POST['h'],$_POST['m'],$_POST['s'],$_POST['f']) && !isset($_GET['single'])){
            echo 
            '
                <div id="wrapper">
                <table style="boarder:1px solid black;">
                    <tr>
                        <th>NDF</th>
                        <th>DF</th>
                    </tr>
            ';
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
                            $totalMinutes = 60*$i + $j;
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
                            $hours = floor(floor($frameQuot/60)/60) % 24;
                            //echo "<p>".$i.":".$j.":".$k.",".$l." -- ".$hours.":".$minutes.":".$seconds.";".$frames."</p>";
                            echo ' 
                                <tr>
                                    <td>'.sprintf("%02d", $i).':'.sprintf("%02d", $j).':'.sprintf("%02d", $k).','.sprintf("%02d", $l).'</td>
                                    <td>'.sprintf("%02d", $hours).':'.sprintf("%02d", $minutes).':'.sprintf("%02d", $seconds).';'.sprintf("%02d", $frames).'</td>
                                </tr>
                            ';
                        }
                    }
                }
            }
            echo
            '
                </table>
                </div>
            ';
        }
        else if (isset ($_GET['single'])){
            $h = $_POST['h']; $m = $_POST['m']; $s = $_POST['s']; $f = $_POST['f'];
            $totalMinutes = 60*$h + $m; 
            $frameNumber = 108000*$h + 1800*$m + 30*$s + $f - 2*($totalMinutes - (floor($totalMinutes/10)));
            $D = floor($frameNumber/17982);
            $M = $frameNumber % 17982;
            if ($M === 0 || $M === 1){
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
            $hours = floor(floor($frameQuot/60)/60) % 24;
            echo 
            '
                <div id="wrapper">
                    <p>NDF: '.sprintf("%02d", $h).':'.sprintf("%02d", $m).':'.sprintf("%02d", $s).';'.sprintf("%02d", $f).'
                    <p>DF:  '.sprintf("%02d", $hours).':'.sprintf("%02d", $minutes).':'.sprintf("%02d", $seconds).';'.sprintf("%02d", $frames).'</p>
                </div>
            ';
        }
        else {
            /**
             * TODO:
             *      Duration checking, working under assumption that h > 1
             *      Time ranges: (h,m,s,f are Z)
             *          1 < h
             *          0 <= m < 60
             *          0 <= s < 60
             *          0 <= f < 30
             */
            echo '
                <p>Running time starts at 01:00:00:00</p>
                <p>Enter the duration time:</p>
                <form method="post" action="index.php">
                    H:<input name="h" type="text" value="01"><br>
                    M:<input name="m" type="text" value="00"><br>
                    S:<input name="s" type="text" value="00"><br>
                    F:<input name="f" type="text" value="00"><br>
                    <input type="submit" value="Give me ALL the timecodes" />
                </form>
                <br><br>
                <form method="post" action="index.php?single">
                    H:<input name="h" type="text" value="01"><br>
                    M:<input name="m" type="text" value="00"><br>
                    S:<input name="s" type="text" value="00"><br>
                    F:<input name="f" type="text" value="00"><br>
                    <input type="submit" value="Give me a SINGLE timecode" />
                </form>
            ' ;
        }
        ?>
    </body>
</html>
