(function($) {

	'use strict';

	$(function() {
		$('#ut_teams_go').on('click', function(e){
			e.preventDefault();
			var url = $('#ut_teams').val();
			window.location = url;
		});
	});

})(jQuery);
