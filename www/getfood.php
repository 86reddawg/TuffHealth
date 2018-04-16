<?php

$q = intval($_GET['q']);

$con = mysqli_connect('localhost','health','user','pass');
if (!$con) {
    die('Could not connect: ' . mysqli_error($con));
}

mysqli_select_db($con,"health");
$sql="SELECT * FROM food WHERE foodid = '".$q."'";
$result = mysqli_query($con,$sql);


if ($row = mysqli_fetch_array($result)){
    echo json_encode($row);
}


mysqli_close($con);

?>




  