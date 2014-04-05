<?php /*Requires a present called search for displaying search result images*/ ?>
<?php $node = $result["node"] ?>
<div class="views-row">
	<div class="views-field-title">
		<span class="field-content">
			<a href="<?php print $url ?>"><?php print $title ?></a>
		</span>
	</div>

    <?php if($node->field_image_cache[0]) { ?>
	<div class="views-field-field-image-cache-fid"><div class="field-content"><a href="<?php print $url ?>"><?php print theme('imagecache', 'product_grid_slim', $node->field_image_cache[0]["filepath"], $node->field_image_cache[0]["data"]["alt"], $node->field_image_cache[0]["data"]["title"]); ?></a></div></div>
    <?php } ?>
</div>