<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script type="text/javascript" src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>


<!-- <script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script> -->

<script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script>
async function CreatePDFfromHTML(classname, filename, hideSelectors = []) {
     $('#ajax-loader').show();
    // Hide certain elements before PDF generation
	var topMargin = 20;
	var bottomMargin = 20;
	var leftMargin = 20;
	var rightMargin = 20;
    $('#paymentButtons').hide();
    var content = classname;
    var element = document.querySelector('.' + content);
	
	const elementsToReplace = document.querySelectorAll('.' + classname + ' [data-truncated][data-full]');
    const originalContents = new Map(); // Store original truncated content

	// Expand to full content before generating PDF
	elementsToReplace.forEach((elem) => {
		if (!originalContents.has(elem)) {
			originalContents.set(elem, elem.innerHTML); // Save the original state (including attributes)
		}
		elem.innerHTML = elem.getAttribute('data-full'); // Show full content
	});
	
    // Temporarily hide elements specified in the hideSelectors array
    var hiddenElements = hideSelectors.map(selector => {
        let elems;
        if (selector.startsWith('#')) {
            elems = document.querySelectorAll(selector);
        } else {
            elems = document.getElementsByClassName(selector);
        }
        Array.from(elems).forEach(elem => elem.style.display = 'none');
        return elems;
    });

    element.classList.add('print-pdf');
	if(document.getElementById('remarks-heading')){
		document.getElementById('remarks-heading').style.display = 'block';
	}
	if(document.getElementById('address')){
		document.getElementById('address').classList.add('no-border');
	}
	if(document.getElementById('address_1')){
		document.getElementById('address_1').classList.add('no-border');
	}
	if(document.getElementById('address_2')){
		document.getElementById('address_2').classList.add('no-border');
	}
	
	
    // Convert HTML to canvas
    const canvas = await html2canvas(element, { scale: 2 });
    const imgData = canvas.toDataURL("image/png");

    // Create a new PDF document using pdf-lib
    const pdfDoc = await PDFLib.PDFDocument.create();

    // Page dimensions
    const pageWidth = canvas.width / 2; // PDF page width (adjust scaling)
    const pageHeight = pageWidth * 1.414; // A4 page aspect ratio (1.414)

    // Calculate available width and height for content by subtracting margins
    const contentWidth = pageWidth - leftMargin - rightMargin; // Content width minus left and right margins
    const contentHeight = pageHeight - topMargin - bottomMargin; // Content height minus top and bottom margins

    const totalHeight = canvas.height;

    // Crop the canvas for each page
    const croppedCanvas = document.createElement('canvas');
    croppedCanvas.width = canvas.width;
    const croppedCtx = croppedCanvas.getContext('2d');

    let offsetY = 0;
    const scale = contentWidth / canvas.width; // Scaling to fit content within the page, considering the margins

    while (offsetY < totalHeight) {
        const remainingHeight = totalHeight - offsetY;
        const drawHeight = Math.min(remainingHeight, contentHeight / scale); // Available space for content, considering top/bottom margins

        // Set the height for the cropped canvas for this page
        croppedCanvas.height = drawHeight;

        // Draw the relevant section of the full canvas onto the cropped canvas
        croppedCtx.clearRect(0, 0, croppedCanvas.width, croppedCanvas.height);
        croppedCtx.drawImage(
            canvas,
            0, offsetY,                   // Source x, y (start point of original canvas)
            canvas.width, drawHeight,      // Source width, height (portion to draw)
            0, 0,                          // Destination x, y (top-left of new canvas)
            croppedCanvas.width, drawHeight // Destination width, height
        );

        // Convert the cropped canvas to an image
        const croppedImgData = croppedCanvas.toDataURL("image/png");

        // Add a new page to the PDF
        const page = pdfDoc.addPage([pageWidth, pageHeight]);

        // Embed the cropped image in the PDF
        const imageBytes = await fetch(croppedImgData).then(res => res.arrayBuffer());
        const pngImage = await pdfDoc.embedPng(imageBytes);

        // Draw the image, adjusting for left, right, top, and bottom margins
        page.drawImage(pngImage, {
            x: leftMargin, // Adjust X position to include the left margin
            y: pageHeight - drawHeight * scale - bottomMargin, // Adjust Y position to include the bottom margin
            width: contentWidth, // Content width, considering left/right margins
            height: drawHeight * scale, // Content height, considering top/bottom margins
        });

        // Move the offset down for the next page
        offsetY += drawHeight;
    }

    // Save the PDF to a file
    const pdfBytes = await pdfDoc.save();

    // Create a Blob and trigger download
    const blob = new Blob([pdfBytes], { type: 'application/pdf' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '.pdf';
    link.click();
   $('#ajax-loader').hide();
    // Show the hidden elements again
    $('#paymentButtons').show();

    // Restore the visibility of hidden elements
    hiddenElements.forEach(elems => {
        Array.from(elems).forEach(elem => elem.style.display = '');
    });
	if(document.getElementById('remarks-heading')){
		document.getElementById('remarks-heading').style.display = 'none';
	}
	if(document.getElementById('address')){
		document.getElementById('address').classList.remove('no-border');
	}
	if(document.getElementById('address_1')){
		document.getElementById('address_1').classList.remove('no-border');
	}
	if(document.getElementById('address_2')){
		document.getElementById('address_2').classList.remove('no-border');
	}
    element.classList.remove('print-pdf');
	elementsToReplace.forEach((elem) => {
		if (originalContents.has(elem)) {
			elem.innerHTML = originalContents.get(elem); // Restore original content
		}
	});
}


</script>