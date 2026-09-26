<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$siteTitle = !empty($input['siteTitle']) ? $input['siteTitle'] : 'Antigravity Live';
$niche     = !empty($input['niche']) ? $input['niche'] : 'General Business';
$style     = !empty($input['style']) ? $input['style'] : 'Minimalist Modern';
$features  = isset($input['selectedFeatures']) && is_array($input['selectedFeatures']) ? $input['selectedFeatures'] : ['hero', 'contact', 'team'];

// Generate Gemini Antigravity AI Site Blueprint
$seedMap = [
    'hero'    => 'seed_hero_cta.php (Hero Carousel & CTA Banners)',
    'contact' => 'create_ro_contact_form.php (RO/EN Contact Form)',
    'team'    => 'seed_team_members.php (Team & Specialist Bios)',
    'gallery' => 'seed_video_gallery.php (Media & Video Gallery)',
    'badges'  => 'seed_badges_acf.php & seed_facts_acf.php (Badges & Metrics)',
    'footer'  => 'create_ro_footer_menus.php (Footer Navigation Tree)'
];

$selectedModules = [];
foreach ($features as $f) {
    if (isset($seedMap[$f])) {
        $selectedModules[] = $seedMap[$f];
    }
}

$blueprint = [
    'title' => $siteTitle,
    'niche' => $niche,
    'style' => $style,
    'summary' => "Custom $style site blueprint for '$siteTitle' in $niche. Architected with responsive components, ACF field structures, and bilingual translation support.",
    'themeName' => 'sparsha-wp (Antigravity Custom Theme)',
    'pages' => [
        'Home (Hero, Badges, Team, Services)',
        'About Us (Mission, Values, Specialists)',
        'Services & Treatments (ACF Grid)',
        'Journal / Blog (Polylang Enabled)',
        'Contact Us (Interactive Form)'
    ],
    'seedModules' => $selectedModules
];

echo json_encode([
    'success' => true,
    'blueprint' => $blueprint
]);
