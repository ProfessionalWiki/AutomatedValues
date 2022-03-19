
$('span.wikibase-edittoolbar-container').click(function() {
	$('textarea.wikibase-labelview-input').filter(function() {
		return $(this).attr('lang') === 'en';
	}).hide();
});
