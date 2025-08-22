<?php
/**
 * Simple syntax test for the plugin
 * This tests if all PHP files have valid syntax
 */

echo "Testing plugin file syntax...\n";

$files_to_test = [
    'wiki-image-social-share.php',
    'includes/class-sti-functions.php',
    'includes/class-sti-helpers.php',
    'includes/class-sti-integrations.php',
    'includes/class-sti-shortcodes.php',
    'includes/class-sti-shortlink.php',
    'includes/class-sti-versions.php',
    'includes/class-wiss-whatsapp-optimizer.php',
    'includes/admin/class-sti-admin.php',
    'includes/admin/class-sti-admin-options.php',
    'includes/admin/class-sti-admin-fields.php',
    'includes/admin/class-sti-admin-notices.php',
    'includes/admin/class-sti-admin-helpers.php'
];

$errors = [];

foreach ($files_to_test as $file) {
    if (!file_exists($file)) {
        $errors[] = "File not found: $file";
        continue;
    }
    
    $output = [];
    $return_code = 0;
    
    // Test PHP syntax
    exec("php -l \"$file\" 2>&1", $output, $return_code);
    
    if ($return_code !== 0) {
        $errors[] = "Syntax error in $file: " . implode("\n", $output);
    } else {
        echo "✅ $file - OK\n";
    }
}

if (empty($errors)) {
    echo "\n🎉 All files passed syntax check!\n";
    exit(0);
} else {
    echo "\n❌ Syntax errors found:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    exit(1);
}
