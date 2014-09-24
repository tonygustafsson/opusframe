var	mobile = window.matchMedia("(max-width: 1090px)").matches;

var Ajax = {
	url: '/',
	responseElement: document.getElementsByTagName('article')[0],
	handleContent: 'replace',
	pushState: false,
	whenDone: false,

	get: function() {
		"use strict";

		var httpRequest = new XMLHttpRequest();

		httpRequest.onreadystatechange = function() {
			if (httpRequest.readyState === 4 && httpRequest.status === 200) {
				//Only fire when response is ready and HTTP status is OK
				var content = httpRequest.responseText;

				switch(Ajax.handleContent) {
					case "prepend":
						Ajax.responseElement.innerHTML = content + Ajax.responseElement.innerHTML;
						break;
					case "append":
						Ajax.responseElement.innerHTML = Ajax.responseElement.innerHTML + content;
						break;
					default:
						Ajax.responseElement.innerHTML = content;
				}

				if (Ajax.whenDone != false && typeof Ajax.whenDone === 'function') {
					Ajax.whenDone();
				}
			}
		};

		httpRequest.open('GET', Ajax.url);
		httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

		if (this.pushState === true) {
			httpRequest.addEventListener("load", function() {
				if (Ajax.url !== window.location) {
					window.history.pushState({id: Ajax.url}, '', Ajax.url);
				}
			}, false);
		}

		httpRequest.send();
	},
	uploadFile: function(file, originalFileName, itemID, imageID) {
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
};

function ajaxPage(url) {
	"use strict";

	Ajax.url = url;
	Ajax.responseElement = document.getElementById('main');
	Ajax.pushState = true;
	Ajax.get();
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
			Ajax.uploadFile(theFile, fileName, itemID, imageID);

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

function createModalImage(currentImage) {
	"use strict";

	function getImagePosition(lookFor) {
		var	modalImages = document.querySelectorAll('.modal-trigger'),
		i;

		for (i = 0; i < modalImages.length; i = i + 1) {
			if (modalImages[i] === lookFor) {
				return i;
			}
		}
	}

	currentImage.id = "thumb_" + getImagePosition(currentImage);

	currentImage.addEventListener('click', function(e) {
		e.preventDefault();

		var modal = document.createElement('div'),
			body = document.getElementsByTagName('body')[0],
			closeButton = document.createElement('div'),
			modalImage = document.createElement('img'),
			imageTitle = document.createElement('p');

		modalImage.src = this.getAttribute('href');
		modal.className = 'modal';
		modal.id = 'modal';
		modal.setAttribute('data-current-image', getImagePosition(currentImage));
		closeButton.className = 'close';
		imageTitle.innerHTML = this.getElementsByTagName('p')[0].innerHTML;
		imageTitle.className = 'image-title';

		modal.appendChild(closeButton);
		body.appendChild(modal);

		modalImage.addEventListener('load', function() {
			modal.appendChild(imageTitle);
			modal.appendChild(modalImage);

			var modalImagePos = this.getBoundingClientRect();
			imageTitle.style.left = modalImagePos.left + 'px';
			imageTitle.style.top = modalImagePos.top + 'px';
			imageTitle.style.width = modalImagePos.width + 'px';
		});

		modal.addEventListener('click', function() {
			body.removeChild(modal);
			modal = null;
		});
	});
}

function createModalImages() {
	"use strict";

	if (!mobile) {
		var i,
			modalImages = document.querySelectorAll('.modal-trigger'),
			currentImage;

		for (i = 0; i < modalImages.length; i = i + 1) {
			var currentImage = modalImages[i];
			createModalImage(currentImage);
		}
	}
}

function continuous_scroll(contentRoot) {
	"use strict";

	var dataOffset = 'data-offset',
		baseUrl = contentRoot.getAttribute('data-ajax-url'),
		count = contentRoot.getAttribute('data-count'),
		needsScrolling = document.getElementsByTagName('body')[0].innerHeight > window.innerHeight;

	//Set an offset for next request
	contentRoot.setAttribute(dataOffset, count);

	function getMoreContent() {
		var url = baseUrl + '?count=' + count + '&offset=' + contentRoot.getAttribute(dataOffset);

		//Get images through AJAX, make them modal images
		Ajax.url = url;
		Ajax.responseElement = contentRoot;
		Ajax.handleContent = "append";
		Ajax.whenDone = createModalImages;
		Ajax.get();

		//Change the offset so we won't load the same images again
		contentRoot.setAttribute(dataOffset, parseInt(contentRoot.getAttribute(dataOffset), 10) + parseInt(count, 10));
	}

	if (!needsScrolling) {
		getMoreContent();
	}

	window.addEventListener('scroll', function() {
		//Detect if we should stop trying to fetch more images
		var	endOfContent = document.getElementById('end-of-content'),
			bottomIsReached = (window.innerHeight + window.scrollY) >= document.body.offsetHeight;

    	if (!endOfContent && bottomIsReached) {
    		//If bottom is reached and there is more content
			getMoreContent();
		}
	});
}

(function ModalImagesControl() {
	"use strict";

	document.addEventListener("keydown", function(e) {
		var modal = document.getElementById('modal'),
			modalImages = document.querySelectorAll('.modal-trigger');

		if (modal === null) {
			return;
		}

		switch(e.keyCode) {
			case 27: //ESC
				modal.click();
				break;
			case 37: //LEFT
				var lastId = modalImages[0].parentNode.lastElementChild.id,
					prevImageId = "thumb_" + (parseInt(modal.getAttribute('data-current-image'), 0) - 1),
					prevImage;

				if (document.getElementById(prevImageId)) {
					prevImage = document.getElementById(prevImageId).getElementsByTagName('img')[0];
				}
				else {
					prevImage = document.getElementById(lastId).getElementsByTagName('img')[0];
				}

				modal.click(); //Remove old modal
				prevImage.click(); //Create new one

				break;
			case 39: //RIGHT
				var firstId = "thumb_0",
					nextImageId = "thumb_" + (parseInt(modal.getAttribute('data-current-image'), 0) + 1),
					nextImage;

				if (document.getElementById(nextImageId)) {
					nextImage = document.getElementById(nextImageId).getElementsByTagName('img')[0];
				}
				else {
					nextImage = document.getElementById(firstId).getElementsByTagName('img')[0];
				}

				modal.click(); //Remove old modal
				nextImage.click(); //Create new one

				break;
		}
	});
})();

(function detectWindowResize() {
	"use strict";

	window.addEventListener('resize', function () {
		mobile = window.matchMedia("(max-width: 1090px)").matches;
	});
})();

(function listenForPopState() {
	"use strict";

	window.addEventListener('popstate', function() {
		ajaxPage(location.pathname);
	});
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
		continuous_scroll(contentRoot);
	}
})();

(function enableModalImages() {
	"use strict";

	var modalImages = document.querySelectorAll('.modal-trigger');

	if (modalImages.length > 0) {
		createModalImages();
	}
})();