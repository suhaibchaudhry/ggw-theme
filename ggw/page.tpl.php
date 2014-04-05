<!DOCTYPE html>
<html lang="<?php print $language->language ?>" dir="<?php print $language->dir ?>">
<head>
<?php print $head ?>
<title><?php print $head_title ?></title>
<?php print $styles ?>
<?php print $scripts ?>
</head>

<body>
<div class="container">
<div class="header-wrap"><div class="header login-<?php print phptemplate_get_login_state() ?>">
  	<div class="logo">
    	<a href="<?php print $base_path ?>"><img src="<?php print $logo ?>" alt="<?php print $site_title ?>" height="<?php print $logo_height ?>" width="<?php print $logo_width ?>" /></a>
  	</div>
    <div class="header_right">
        <?php if (isset($secondary_links)) : ?><?php print theme('links', $secondary_links, array()) ?><?php endif; ?>
   		<?php if($search_box) : ?><div class="search_box"><?php print $search_box ?><div class="search-button"></div></div><?php endif; ?>
		<?php if(isset($header)) : ?><div class="header_left"><?php print $header ?></div><?php endif; ?>
        <?php if(isset($site_slogan)) :?><div class="slogan"><?php print $site_slogan ?></div><?php endif; ?>
        <?php if (isset($primary_links)) : ?>
  	<div class="primary_nav"><?php print theme('links', $primary_links, array()) ?></div>
    <?php if($primary_links_children) : ?><div class="primary_children"><?php print $primary_links_children ?></div><?php endif; ?>
  	<?php endif; ?>
     </div>
     <?php if(isset($header_two)) : ?><div class="header_right"><?php print $header_two ?></div><?php endif; ?>
</div></div>
<div class="clearfloat"></div>
<div class="containerbg login-<?php print phptemplate_get_login_state() ?>">
  <?php if($messages) : ?>
    <div class="system-messages"><?php print $messages ?></div>
  <?php endif; ?>
  <?php if($top_pane) : ?>
  <div class="top_pane">
  	<?php print $top_pane ?>
  </div>
  <?php endif; ?>
  <?php if($left) : ?>
  <div class="sidebar_left">
  	<?php print $left ?>
  </div>
  <?php endif; ?>
  <?php if($right) : ?>
  <div class="sidebar_right">
  	<?php print $right ?>
  </div>
  <?php endif; ?>
  <div class="content<?php print ggw_content_class($left, $right) ?><?php if(!empty($node->type)) {print ' type-'.$node->type;} ?>"<?php if(isset($node->nid)): ?> id="content-id-<?php print $node->nid ?>"<?php endif; ?>>
    <?php if ($title && !$is_front): ?>
    <?php if(arg(0) == 'taxonomy' && arg(1) == 'term' || $node->type == 'product') { ?>
		<h1 class="title <?php print $node->type ?>"><?php print $breadcrumb; ?></h1>
		<?php } else { ?>
    <h1 class="title <?php print $node->type ?>"><?php print $title; ?></h1>
    <?php } ?>
    <?php endif; ?>
    <?php if($tabs && !$is_front) : ?><?php print '<div class="tabs"><ul class="tabs primary">'.$tabs.'</ul></div>' ?><?php endif; ?>
    <?php if($tabs2 && !$is_front) : ?><?php print '<div class="tabs"><ul class="tabs2 secondary">'.$tabs2.'</ul></div>' ?><?php endif; ?>
    <?php if($tabs && !$is_front) : ?><div class="clear-block-tabs"></div><?php endif; ?>
  	<?php print $content ?>
  </div>
  <div class="clearfloat"></div>
  <div class="footer">
  	<?php print $footer ?>
    <p class="footer-message"><?php print $footer_message ?></p>
  </div>
  </div>
</div>
<?php print $closure; ?>
</body>
</html>