<?php

require_once('../triple_store.php');

$endpoint_config = array
(
  /* db */
  'db_name' => $config['db_name'],
  'db_user' => $config['db_user'],
  'db_pwd' => $config['db_passwd'],
  /* store */
  'store_name' => 'arc',

/* endpoint */
'endpoint_features' => array(
    'select', 'construct', 'ask', 'describe', 
    'dump' /* dump is a special command for streaming SPOG export */
  ),
  'endpoint_timeout' => 60, /* not implemented in ARC2 preview */
  'endpoint_read_key' => '', /* optional */
  'endpoint_write_key' => 'somekey', /* optional */
  'endpoint_max_limit' => 250, /* optional */
);  


// We may need to go through a proxy
$store_config['proxy_host'] = $config['proxy_name'];
$store_config['proxy_port'] = $config['proxy_port'];
	
/* instantiation */
$ep = ARC2::getStoreEndpoint($endpoint_config);


/* request handling */
$ep->go();
?>