<?php

session_start();
$progress = [
  'message' => 'Starting upload...',
  'percentage' => -1,
];
$key = ini_get("session.upload_progress.prefix") . $_POST[ini_get("session.upload_progress.name")];
$status = $_SESSION[$key];
if (isset($status['bytes_processed']) && !empty($status['content_length'])) {
  $progress['percentage'] = round(100 * $status['bytes_processed'] / $status['content_length']);
}
header('Content-Type: application/json');
echo(json_encode($progress));
