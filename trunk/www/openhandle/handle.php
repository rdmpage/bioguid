<?php

require_once ('../lib.php');


$url = 'http://bioguid.info:8080/openhandle/handle?'
	. 'id=' . urlencode($_GET['id'])
	. '&include=' . $_GET['include']
	. '&index=' . $_GET['index']
	. '&format=' . $_GET['format']
	. '&mimetype=' . urlencode($_GET['mimetype']);

$result = get($url);

header("Content-type: " . $_GET['mimetype'] . "\n\n");
echo $result;

?>