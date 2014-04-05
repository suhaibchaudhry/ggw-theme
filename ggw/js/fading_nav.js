Drupal.behaviors.google_maps = function() {
	$('.googlemap').append('<iframe width="425" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=FRp3xQEdQWhO-in1x_B2_sJAhjEi2VfMa4NrOg&amp;q=8000+Harwin+dr.+%23200+Houston,+TX+77036&amp;aq=&amp;sll=29.718298,-95.524799&amp;sspn=0.010566,0.021136&amp;ie=UTF8&amp;hq=&amp;hnear=8000+Harwin+Dr+%23200,+Houston,+Harris,+Texas+77036&amp;t=m&amp;z=14&amp;start=0&amp;ll=29.718298,-95.524799&amp;output=embed"></iframe><br /><small><a href="https://maps.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=FRp3xQEdQWhO-in1x_B2_sJAhjEi2VfMa4NrOg&amp;q=8000+Harwin+dr.+%23200+Houston,+TX+77036&amp;aq=&amp;sll=29.718298,-95.524799&amp;sspn=0.010566,0.021136&amp;ie=UTF8&amp;hq=&amp;hnear=8000+Harwin+Dr+%23200,+Houston,+Harris,+Texas+77036&amp;t=m&amp;z=14&amp;start=0&amp;ll=29.718298,-95.524799" style="color:#0000FF;text-align:left">View Larger Map</a></small>');
};

Drupal.behaviors.childSelectorsReplacements = function(context) {
	$('dl.search-results', context).addClass('grid-page');
	$(".grid-page .views-row:nth-child(4n)", context).addClass('cornerElement');
}