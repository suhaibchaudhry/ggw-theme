Drupal.behaviors.quantityDropDown = function(context) {
	$qty = $('.add-to-cart #edit-qty', context);
	$qty.after(generateQtySelect());
	$qty.hide();
	
	$('select.qtySelect').change(function() {
		var $this = $(this);
		if($this.val() == '0') {
			$this.hide();
			$qty.show();
		} else {
			$qty.val($this.val());
		}
	});
}

Drupal.behaviors.insertTitleAboveSKU = function(context) {
	$('.product-info.model', context).before($('div.product-title', context));
}


function generateQtySelect() {
	var options = generateOption('1', '1');
	options += generateOption('2', '2');
	options += generateOption('3', '3');
	options += generateOption('4', '4');
	options += generateOption('5', '5');
	options += generateOption('6', '6');
	options += generateOption('7', '7');
	options += generateOption('8', '8');
	options += generateOption('9', '9');
	options += generateOption('10', '10');
	options += generateOption('12', '12');
	options += generateOption('13', '13');
	options += generateOption('14', '14');
	options += generateOption('15', '15');
	options += generateOption('0', 'Other');

	return '<select class="qtySelect" name="qty">'+options+'</select>';
}

function generateOption(value, text) {
	return '<option value="'+value+'">'+text+'</option>';
}