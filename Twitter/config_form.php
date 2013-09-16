<div class="field">
    <div class="two columns alpha">
        <label for="twitter_link_to_nav"><?php echo __('Add Link to Tweet display on Items/Browse Navigation'); ?></label>    
    </div>    
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Add a link to the Tweet display on all the items/browse pages.'); ?></p>
        <div class="input-block">        
        <?php echo get_view()->formCheckbox('twitter_link_to_nav', true, 
         array('checked'=>(boolean)get_option('twitter_link_to_nav'))); ?>        
        </div>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="per_page"><?php echo __('Number of Tweets Per Page'); ?></label>    
    </div>    
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('The number of Tweets displayed per page when browsing Tweets. (Maximum is '); ?><?php echo GEOLOCATION_MAX_LOCATIONS_PER_PAGE; ?>).</p>
        <div class="input-block">        
        <input type="text" class="textinput"  name="per_page" size="4" value="<?php echo get_option('twitter_per_page'); ?>" id="per_page" />        
        </div>
    </div>
</div>