<?php
 
include_once 'database.php';

$database = new Database();
$database->connect();

//Create
if (isset($_POST['create'])) {
 
  $obj = null;
  $obj['username'] = $_POST['username'];
  $obj['type'] = $_POST['type'];
  $obj['devid'] = $_POST['devid'];
  $obj['stream'] = $_POST['stream'];

  if(!$database->insert(Database::R_DEVICE, $obj)) {
    die("SQL Error Message: ".$database->getError());
  }
}
 
if (isset($_GET['delete'])) {
 
  $obj["username"] = $_GET['delete'];

  if(!$database->remove(Database::R_DEVICE, $obj)) {
    // ignore error
  }

  header("Location: reg.php");
}
 
?>