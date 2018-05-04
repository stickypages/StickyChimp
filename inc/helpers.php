<?php

function _log($msg=null) {
	$data = PHP_EOL ." ". $msg;

	if(!empty($data)) {
		file_put_contents(__DIR__.'/../sticky.log', $data, FILE_APPEND);
	}
}