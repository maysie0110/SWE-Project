<?php
function convertor($linereverse, $score1, $line)
{
    $slope = 1 / $linereverse['slope'];
    $intercept = $linereverse['intercept'] / $linereverse['slope'];
    $linereverse['slope'] = $slope;
    $linereverse['intercept'] = -$intercept;


    $answer = $line['slope'] * doubleval($score1) + $line['intercept'];;
    $answerreverse = $linereverse['slope'] * doubleval($score1) + $linereverse['intercept'];;

    if ($answer > $answerreverse) {
        $lowanswer = floor($answerreverse);
        $highanswer = ceil($answer);
    } else {
        $lowanswer = floor($answer);
        $highanswer = ceil($answerreverse);
    }

    return array(
        'lowanswer' => $lowanswer,
        'highanswer' => $highanswer,
    );
}

//uses equation below
//(NΣXY - (ΣX)(ΣY)) / (NΣX2 - (ΣX)2)
function linear_regression($x, $y)
{

    $n = count($x); // number of items in the array
    $x_sum = array_sum($x); // sum of all X values
    $y_sum = array_sum($y); // sum of all Y values

    $xx_sum = 0;
    $xy_sum = 0;

    for ($i = 0; $i < $n; $i++) {
        $xy_sum += ($x[$i] * $y[$i]);
        $xx_sum += ($x[$i] * $x[$i]);
    }

    // Slope
    $slope = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));

    // calculate intercept
    // $intercept = ($y_sum - ($slope * $x_sum)) / $n;
    $intercept =(($y_sum * $xx_sum)-($x_sum * $xy_sum))/($n*$xx_sum-($x_sum*$x_sum));
    return array(
        'slope' => $slope,
        'intercept' => $intercept,
    );
}

//calculate the linear correlation coefficient
// -1 is negative correlation, 0 is no correlation, 1 is positive correlation
function correlation($x, $y)
{

    $n = count($x); // number of items in the array
    $x_sum = array_sum($x); // sum of all X values
    $y_sum = array_sum($y); // sum of all Y values

    $xx_sum = 0;
    $xy_sum = 0;
    $yy_sum = 0;
    for ($i = 0; $i < $n; $i++) {
        $xy_sum += ($x[$i] * $y[$i]);
        $xx_sum += ($x[$i] * $x[$i]);
        $yy_sum += ($y[$i] * $y[$i]);
    }
    $numerator = $n * $xy_sum - $x_sum * $y_sum;
    $denomenator = sqrt($n * $xx_sum - ($x_sum * $x_sum)) * sqrt($n * $yy_sum - ($y_sum * $y_sum));
    // print($numerator."<br>");
    // print($denomenator);
    // die();
    return round($numerator / $denomenator, 4);
}

?>
