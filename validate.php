<html>
    <body>
    <?php
$result;
if($_POST['Azbio']>=3)
    $result['success']=true;
    $result['result']=$_POST['Azbio'];
?>
<h3><?php echo json_encode($result['result']); echo $result['success'] ?></h3>
    </body>
</html>


