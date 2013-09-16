<?php 
$css = "table
{
border-collapse:collapse;
}
table,th, td
{
border: 1px solid black;
}
            }";
queue_css_string($css);

echo head(array('title' => __('Browse Tweets'),'bodyid'=>'tweets','bodyclass' => 'browse')); ?>

<h1><?php echo __('Browse Tweets');?> (<?php echo $total_results; ?> <?php echo __('total');?>)</h1>


<div class="items navigation" id="secondary-nav">
			<?php echo deco_nav();?>
		</div>
        
<div class="pagination">
    <?php echo pagination_links(); ?>
</div><!-- end pagination -->



  <table id = "twitter-table">
        <thead id="twitter-table-head">
            <tr>
            <?php
                $browseHeadings[__('Tweet ID')] = 'Item Type Metadata,Tweet ID';
                $browseHeadings[__('Text')] = null;
                $browseHeadings[__('Screen Name')] = 'Item Type Metadata,Tweet Screen Name';
                $browseHeadings[__('Date')] = 'Tweet Date Created';
                $browseHeadings[__('User Mention')] = null;
                $browseHeadings[__('Media URL')] = null;
                $browseHeadings[__('Hash Tags')] = null;
                $browseHeadings[__('Favorite Count')] = null;
                $browseHeadings[__('Retweet Count')] = null;
                $browseHeadings[__('Coordinates')] = null;
                $browseHeadings[__('User ID')] = 'Item Type Metadata,Tweet User ID';
                $browseHeadings[__('Replied to User')] = null;
                $browseHeadings[__('Replied to ID')] = null;
                $browseHeadings[__('Language')] = null;
                echo browse_sort_links($browseHeadings, array('link_tag' => 'th scope="col"', 'list_tag' => '')); 
             ?>  
            </tr>
        </thead>
        
        <tbody id="twitter-table-body">
        <?php 
        foreach($items as $tweet):
        	set_current_record('Item',$tweet);
        	$item= get_current_record('Item'); ?>
    
          <tr>
            <td>
             <?php echo link_to_item(metadata('Item',array('Item Type Metadata', 'Tweet ID'), array('class'=>'permalink'))); ?>
            </td>
            
            <td>
              <?php echo metadata('Item',array('Item Type Metadata', 'Tweet Text')); ?>
            </td>
            
            <td>
              <?php echo metadata('Item',array('Item Type Metadata', 'Tweet Screen Name')); ?>
            </td>
            
            <td>
              <?php echo metadata('Item',array('Item Type Metadata', 'Tweet Date Created'));  ?>
            </td>
            
            <td>
              <?php echo metadata('Item',array('Item Type Metadata', 'Tweet User Mention Screen Name')); ?>
            </td>
            
            <td>
              <?php echo metadata('Item',array('Item Type Metadata', 'Tweet Media Url')); ?>
            </td>
            
            <td>
             <?php //TO DO: make link to a list of other items with the same hash tag
			       echo metadata('Item',array('Item Type Metadata', 'Tweet Hash Tag')); ?>
            </td>
            
            <td>
              <?php echo metadata('Item',array('Item Type Metadata', 'Tweet Favorite Count')); ?>
            </td>
            
            <td>
              <?php echo metadata('Item',array('Item Type Metadata', 'Tweet Retweet Count')); ?>
            </td>
            
            <td>
              <?php $latitude = metadata('Item',array('Item Type Metadata', 'Tweet Latitude')); 
			  		$longitude = metadata('Item',array('Item Type Metadata', 'Tweet Longitude'));
					echo "Lat.: $latitude <br/> Long.: $longitude" ?>
            </td>
            
            <td>
             <?php echo metadata('Item',array('Item Type Metadata', 'Tweet User ID')); ?>
            </td>
            
            <td>
              <?php echo metadata('Item',array('Item Type Metadata', 'Tweet Replied to User ID')); ?>
            </td>
            
            <td>
              <?php echo metadata('Item',array('Item Type Metadata', 'Tweet Replied to ID')); ?>
            </td>
            
            <td>
              <?php echo metadata('Item',array('Item Type Metadata', 'Tweet Language')); ?>
            </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
  </table>


</div><!-- end primary -->

<?php echo foot(); ?>