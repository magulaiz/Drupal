<?php

/**
 * @file
 * Gives file upload progress data.
 */

session_start();
$key = ini_get("session.upload_progress.prefix") . $_POST[ini_get("session.upload_progress.name")];
if (!empty($_SESSION[$key])) {
  $current = $_SESSION[$key]["bytes_processed"];
  $total = $_SESSION[$key]["content_length"];
  $progress['percentage'] = $current < $total ? ceil($current / $total * 100) : 100;
  $progress['message'] = t('Uploading... (@current of @total)', [
    '@current' => $current,
    '@total' => $total,
  ]);
  echo json_encode($progress);
}
else {
  $progress['percentage'] = 100;
  $progress['message'] = 'Upload completed';
  echo json_encode($progress);
}
