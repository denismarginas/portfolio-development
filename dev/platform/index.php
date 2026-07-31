<?php

require __DIR__ . '/core/autoload.php';

$page = $_GET['page'] ?? '';

if ($page) {
    if ($page === 'workflow-ui') {
        // workflow-ui is the default homepage, rendered below
        $page = '';
    } else {
        // Discover page-type components
        $componentsDir = __DIR__ . '/components';
        $found = false;
        foreach (glob($componentsDir . '/*/component.json') as $cf) {
            $meta = json_decode(file_get_contents($cf), true);
            if (($meta['type'] ?? '') === 'page' && ($meta['name'] ?? '') === $page) {
                $index = dirname($cf) . '/index.php';
                if (file_exists($index)) {
                    require $index;
                    $found = true;
                    break;
                }
            }
        }
        if (!$found) {
            http_response_code(404);
            echo '<h1>Page not found</h1>';
        }
        exit;
    }
}

if (!$page) {
    // Redirect bare / to ?page=workflow-ui
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    if ($qs === '' || $qs === 'page=workflow-ui') {
        if ($qs === '') {
            header('Location: ?page=workflow-ui', true, 302);
            exit;
        }
    }
}

$pageTitle = platform_data::getString('title', 'Workflow') . ' ' . platform_data::getString('pageTitleSuffix', '| Platform');
$metaDesc = platform_data::getString('meta_description');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="components/workflow-ui/assets/css/style.css">
    <script src="assets/js/platform.js" defer></script>
    <script src="components/workflow-ui/assets/js/workflow-ui.js" defer></script>
</head>
<body class="platform-body">
    <?php echo render_workflow_ui(); ?>
</body>
</html>
