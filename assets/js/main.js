document.forms[0].elements[0].focus();

var ranges = document.querySelector('input[type=range]');

if (ranges) {
	ranges.addEventListener('change', function(e) {
		var rangeId = this.getAttribute('id');
		var helperId = rangeId + '_helper';

		var range = document.getElementById(rangeId);
		var helper = document.getElementById(helperId);

		helper.innerHTML = range.value;
	});
}

function ajax(url) {
	var httpRequest = new XMLHttpRequest();

	httpRequest.onreadystatechange = function() {
		var article = document.getElementById('main');
		var content = httpRequest.responseText;

		article.innerHTML = content;
	}

	httpRequest.open('GET', url);
	httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

	httpRequest.addEventListener("load", function() {
		if (url != window.location) {
			window.history.pushState({path: url}, '', url);
		}
	}, false);

	httpRequest.send();
}

window.addEventListener('popstate', function(e) {
	ajax(location.pathname);
});

function getTitle(html) {
	var regex = new RegExp("(?s)<h2>.+?</h2>");
	preg_match(regex, html, matches);
	return matches[0];
}