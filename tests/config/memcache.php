<?php
return array(
	'default'=>array(
		'instant_death'      => TRUE,               // Take server offline immediately on first fail (no retry)
		'servers'            => array(
				array(
						'host'             => '192.168.1.101',
						'port'             => 11211,
						'persistent'       => FALSE,
						'weight'           => 1,
						'timeout'          => 1,
						'retry_interval'   => 15,
						'status'           => TRUE,
				)
		),
	)
);