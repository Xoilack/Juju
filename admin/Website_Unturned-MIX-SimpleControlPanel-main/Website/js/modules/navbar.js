function ToggleCollapsedMenu(ElementId, Timeout) {
	var Element = document.getElementById(ElementId);
	if (Element.style.display == 'none') {
		Element.style.display = 'block';
		setTimeout(function() {
			Element.style.height = '100%';
			Element.style.opacity = '1';
		}, Timeout/3);
	} else {
		Element.style.height = '0%';
		Element.style.opacity = '0';
		setTimeout(function() {
			Element.style.display = 'none';
		}, Timeout);
	}
}