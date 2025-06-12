function fitLabelTextWithCheckbox(label, maxFontSizePx = 20, minFontSizePx = 8, paddingPx = 10) {
	const input = label.querySelector('input');
	const labelWidth = label.clientWidth - paddingPx;
	
	const text = Array.from(label.childNodes)
	.filter(n => n.nodeType === Node.TEXT_NODE)
	.map(n => n.textContent.trim())
	.join(' ');
	
	const tempSpan = document.createElement('span');
	tempSpan.style.visibility = 'hidden';
	tempSpan.style.position = 'absolute';
	tempSpan.style.whiteSpace = 'nowrap';
	tempSpan.style.fontSize = maxFontSizePx + 'px';
	tempSpan.textContent = text;
	document.body.appendChild(tempSpan);
	
	const inputStyle = getComputedStyle(input);
	const inputWidth = input.offsetWidth + parseFloat(inputStyle.marginLeft) + parseFloat(inputStyle.marginRight);
	
	let fontSize = maxFontSizePx;
	
	while (fontSize >= minFontSizePx) {
		tempSpan.style.fontSize = fontSize + 'px';
		const totalWidth = tempSpan.offsetWidth + inputWidth;
		if (totalWidth <= labelWidth) {
			break;
		}
		fontSize -= 0.5;
	}
	
	document.body.removeChild(tempSpan);
	
	label.style.fontSize = fontSize + 'px';
}

const label = document.getElementById('stuff-me');
fitLabelTextWithCheckbox(label);