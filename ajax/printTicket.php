<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../class/php_serial.class.php";

$rows = $_POST['array'];
print_r ($rows);

// Let's start the class
$serial = new phpSerial;

// First we must specify the device. This works on both linux and windows (if
// your linux serial device is /dev/ttyS0 for COM1, etc)
$device = "/dev/ttyUSB0";
$serial->deviceSet($device);
// Then we need to open it
$serial->deviceOpen();

// To write into
$serial->sendMessage("Hello !");

// Or to read from
$read = $serial->readPort();

// If you want to change the configuration, the device must be closed
$serial->deviceClose();

// We can change the baud rate
$serial->confBaudRate(2400);

?>
