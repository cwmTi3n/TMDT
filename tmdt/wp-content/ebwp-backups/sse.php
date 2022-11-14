<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache');
$ebwp_backups_dir = dirname( __FILE__ );
$procstat_file = $ebwp_backups_dir . DIRECTORY_SEPARATOR . "PROCSTAT";
if ( ! file_exists( $procstat_file ) ) {
echo "{}";
die();
}
$content = @file_get_contents( $procstat_file );
echo $content ? $content : "{}";
if ( isset( $_GET["unlink"] ) ) {
$procstat_file = $ebwp_backups_dir . DIRECTORY_SEPARATOR . "PROCSTAT";
@unlink( $procstat_file );
$lockfile = $ebwp_backups_dir . DIRECTORY_SEPARATOR . "LOCKFILE";
if ( file_exists( $lockfile ) ) {
@unlink( $lockfile );
}
}
die();