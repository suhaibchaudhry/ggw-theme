<?php
function ggw_form_element($element, $value) {
  // This is also used in the installer, pre-database setup.
  $t = get_t();

  $output = '<div class="form-item"';
  if (!empty($element['#id'])) {
    $output .= ' id="' . $element['#id'] . '-wrapper"';
  }
  $output .= ">\n";
  $required = !empty($element['#required']) && $element['#title'] != 'Size' ? '<span class="form-required" title="' . $t('This field is required.') . '">*</span>' : '';

  if (!empty($element['#title'])) {
    $title = $element['#title'];
	if($title == 'Size') {
		$title = 'Select a size';
	}
    if (!empty($element['#id'])) {
      $output .= ' <label for="' . $element['#id'] . '">' . $t('!title: !required', array('!title' => filter_xss_admin($title), '!required' => $required)) . "</label>\n";
    }
    else {
      $output .= ' <label>' . $t('!title: !required', array('!title' => filter_xss_admin($title), '!required' => $required)) . "</label>\n";
    }
  }

  $output .= " $value\n";

  if (!empty($element['#description'])) {
    $output .= ' <div class="description">' . $element['#description'] . "</div>\n";
  }

  $output .= "</div>\n";

  return $output;
}

//Wrap fieldsets into a div
function ggw_fieldset($element) {
	if (!empty($element['#collapsible'])) {
		drupal_add_js('misc/collapse.js');

		if (!isset($element['#attributes']['class'])) {
 			$element['#attributes']['class'] = '';
		}

		$element['#attributes']['class'] .= ' collapsible';

		if (!empty($element['#collapsed'])) {
 			$element['#attributes']['class'] .= ' collapsed';
 		}
 	}

	return '<div class=\'fieldset-outer fieldset-'.$element['#attributes']['class'].' \'><fieldset'. drupal_attributes($element['#attributes']) .'>'. ($element['#title'] ? '<legend>'. $element['#title'] .'</legend>' : '') . (isset($element['#description']) && $element['#description'] ? '<div>'. $element['#description'] .'</div>' : '') . (!empty($element['#children']) ? $element['#children'] : '') . (isset($element['#value']) ? $element['#value'] : '') ."</fieldset></div>\n";
}

function ggw_uc_product_price($price, $context, $options = array()) {
  global $user;

  $output = '<div class="product-info ' . implode(' ', (array) $context['class']) . '">';
  if($user->uid) {
  	$output .= uc_price($price, $context, $options);
  } else {
	$output .= 'Please login for price'; 
  }
  $output .= '</div>';

  return $output;
}

function ggw_content_class($left, $right) {
	if(empty($left) && !empty($right)) {
		return ' one-bar lbar';
	} else if(!empty($left) && empty($right)) {
		return ' one-bar rbar';
	} elseif(!empty($left) && !empty($right)) {
		return ' both-bars';
	} else {
		return ' no-bars';
	}
}

//Theme breadcrums
function ggw_breadcrumb($breadcrumb) {
  if (module_exists('taxonomy_breadcrumb')) {
    $item = menu_get_item();
    if($item['path'] == 'taxonomy/term/%') {
      require_once(drupal_get_path('module', 'taxonomy_breadcrumb') . '/taxonomy_breadcrumb.inc');
      $breadcrumb = _taxonomy_breadcrumb_generate_breadcrumb(arg(2));
    }

    if($item['path'] == 'node/%' && $item['page_arguments'][0]->type == 'product') {
      $last = array_pop($breadcrumb);
      $breadcrumb = array_slice($breadcrumb, 0, 3);
      $breadcrumb[] = $last;
    }
  }

  //return theme_breadcrumb($breadcrumb);
  
  if (!empty($breadcrumb)) {
    return implode(' › ', $breadcrumb);
  }
}

function ggw_preprocess_node(&$vars) {
	//Determine if product is last in row
	if($vars['type'] == 'product') {
		drupal_add_js(drupal_get_path('theme', 'ggw').'/js/product.js');
	}
}