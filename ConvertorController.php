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
require_once './Convertor2.php';
require_once 'DbManager.php';

//connect to db
$con=new Database($HOST, $DBNAME, $DBUSERNAME, $DBPASSWORD);

// $con = mysqli_connect($HOST, $DBUSERNAME, $DBPASSWORD, $DBNAME);
print("<div class=\"mForm\">");
print("<div class=\"formContent\">");

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

//conversion between Azbio and CNC
if(($_POST['Input']=="Azbio"||$_POST['Output']=="Azbio")&&($_POST['Input']=="Cnc"||$_POST['Output']=="Cnc"))
{
    //get data from the dbmanager 
    $data=$con->AzbioCncData();
    $x = array_keys($data[0]);
    $y = array_values($data[0]);
    $y2 = array_values($data[1]);
    
    //call linear_regression function to produce formula between 2 sets of data
    $line = linear_regression($x, $y);
    $line2 = linear_regression($x, $y2);

    $linereverse = linear_regression($y, $x);
    $line2reverse = linear_regression($y2, $x);

    if($_POST['Input']=="Azbio") //convert from Azbio to CNC
    {
        print("<br><hr>");
        print("<br/>Words with 3 Phonemes Correct:<br/>");

        //pass linear regression line between 2 sets of data and the input test score to convertor for results
        $answer2 = convertor($line2reverse, $score1, $line2);
        print($answer2['lowanswer'] . "-" . $answer2['highanswer']); //print the lower bound and higher bound of the result


        print("<br><hr>");
        print("<br/>Phonemes Correct:<br/>"); 
        $answer = convertor($linereverse, $score1, $line);
        print($answer['lowanswer'] . "-" . $answer['highanswer']); //print the lower bound and higher bound of the result
      
    }
    else if($_POST['Input']=="Cnc"){ //convert from CNC to Azbio
        
           //first score in CNC - Phonemes
        print("<br><hr>");
        print("<br/>Azbio from Phonemes:<br/>");
        $answer = convertor($line, $score1, $linereverse);
        print($answer['lowanswer'] . "-" . $answer['highanswer']);

   //second score in CNC - Words
        print("<br><hr>");
        print("<br/>Azbio from Words Correct:<br/>");
        $answer2 = convertor($line2, $score2, $line2reverse);
        print($answer2['lowanswer'] . "-" . $answer2['highanswer']);

       $lowanswer=floor(min([$answer['lowanswer'],$answer2['lowanswer']]));
       $highanswer=ceil(max([$answer['highanswer'],$answer2['highanswer']]));
        
        print("<br><hr>");
        print("<br/>Azbio Range:<br/>");
        print($lowanswer . "-" . $highanswer);
    }
    
}

//conversion between BKB and CNC
else if(($_POST['Input']=="Bkb"||$_POST['Output']=="Bkb")&&($_POST['Input']=="Cnc"||$_POST['Output']=="Cnc"))
{
    $data=$con->CncBkbData(); //get data of CNC and BKB tests from the dbmanager
    $x = array_keys($data[0]);
    $y = array_values($data[0]);
    $y2 = array_values($data[1]);
    
    //use linear regression to find a formula between 2 sets of data
    $line = linear_regression($x, $y);
    $line2 = linear_regression($x, $y2);

    $linereverse = linear_regression($y, $x);
    $line2reverse = linear_regression($y2, $x);

    if($_POST['Input']=="Bkb") //convert from BKB to CNC
    {
        //first score in CNC - Words
        
        print("<br><hr>");
        print("<br/>Words with 3 Phonemes Correct:<br/>");
        $answer2 = convertor($line2reverse, $score1, $line2);
        print($answer2['lowanswer'] . "-" . $answer2['highanswer']); //print the lower bound and higher bound of the result
        
        //second score in CNC - Phonemes
        print("<br><hr>");
        print("<br/>Phonemes Correct:<br/>"); 
        $answer = convertor($linereverse, $score1, $line);
        print($answer['lowanswer'] . "-" . $answer['highanswer']);
    }
    else if($_POST['Input']=="Cnc"){ //convert from CNC to BKB
        
        //first score in CNC - Phonemes
        print("<br><hr>");
        print("<br/>Bkb from Phonemes:<br/>");
        $answer = convertor($line, $score1, $linereverse);
        print($answer['lowanswer'] . "-" . $answer['highanswer']);

        //second score in CNC - Words
        print("<br><hr>");
        print("<br/>Bkb from Words Correct:<br/>");
        $answer2 = convertor($line2, $score2, $line2reverse);
        print($answer2['lowanswer'] . "-" . $answer2['highanswer']);

       $lowanswer=floor(min([$answer['lowanswer'],$answer2['lowanswer']]));
       $highanswer=ceil(max([$answer['highanswer'],$answer2['highanswer']]));
        
       print("<br><hr>");
       print("<br/>Bkb Range:<br/>");
        print($lowanswer . "-" . $highanswer);
    }
    
}

//Conversion between BKB and AzBio
else if(($_POST['Input']=="Bkb"||$_POST['Output']=="Bkb")&&($_POST['Input']=="Azbio"||$_POST['Output']=="Azbio"))
{ 
    $data=$con->AzbioBkbData(); //get data of Azbio and BKB tests from the dbmanager
    $x = array_keys($data[0]);
    $y = array_values($data[0]);
    
      //use linear regression to find a formula between 2 sets of data
    $line = linear_regression($x, $y);
    $linereverse = linear_regression($y, $x);

    if($_POST['Input']=="Azbio") //convert from Azbio to BKB
    {   
        print("<br><hr>");
        print("<br/>Bkb Range:<br/>");

        //call convertor function
        $answer = convertor($linereverse, $score1, $line);
        print($answer['lowanswer'] . "-" . $answer['highanswer']); //print the lower bound and higher bound of the result
    }
    else if($_POST['Input']=="Bkb"){ //convert from BKB to Azbio
       
        print("<br><hr>");
        print("<br/>Azbio Range:<br/>");
       
        $answer = convertor($line, $score1, $linereverse);
        print($answer['lowanswer'] . "-" . $answer['highanswer']); //print the lower bound and higher bound of the result
    }
    
}
print("<br><br>");
print("<button onclick=\"location.href='http://cs2.mwsu.edu/~sbeaver/SWEProject/';\">Return</button>");
print("</div>");
print("</div>");

?>

<br>
    </body>
</html>