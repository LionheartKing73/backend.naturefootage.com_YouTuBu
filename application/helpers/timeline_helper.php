<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function sec_to_hms($value) {
	$point_pos = strpos($value, '.');
	if ($point_pos === FALSE) {
		$ms = '';
	} else {
		$ms =  rtrim(substr($value, $point_pos), '0');
	}
	if ($ms == '.') {
		$ms = '';
	}

	$h = intval($value / 3600);
	$value %= 3600;
	$m = intval($value / 60);
	$s = $value % 60;

	$result = str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT)
		. ':' . str_pad($s, 2, '0', STR_PAD_LEFT) . $ms;

	return $result;
}

function hms_to_sec($value) {
	if (empty($value)) {
		return 0;
	}

	$parts = explode(':', $value, 3);

	switch (count($parts)) {
		case 1:
			list($h, $m, $s) = array(0, 0, $parts[0]);
			break;
		case 2:
			list($h, $m, $s) = array(0, $parts[0], $parts[1]);
			break;
		default:
			list($h, $m, $s) = array($parts[0], $parts[1], $parts[2]);
			break;
	}

	$s = str_replace(':', '.', $s);

	$sec = intval($h) * 3600 + intval($m) * 60 + floatval($s);

	return $sec;
}