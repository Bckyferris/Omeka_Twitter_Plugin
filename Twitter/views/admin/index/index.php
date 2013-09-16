<?php
    echo head(array('title' => 'Twitter Import', 'bodyclass' => 'primary', 'content_class' => 'horizontal-nav'));
?>
<?php echo common('twitter-nav'); ?>

<div id="primary">
    <?php echo flash(); ?>
    <h2><?php echo __('Select your JSON file'); ?></h2>
    <?php echo $this->form; ?>        
</div>


  <?php


//$json = file_get_contents(TWITTER_IMPORT_DIRECTORY . '/JSON_files/twittersample.js');

//$obj = json_decode($json);

//var_dump($obj);

  ?>

<?php
    echo foot();
?>
