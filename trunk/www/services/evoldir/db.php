<?php

require_once 'config.inc.php';
require_once($config['adodb_dir']);

$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


function store_message($headers, $body)
{
	global $db;
	
	$id = '';
	
	$message_id = $headers['Message-ID'];
	if ($message_id == '')
	{
		$message_id = $headers['Message-Id'];
	}
	
	$id = md5($message_id);
	
	$sql = 'SELECT * FROM `message` WHERE (id = ' . $db->qstr($id) . ') LIMIT  1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 0)
	{
		$sql = 'INSERT INTO `message`(`message-id`, `from`, subject, body, id) VALUES ('
		 . $db->qstr($message_id)
		 . ', ' . $db->qstr($headers['From'])
		 . ', ' . $db->qstr($headers['Subject'])
		 . ', ' . $db->qstr($body)
		 . ', ' . $db->qstr($id)
		 . ')';
		 
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
		
	}
	else
	{
		$id = '';
	}
	return $id;
	
}

function get_message($id)
{
	global $db;
	
	$msg = new stdClass;
	
	
	$sql = 'SELECT * FROM `message` WHERE (id = ' . $db->qstr($id) . ') LIMIT  1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$msg->messageid = $result->fields['message-id'];
		$msg->subject = $result->fields['subject'];
		$msg->from = $result->fields['from'];
		$msg->body= $result->fields['body'];
	}
	return $msg;
}
?>
