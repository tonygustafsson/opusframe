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

function ajaxUploadFile(file, originalFileName, itemID, imageID)
{
	var httpRequest = new XMLHttpRequest();

	if (httpRequest.upload)
	{
		httpRequest.open("POST", '/opusframe/movies/image_upload', true);
		httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		httpRequest.setRequestHeader('X-Original-File-Name', originalFileName);
		httpRequest.setRequestHeader('X-Item-Id', itemID);
		httpRequest.setRequestHeader('X-Image-Id', imageID);
		httpRequest.send(file);
	}
}

window.addEventListener('popstate', function(e) {
	ajax(location.pathname);
});

function getTitle(html) {
	var regex = new RegExp("(?s)<h2>.+?</h2>");
	preg_match(regex, html, matches);
	return matches[0];
}

function isInArray(string, array) {
	for (var i = 0; i < array.length; i++)
	{
		if (array[i] == string)
			return true;
	}

	return false;
}

function handleImageFileSelect(e) {
	if (! window.File || ! window.FileReader || ! window.FileList || ! window.Blob)
		return;

	var uploadImage = e.srcElement.parentNode;
	var uploadArea = uploadImage.parentNode;
	var fileInput = e.target;
	var file = fileInput.files[0];
	var itemID = uploadArea.getAttribute('data-item-id');
	var imageID = uploadImage.getAttribute('data-image-id');
	var outputError = document.getElementById('images_upload_error_' + imageID);
	var outputSuccess = document.getElementById('images_upload_success_' + imageID);
	var maxSize = uploadArea.getAttribute('data-max-size');
	var acceptedFileTypes = fileInput.getAttribute('accept').split(', ');

	if (!isInArray(file.type, acceptedFileTypes))
	{
		outputError.style.display = 'block';
		outputError.innerHTML = 'This uploader is for images only!';
		return;
	}

	if (file.size > maxSize)
	{
		outputError.style.display = 'block';
		outputError.innerHTML = 'The images cannot be larger than ' + maxSize + ' bytes!';
		return;
	}

	var reader = new FileReader();
	var fileName = file.name;
	var thumbnail = this.parentNode.getElementsByClassName('images_upload_thumb')[0];

	reader.onload = (function(theFile) {
		return function(e) {
			thumbnail.src = e.target.result;
			thumbnail.style.opacity = '.3';
			ajaxUploadFile(theFile, fileName, itemID, imageID);
			outputSuccess.innerHTML = "Image saved.";
			outputSuccess.style.display = 'block';
			thumbnail.style.opacity = '1';
		};
	})(file);

	// Read in the image file as a data URL
	reader.readAsDataURL(file);
}

var imageUploadThumbs = document.getElementsByClassName('images_upload_thumb');

for (var i = 0; i < imageUploadThumbs.length; i++) {
    imageUploadThumbs[i].addEventListener('click', function(e) {
    	var id = this.parentNode.getAttribute('data-image-id');
		document.getElementById('images_file_upload_' + id).click();
	});
}

var imageUploadInputs = document.getElementsByClassName('images_upload_input');

for (var i = 0; i < imageUploadInputs.length; i++) {
    imageUploadInputs[i].addEventListener('change', handleImageFileSelect, false);
}