<?php
	
	$file = ( isset($_GET['f']) && !empty($_GET['f']) ) ? base64_decode($_GET['f']) : NULL;
	$file = '../'.$file;
	$ext = strtolower(substr(strrchr($file, '.'), 1));
	$forbidden_ext = array('php');
	$mtypes = array(
		// archives
		'zip' => 'application/x-zip-compressed',
		'rar' => 'application/x-rar-compressed',
		'7z' => 'application/x-7z-compressed',
		// documents
		'pdf' => 'application/pdf',
		'odt' => 'application/vnd.oasis.opendocument.text',
		'doc' => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		'xls' => 'application/vnd.ms-excel',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'odp' => 'application/vnd.oasis.opendocument.presentation',
		'ppt' => 'application/vnd.ms-powerpoint',
		'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		// text
		'css' => 'text/css',
		'csv' => 'text/csv',
		'html' => 'text/html',
		'txt' => 'text/plain',
		'js' => 'application/javascript',
		'json' => 'text/json',
		'php' => 'text/php',
		'xhtml' => 'text/xhtml+xml',
		'xml' => 'text/xml',
		// images
		'gif' => 'image/gif',
		'png' => 'image/png',
		'jpg' => 'image/jpeg',
		'jpeg'=> 'image/jpeg',
		// audio
		'mp3' => 'audio/mpeg',
		'wav' => 'audio/x-wav',
		// video
		'mp4'=> 'video/mp4',
		'mpeg'=> 'video/mpeg',
		'mpg' => 'video/mpeg',
		'mpe' => 'video/mpeg',
		'mov' => 'video/quicktime',
		'avi' => 'video/x-msvideo',
		'flv' => 'video/x-flv',
		'wmv' => 'video/x-ms-wmv',
		// auther
		'exe' => 'application/x-msdownload',
		'application/octet-stream',
		'swf' => 'application/x-shockwaveflash',
	);
	if(is_file($file) && !in_array($ext, $forbidden_ext)) {

		if (array_key_exists($ext, $mtypes)) {
			$mtype = $mtypes[$ext];
		}
		else {$mtype = 'application/octet-stream';}

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public');
		header('Content-Description: File Transfer');
		header('Content-Type: '.$mtype);
		header('Content-Disposition: attachment; filename="'.basename($file).'"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.filesize($file));
		readfile($file);

	}

?>