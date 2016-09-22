<?php
 
define('MYHOST', 'localhost');
define('MYDATABASE', 'reorreg');
define('MYUSERNAME', 'root');
define('MYPASSWORD', '');
 
$mydb = new mysqli(MYHOST, MYUSERNAME, MYPASSWORD, MYDATABASE);
 
if ($mydb->connect_error) {

die("Connection Error Message: ".$mydb->connect_error);
}
 
?>