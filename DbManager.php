<?php
require_once './Convertor2.php';
class Database {
    
   private $con;
   private $answer=[];
  
    function __construct($HOST, $DBNAME, $DBUSERNAME, $DBPASSWORD) {
        $this->con = mysqli_connect($HOST, $DBUSERNAME, $DBPASSWORD, $DBNAME);
        if (mysqli_connect_errno()) {
            exit( "Failed to connect to MySQL: " . mysqli_connect_error());
        }  
    }

    function AzbioCncData(){    
       
        $result = $this->con->query("SELECT DISTINCT azbioResults.PatientID,azbioResults.TestDate, azbioResults.ConditionsID,
        round(avg(azbioResults.Score)) as azbioScore, round(avg(cncResults.`Phonemes Correct`)) as PhonemesCorrect,
        round(avg(cncResults.`Words with 3 Phonemes Correct`)) as ThreeWordsCorrect from azbioResults
        join cncResults on azbioResults.PatientID=cncResults.PatientID and azbioResults.ConditionsID =cncResults.ConditionsID and azbioResults.TestDate = cncResults.TestDate and azbioResults.Score!=0 and `cncResults`.`Words with 3 Phonemes Correct`!=0 and `cncResults`.`Phonemes Correct`!=0
        where azbioResults.TestDate!=0000 and `cncResults`.`Words with 3 Phonemes Correct` is NOT null group by azbioResults.PatientID, azbioResults.TestDate, azbioResults.ConditionsID");
      
        //while rows, get row and add data to corresponding array
        while ($row = $result->fetch_assoc()) {
            $data1[$row['azbioScore']][] = $row['PhonemesCorrect'];
            $data2[$row['azbioScore']][] = $row['ThreeWordsCorrect'];
        }
        $data1=$this->Average($data1,0);
        $data2=$this->Average($data2,0);

        $this->answer[0]=$data1;
        $this->answer[1]=$data2;
        return $this->answer;
    }

    function AzbioBkbData(){
        $result = $this->con->query("SELECT DISTINCT azbioResults.PatientID,azbioResults.TestDate, azbioResults.ConditionsID, round(avg(azbioResults.Score)) as azbioScore, 
        round(avg(bkbResults.`SNR-50`),1) as SNR50 from azbioResults join bkbResults on azbioResults.PatientID=bkbResults.PatientID and azbioResults.ConditionsID =bkbResults.ConditionsID 
        and azbioResults.TestDate = bkbResults.TestDate and azbioResults.Score!=0 and bkbResults.`SNR-50`> -23 where azbioResults.TestDate!=0000 and 
        bkbResults.`SNR-50` is NOT null group by azbioResults.PatientID, azbioResults.TestDate, azbioResults.ConditionsID");
        //while rows, get row and add data to corresponding array
        while ($row = $result->fetch_assoc()) {
            $data1[$row['azbioScore']][] = $row['SNR50'];
        }
    
        $data1=$this->Average($data1,1);
     
        $this->answer[0]=$data1;
        return $this->answer;
    }
    
    function CncBkbData(){
        $result = $this->con->query("SELECT DISTINCT bkbResults.PatientID,bkbResults.TestDate, bkbResults.ConditionsID, round(avg(bkbResults.`SNR-50`),1) as bkbScore, 
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

        $data1=$this->Average($data1,0);
        $data2=$this->Average($data2,0);

        $this->answer[0]=$data1;
        $this->answer[1]=$data2;
        return $this->answer;
    }

    private function Average($data,$n)
    {
        foreach ($data as $key => $value) {
            $avg = round(array_sum($value) / count($value),$n);
            $data[$key] = $avg;
        }
        return $data;
    }
}

?>