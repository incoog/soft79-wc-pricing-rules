<?php
$count = 0;
foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( dirname( __DIR__ ), FilesystemIterator::SKIP_DOTS ) ) as $file ) {
    if ( $file->getExtension() !== 'php' || strpos( $file->getPathname(), '/.git/' ) !== false ) continue;
    token_get_all( file_get_contents( $file->getPathname() ), TOKEN_PARSE );
    ++$count;
}
echo 'PASS syntax: ' . $count . ' PHP files on ' . PHP_VERSION . "\n";
