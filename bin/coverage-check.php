<?php
$minimum = (float) ( $argv[1] ?? 0 );
$path = __DIR__ . '/../coverage/clover.xml';

$xml = is_readable( $path ) ? simplexml_load_file( $path ) : false;
if ( ! $xml ) {
	fwrite( STDERR, "Could not read clover report at {$path}\n" );
	exit( 1 );
}

$metrics = $xml->project->metrics;
$total = (int) $metrics['statements'];
$covered = (int) $metrics['coveredstatements'];
$percent = $total > 0 ? ( $covered / $total ) * 100 : 0;

printf( "Coverage: %.2f%% (%d/%d), minimum %d%%\n", $percent, $covered, $total, $minimum );

if ( $percent < $minimum ) {
	fwrite( STDERR, "Coverage below minimum of {$minimum}%\n" );
	exit( 1 );
}
