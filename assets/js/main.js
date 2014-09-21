function ajaxGet(url, element, method, pushState) {
	"use strict";

	var httpRequest = new XMLHttpRequest();

	httpRequest.onreadystatechange = function() {
		if (httpRequest.readyState === 4 && httpRequest.status === 200) {
			//Only fire when response is ready and HTTP status is OK
			var content = httpRequest.responseText;

			switch(method) {
				case "prepend":
					element.innerHTML = content + element.innerHTML;
					break;
				case "append":
					element.innerHTML = element.innerHTML + content;
					break;
				default:
					element.innerHTML = content;
			}
		}
	};

	httpRequest.open('GET', url);
	httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

	if (pushState) {
		httpRequest.addEventListener("load", function() {
			if (url !== window.location) {
				window.history.pushState({path: url}, '', url);
			}
		}, false);
	}

	httpRequest.send();
}

function ajaxPage(url) {
	"use strict";

	var article = document.getElementById('main');
	ajaxGet(url, article, "replace", true);
}

function ajaxUploadFile(file, originalFileName, itemID, imageID)
{
	"use strict";
	var httpRequest = new XMLHttpRequest();

	httpRequest.onreadystatechange = function() {
		var imageSrc = httpRequest.responseText,
			thumbnail = document.getElementById('thumb_' + imageID);
		
		thumbnail.src = imageSrc;
	};

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

function isInArray(string, array) {
	"use strict";

	var i;

	for (i = 0; i < array.length; i = i + 1)
	{
		if (array[i] == string)
		{
			return true;
		}
	}

	return false;
}

function handleImageFileSelect(e) {
	"use strict";

	if (! window.File || ! window.FileReader || ! window.FileList || ! window.Blob)
	{
		return;
	}

	var uploadImage = e.srcElement.parentNode,
		uploadArea = uploadImage.parentNode,
		fileInput = e.target,
		file = fileInput.files[0],
		itemID = uploadArea.getAttribute('data-item-id'),
		imageID = uploadImage.getAttribute('data-image-id'),
		outputError = document.getElementById('images_upload_error_' + imageID),
		maxSize = uploadArea.getAttribute('data-max-size'),
		acceptedFileTypes = fileInput.getAttribute('accept').split(', ');

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

	var reader = new FileReader(),
		fileName = file.name,
		thumbnail = this.parentNode.getElementsByClassName('images_upload_thumb')[0],
		imageLoadingURL = thumbnail.getAttribute('data-image-loading-url');

	reader.onload = (function(theFile) {
		return function() {
			thumbnail.src = imageLoadingURL;
			ajaxUploadFile(theFile, fileName, itemID, imageID);

			var nextImageID = parseInt(imageID, 10) + 1,
				nextUploader = document.getElementById('images_upload_section_' + nextImageID);

			if (nextUploader !== null)
			{
				nextUploader.className = 'image_upload';
			}
		};
	})(file);

	// Read in the image file as a data URL
	reader.readAsDataURL(file);
}

function continuous_scroll(contentRoot, count, offset) {
	"use strict";

	var dataCount = 'data-count',
		dataOffset = 'data-offset';

	if (!contentRoot.hasAttribute(dataCount))
	{
		contentRoot.setAttribute(dataCount, count);
	}

	if (!contentRoot.hasAttribute(dataOffset))
	{
		contentRoot.setAttribute(dataOffset, offset);
	}

	window.addEventListener('scroll', function() {
		var	endOfContent = document.getElementById('end-of-content');

    	if (!endOfContent && (window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
			var url = '/opusframe/images?count=' + contentRoot.getAttribute(dataCount) + '&offset=' + contentRoot.getAttribute(dataOffset);
			ajaxGet(url, contentRoot, "append");
			contentRoot.setAttribute(dataOffset, parseInt(contentRoot.getAttribute(dataOffset), 10) + parseInt(count, 10));
		}
	});
}

(function listenForPopState() {
	"use strict";

	window.addEventListener('popstate', function() {
		ajaxPage(location.pathname);
	});
})();

(function focusOnFirstInput() {
	"use strict";

	if (document.forms[0])
	{
		document.forms[0].elements[0].focus();
	}
})();

(function createRanges() {
	//Use HTML5 ranges
	"use strict";

	var ranges = document.querySelector('input[type=range]');

	if (ranges) {
		ranges.addEventListener('change', function() {
			var rangeId = this.getAttribute('id'),
				helperId = rangeId + '_helper',
				range = document.getElementById(rangeId),
				helper = document.getElementById(helperId);

			helper.innerHTML = range.value;
		});
	}
})();

(function imageUploading() {
	"use strict";

	//Add click events to image uploading thumbs
	var imageUploadThumbs = document.getElementsByClassName('images_upload_thumb'),
		imageUploadInputs = document.getElementsByClassName('images_upload_input'),
		removeImagesLinks = document.getElementsByClassName('remove-image-link'),
		i;

	for (i = 0; i < imageUploadThumbs.length; i = i + 1) {
		imageUploadThumbs[i].addEventListener('click', function() {
			var id = this.parentNode.getAttribute('data-image-id');
			document.getElementById('images_file_upload_' + id).click();
		});
	}

	//Add change event listenerer on file uploads
	for (i = 0; i < imageUploadInputs.length; i = i + 1) {
	    imageUploadInputs[i].addEventListener('change', handleImageFileSelect, false);
	}

	//Add events for removing uploaded images
	for (i = 0; i < removeImagesLinks.length; i = i + 1) {
	    removeImagesLinks[i].addEventListener('click', function(e) {
	    	e.preventDefault();

	    	var url = this.getAttribute('href'),
				httpRequest = new XMLHttpRequest();

			httpRequest.onreadystatechange = function() {
				var content = httpRequest.responseText;
				
				if (content === "OK") {
					var movieID = e.srcElement.parentNode.parentNode.getAttribute('data-image-id'),
						thumbnail = document.getElementById('thumb_' + movieID),
						noImageURL = thumbnail.getAttribute('data-no-image-url');

					thumbnail.src = noImageURL;
				}
			};

			httpRequest.open('GET', url);
			httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			httpRequest.send();
	    });
	}
})();

(function enableContinuousScrolling() {
	//Enable continuous scrolling
	"use strict";

	var contentRoot = document.getElementById('image-area');

	if (contentRoot)
	{
		continuous_scroll(contentRoot, 5, 5);
	}
})();