<?php
header('Content-Type: application/json');

$rootDir = dirname(__DIR__);

$seedScripts = [
    'seed_hero_cta.php' => 'Hero CTA & Slider Banners',
    'seed_badges_acf.php' => 'Badges ACF Fields',
    'seed_facts_acf.php' => 'Fact Statistics ACF Fields',
    'seed_values_acf.php' => 'Company Values ACF Fields',
    'seed_team_members.php' => 'Team & Medical Specialists',
    'seed_video_gallery.php' => 'YouTube & Video Gallery',
    'create_ro_contact_form.php' => 'Multilingual RO/EN Contact Form',
    'create_ro_footer_menus.php' => 'Footer Links & Navigation Tree'
];

$logs = [];
$successCount = 0;

foreach ($seedScripts as $script => $label) {
    $fullPath = $rootDir . '/' . $script;
    if (file_exists($fullPath)) {
        // Execute script via PHP CLI or ob_start
        $cmd = "php " . escapeshellarg($fullPath) . " 2>&1";
        $output = [];
        $returnCode = 0;
        exec($cmd, $output, $returnCode);

        $outStr = implode(" ", array_map('trim', $output));
        if ($returnCode === 0) {
            $logs[] = "✓ Executed $label ($script): " . ($outStr ?: "Done.");
            $successCount++;
        } else {
            $logs[] = "⚠️ Warning running $script: " . ($outStr ?: "Code $returnCode");
        }
    } else {
        $logs[] = "ℹ️ Skipped $script (File not found)";
    }
}

echo json_encode([
    'success' => true,
    'message' => "✓ Content Seeding Complete ($successCount scripts executed successfully).",
    'logs' => $logs
]);
