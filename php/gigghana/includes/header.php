<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>GigGhana - Your Skill, Your Success, Your Ghana</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Meta tags for SEO -->
    <meta name="description" content="Ghana's premier marketplace for freelance services. Connect talented service providers with clients across Ghana.">
    <meta name="keywords" content="Ghana freelance, Ghana jobs, Ghana services, GigGhana, freelancer Ghana">
    
    <!-- Open Graph -->
    <meta property="og:title" content="GigGhana - Your Skill, Your Success, Your Ghana">
    <meta property="og:description" content="Ghana's premier marketplace for freelance services">
    <meta property="og:image" content="/assets/images/og-image.png">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    
    <?php
    // Get flash message if exists
    $flash = getFlash();
    ?>
</head>
<body>
    <?php if ($flash): ?>
        <div class="notification notification-<?php echo $flash['type']; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
        <script>
            setTimeout(() => {
                document.querySelector('.notification').remove();
            }, 3000);
        </script>
    <?php endif; ?>