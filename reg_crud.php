<?php
 
include_once 'database.php';
 
//Create
if (isset($_POST['create'])) {
 
  $username = $_POST['username'];
  $type = $_POST['type'];
  $devid = $_POST['devid'];
  $stream = $_POST['stream'];

 
  $sql = "insert into register(";
    $sql = $sql."username, ";
    $sql = $sql."type, ";
    $sql = $sql."devid, ";
    $sql = $sql."stream) values(";
    $sql = $sql."'".$username."', ";
    $sql = $sql."'".$type."', ";
    $sql = $sql."'".$devid."', ";
    $sql = $sql."'".$stream."')";
 
  $result = $mydb->query($sql);
 
  if (!$result) {
 
    die("SQL Error Message: ".$mydb->error);
  }
}
 
if (isset($_GET['delete'])) {
 
  $username = $_GET['delete'];
  $sql = "delete from register where username = '".$username."'";
  $result = $mydb->query($sql);
 
  header("Location: reg.php");
}
 
?>