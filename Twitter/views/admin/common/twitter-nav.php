<nav id="section-nav" class="navigation vertical">
<?php
    $navArray = array(
        array(
            'label' => 'Import Tweets',
            'action' => 'index',
            'module' => 'twitter',
        ),
        array(
            'label' => 'Your Imports',
            'action' => 'browse',
            'module' => 'twitter',
        ),
    );
    echo nav($navArray, 'admin_navigation_settings');
?>
</nav>