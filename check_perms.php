<?php
$dir = 'bootstrap/cache';
echo "Checking $dir...\n";
if (file_exists($dir)) {
    echo "Exists: Yes\n";
    if (is_dir($dir)) {
        echo "Is Dir: Yes\n";
        if (is_writable($dir)) {
            echo "Is Writable: Yes\n";
        } else {
            echo "Is Writable: No\n";
        }
        $testFile = "$dir/test_php_write.txt";
        if (file_put_contents($testFile, "test") !== false) {
            echo "Write Test: Success\n";
            unlink($testFile);
        } else {
            echo "Write Test: Failure\n";
        }
    } else {
        echo "Is Dir: No\n";
    }
} else {
    echo "Exists: No\n";
}
