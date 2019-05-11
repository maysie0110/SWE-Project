<html>
    <head>
        <link rel = "stylesheet" type="text/css" href="./template.css">
    </head>
    <body>
    <div class="pback"></div>
    <header class="pHeader">
        <div class="headerContent" id="logoFrame"></div>
        <h1 class = "headerContent" id="headerText">
            Hearing Test Converter
        </h1>
    </header>

<?php
require './.config.php';
include './linear_regresion.php';

$con = mysqli_connect($HOST, $DBUSERNAME, $DBPASSWORD, $DBNAME);
print("<div class=\"mForm\">");
print("<div class=\"formContent\">");
$data1 = [];
$data2 = [];
$line=null;
$line2=null;
$linereverse=null;
$line2reverse=null;
$score1=$_POST['ScoreOne'];
$score2=$_POST['ScoreTwo'];
$lowanswer=null;
$highanswer=null;
$lowanswer2=null;
$highanswer2=null;

// Check connection
if (mysqli_connect_errno()) {
    exit( "Failed to connect to MySQL: " . mysqli_connect_error());
}

if(($_POST['Input']=="Azbio"||$_POST['Output']=="Azbio")&&($_POST['Input']=="Cnc"||$_POST['Output']=="Cnc"))
{
    
    //query
    $result = $con->query("SELECT DISTINCT azbioResults.PatientID,azbioResults.TestDate, azbioResults.ConditionsID,
    round(avg(azbioResults.Score)) as azbioScore, round(avg(cncResults.`Phonemes Correct`)) as PhonemesCorrect,
    round(avg(cncResults.`Words with 3 Phonemes Correct`)) as ThreeWordsCorrect from azbioResults
    join cncResults on azbioResults.PatientID=cncResults.PatientID and azbioResults.ConditionsID =cncResults.ConditionsID and azbioResults.TestDate = cncResults.TestDate and azbioResults.Score!=0 and `cncResults`.`Words with 3 Phonemes Correct`!=0 and `cncResults`.`Phonemes Correct`!=0
    where azbioResults.TestDate!=0000 and `cncResults`.`Words with 3 Phonemes Correct` is NOT null group by azbioResults.PatientID, azbioResults.TestDate, azbioResults.ConditionsID");
    
    //while rows, get row and add data to corresponding array
    while ($row = $result->fetch_assoc()) {
        $data1[$row['azbioScore']][] = $row['PhonemesCorrect'];
        $data2[$row['azbioScore']][] = $row['ThreeWordsCorrect'];
    }
    
    foreach ($data1 as $key => $value) {
        $avg = round(array_sum($value) / count($value));
        $data1[$key] = $avg;
    }
    foreach ($data2 as $key => $value) {
        $avg = round(array_sum($value) / count($value));
        $data2[$key] = $avg;
    }
    $x = array_keys($data1);
    $y = array_values($data1);
    $y2 = array_values($data2);
    

    $line = linear_regression($x, $y);
    $line2 = linear_regression($x, $y2);

    $linereverse = linear_regression($y, $x);
    $line2reverse = linear_regression($y2, $x);

    if($_POST['Input']=="Azbio")
    {
        $slope=1/$linereverse['slope'];
        $intercept=$linereverse['intercept']/$linereverse['slope'];
        $linereverse['slope']=$slope;
        $linereverse['intercept']=-$intercept;
        $slope=1/$line2reverse['slope'];
        $intercept=$line2reverse['intercept']/$line2reverse['slope'];
        $line2reverse['slope']=$slope;
        $line2reverse['intercept']=-$intercept;

        $answer=convert($line,$score1);
        $answerreverse=convert($linereverse,$score1);
        $answer2=convert($line2,$score1);
        $answer2reverse=convert($line2reverse,$score1);

        print("<br><hr>");
        print("<br/>Words with 3 Phonemes Correct:<br/>");
        if($answer2>$answer2reverse)
        {
            $lowanswer2=floor($answer2reverse);
            $highanswer2=ceil($answer2);
        }
        else{
            $lowanswer2=floor($answer2);
            $highanswer2=ceil($answer2reverse);
        }
        print($lowanswer2 . "-" . $highanswer2);
        print("<br><hr>");
        print("<br/>Phonemes Correct:<br/>"); 
        if($answer>$answerreverse)
        {
            $lowanswer=floor($answerreverse);
            $highanswer=ceil($answer);
        }
        else{
            $lowanswer=floor($answer);
            $highanswer=ceil($answerreverse);
        }
        print($lowanswer . "-" . $highanswer);
    }
    else if($_POST['Input']=="Cnc"){
        $slope=1/$line['slope'];
        $intercept=$line['intercept']/$line['slope'];
        $line['slope']=$slope;
        $line['intercept']=-$intercept;
        $slope=1/$line2['slope'];
        $intercept=$line2['intercept']/$line2['slope'];
        $line2['slope']=$slope;
        $line2['intercept']=-$intercept;

        $answer=convert($line,$score1);
        $answerreverse=convert($linereverse,$score1);
        $answer2=convert($line2,$score2);
        $answer2reverse=convert($line2reverse,$score2);

        print("<br><hr>");
        print("<br/>Azbio from Phonemes:<br/>");
        $lowanswer=floor(min([$answer,$answerreverse]));
        $highanswer=ceil(max([$answer,$answerreverse]));
        
        print($lowanswer . "-" . $highanswer);

        print("<br><hr>");
        print("<br/>Azbio from Words Correct:<br/>");
        $lowanswer=floor(min([$answer2,$answer2reverse]));
        $highanswer=ceil(max([$answer2,$answer2reverse]));
        
        print($lowanswer . "-" . $highanswer);

        $lowanswer=floor(min([$answer,$answerreverse,$answer2,$answer2reverse]));
        $highanswer=ceil(max([$answer,$answerreverse,$answer2,$answer2reverse]));
        
        print("<br><hr>");
        print("<br/>Azbio Range:<br/>");
        print($lowanswer . "-" . $highanswer);
    }
    
}

else if(($_POST['Input']=="Bkb"||$_POST['Output']=="Bkb")&&($_POST['Input']=="Cnc"||$_POST['Output']=="Cnc"))
{
    
    //query
    $result = $con->query("SELECT DISTINCT bkbResults.PatientID,bkbResults.TestDate, bkbResults.ConditionsID, round(avg(bkbResults.`SNR-50`),1) as bkbScore, 
    round(avg(cncResults.`Phonemes Correct`)) as PhonemesCorrect, round(avg(cncResults.`Words with 3 Phonemes Correct`)) as ThreeWordsCorrect 
    from bkbResults join cncResults on bkbResults.PatientID=cncResults.PatientID and bkbResults.ConditionsID =cncResults.ConditionsID and 
    bkbResults.TestDate = cncResults.TestDate and bkbResults.`SNR-50` > -23 and `cncResults`.`Words with 3 Phonemes Correct`!=0 and 
    `cncResults`.`Phonemes Correct`!=0 where bkbResults.TestDate!=0000 and `cncResults`.`Words with 3 Phonemes Correct` is NOT null group by 
    bkbResults.PatientID, bkbResults.TestDate, bkbResults.ConditionsID");  
    //while rows, get row and add data to corresponding array
    while ($row = $result->fetch_assoc()) {
        $data1[$row['bkbScore']][] = $row['PhonemesCorrect'];
        $data2[$row['bkbScore']][] = $row['ThreeWordsCorrect'];
    }
    
    foreach ($data1 as $key => $value) {
        $avg = round(array_sum($value) / count($value));
        $data1[$key] = $avg;
    }
    foreach ($data2 as $key => $value) {
        $avg = round(array_sum($value) / count($value));
        $data2[$key] = $avg;
    }
    $x = array_keys($data1);
    $y = array_values($data1);
    $y2 = array_values($data2);
    

    $line = linear_regression($x, $y);
    $line2 = linear_regression($x, $y2);

    $linereverse = linear_regression($y, $x);
    $line2reverse = linear_regression($y2, $x);

    if($_POST['Input']=="Bkb")
    {
        $slope=1/$linereverse['slope'];
        $intercept=$linereverse['intercept']/$linereverse['slope'];
        $linereverse['slope']=$slope;
        $linereverse['intercept']=-$intercept;
        $slope=1/$line2reverse['slope'];
        $intercept=$line2reverse['intercept']/$line2reverse['slope'];
        $line2reverse['slope']=$slope;
        $line2reverse['intercept']=-$intercept;

        $answer=convert($line,$score1);
        $answerreverse=convert($linereverse,$score1);
        $answer2=convert($line2,$score1);
        $answer2reverse=convert($line2reverse,$score1);

        print("<br><hr>");
        print("<br/>Words with 3 Phonemes Correct:<br/>");
        if($answer2>$answer2reverse)
        {
            $lowanswer2=floor($answer2reverse);
            $highanswer2=ceil($answer2);
        }
        else{
            $lowanswer2=floor($answer2);
            $highanswer2=ceil($answer2reverse);
        }
        print($lowanswer2 . "-" . $highanswer2);
        print("<br><hr>");
        print("<br/>Phonemes Correct:<br/>"); 
        if($answer>$answerreverse)
        {
            $lowanswer=floor($answerreverse);
            $highanswer=ceil($answer);
        }
        else{
            $lowanswer=floor($answer);
            $highanswer=ceil($answerreverse);
        }
        print($lowanswer . "-" . $highanswer);
    }
    else if($_POST['Input']=="Cnc"){
        $slope=1/$line['slope'];
        $intercept=$line['intercept']/$line['slope'];
        $line['slope']=$slope;
        $line['intercept']=-$intercept;
        $slope=1/$line2['slope'];
        $intercept=$line2['intercept']/$line2['slope'];
        $line2['slope']=$slope;
        $line2['intercept']=-$intercept;

        $answer=convert($line,$score1);
        $answerreverse=convert($linereverse,$score1);
        $answer2=convert($line2,$score2);
        $answer2reverse=convert($line2reverse,$score2);

        print("<br><hr>");
        print("<br/>Bkb from Phonemes:<br/>");
        $lowanswer=floor(min([$answer,$answerreverse]));
        $highanswer=ceil(max([$answer,$answerreverse]));
        
        print($lowanswer . "-" . $highanswer);

        print("<br><hr>");
        print("<br/>Bkb from Words Correct:<br/>");
        $lowanswer=floor(min([$answer2,$answer2reverse]));
        $highanswer=ceil(max([$answer2,$answer2reverse]));
        
        print($lowanswer . "-" . $highanswer);

        $lowanswer=floor(min([$answer,$answerreverse,$answer2,$answer2reverse]));
        $highanswer=ceil(max([$answer,$answerreverse,$answer2,$answer2reverse]));
        
        print("<br><hr>");
        print("<br/>Bkb Range:<br/>");
        print($lowanswer . "-" . $highanswer);
    }
    
}
else if(($_POST['Input']=="Bkb"||$_POST['Output']=="Bkb")&&($_POST['Input']=="Azbio"||$_POST['Output']=="Azbio"))
{
    //query
    $result = $con->query("SELECT DISTINCT azbioResults.PatientID,azbioResults.TestDate, azbioResults.ConditionsID, round(avg(azbioResults.Score)) as azbioScore, 
    round(avg(bkbResults.`SNR-50`),1) as SNR50 from azbioResults join bkbResults on azbioResults.PatientID=bkbResults.PatientID and azbioResults.ConditionsID =bkbResults.ConditionsID 
    and azbioResults.TestDate = bkbResults.TestDate and azbioResults.Score!=0 and bkbResults.`SNR-50`> -23 where azbioResults.TestDate!=0000 and 
    bkbResults.`SNR-50` is NOT null group by azbioResults.PatientID, azbioResults.TestDate, azbioResults.ConditionsID");
    //while rows, get row and add data to corresponding array
    while ($row = $result->fetch_assoc()) {
        $data1[$row['azbioScore']][] = $row['SNR50'];
    }
    
    foreach ($data1 as $key => $value) {
        $avg = round(array_sum($value) / count($value));
        $data1[$key] = $avg;
    }
    
    $x = array_keys($data1);
    $y = array_values($data1);
    
    $line = linear_regression($x, $y);

    $linereverse = linear_regression($y, $x);


    if($_POST['Input']=="Azbio")
    {
        
        $slope=1/$linereverse['slope'];
        $intercept=$linereverse['intercept']/$linereverse['slope'];
        $linereverse['slope']=$slope;
        $linereverse['intercept']=-$intercept;
        

        $answer=convert($line,$score1);
        $answerreverse=convert($linereverse,$score1);
        
        print("<br><hr>");
        print("<br/>Bkb Range:<br/>");
        
        if($answer>$answerreverse)
        {
            $lowanswer=floor($answerreverse);
            $highanswer=ceil($answer);
        }
        else{
            $lowanswer=floor($answer);
            $highanswer=ceil($answerreverse);
        }
        print($lowanswer . "-" . $highanswer);


        
    }
    else if($_POST['Input']=="Bkb"){
        $slope=1/$line['slope'];
        $intercept=$line['intercept']/$line['slope'];
        $line['slope']=$slope;
        $line['intercept']=-$intercept;
       

        $answer=convert($line,$score1);
        $answerreverse=convert($linereverse,$score1);
       
        print("<br><hr>");
        print("<br/>AzBio Range:<br/>");
        
        if($answer>$answerreverse)
        {
            $lowanswer=floor($answerreverse);
            $highanswer=ceil($answer);
        }
        else{
            $lowanswer=floor($answer);
            $highanswer=ceil($answerreverse);
        }
        print($lowanswer . "-" . $highanswer);
    }
    
}
print("<br><br>");
print("<button onclick=\"location.href='http://cs2.mwsu.edu/~sbeaver/SWEProject/';\">Return</button>");
print("</div>");
print("</div>");

function convert($line, $score)
{
    return $line['slope']*doubleval($score)+$line['intercept'];
}
?>

<br>
    </body>
</html>