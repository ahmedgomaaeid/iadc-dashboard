<?php
echo '<h2>PHP Version: ' . phpversion() . '</h2>';
echo '<h3>IMAP Extension: ' . (extension_loaded('imap') ? 'LOADED ✅' : 'NOT LOADED ❌') . '</h3>';
echo '<h3>imap_open exists: ' . (function_exists('imap_open') ? 'YES ✅' : 'NO ❌') . '</h3>';
echo '<h3>Disabled Functions:</h3>';
echo '<pre>' . ini_get('disable_functions') . '</pre>';
echo '<h3>OPcache Enabled: ' . (function_exists('opcache_get_status') && opcache_get_status() ? 'YES' : 'NO') . '</h3>';

// Try calling imap_open directly
echo '<h3>Direct test:</h3>';
try {
    $test = @imap_open('{iadcsuez.org:993/imap/ssl/novalidate-cert}INBOX', 'test', 'test');
    echo 'imap_open callable ✅ (connection result: ' . ($test ? 'connected' : 'failed to connect, but function works') . ')';
    if ($test) imap_close($test);
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage();
}
