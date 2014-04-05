<?php
function ggw_settings($saved_settings) {
	$defaults = array('show_children' => 0);
	$settings = array_merge($defaults, $saved_settings);
	$form['show_children'] = array(
		'#type' => 'checkbox',
		'#title' => t('Show Primary Meny Children'),
		'#default_value' => $settings['show_children'],
	);

	return $form;
}
