function onNumberKeyPress(e, input, signed, precision, scale)
{
	var prefix = "";
	if (signed)
		prefix = "-|";
	var regexStr = prefix + "\\d|[\\.,]";
	var ret = (onKeyPress(e, new RegExp(regexStr)));
	return ret;
}

function validateNumber(name, input, signed, precision, scale)
{
	if (signed == null)
		signed = false;
	if (precision == null)
		precision = 10;
	if (scale == null)
		scale = 0;
	var regexps = new Array();
	var prefix = "";
	if (signed)
		prefix = "-?";
	var regexpStr = prefix + "\\d{0," + precision + "}";
	regexps[0] = new RegExp(regexpStr);
	if (scale > 0) {
		regexps[1] = new RegExp(regexpStr + "[\\.,]\\d{0," + scale + "}");
	}
	return validate(name, input, regexps);
}

function onMoneyKeyPress(e, input, signed, size)
{
	return onNumberKeyPress(e, input, signed, size, 2);
}

function validateMoney(name, input, signed, size)
{
	return validateNumber(name, input, signed, size, 2);
}

function onTimeKeyPress(e, input)
{
	var regexp = /\d|[:\.]/;
	return onKeyPress(e, regexp); 
}

function validateTime(name, input)
{
	var regexp = /\d{1,2}|\d{1,2}[:\.]\d{2}/;
	var ret = validate(name, input, regexp);
	return ret;
}

function onDateKeyPress(e, input)
{
	var regexp = /\d|-/;
	return onKeyPress(e, regexp); 
}

function validateDate(name, input)
{
	if (input.value == '')
		return true;
	var regexp = /\d{2,4}-?\d{2}-?\d{2}/;
	var ret = validate(name, input, regexp);
	if (!ret) {
		return false;
	}
	var ret = checkValidDate(input.value);
	if (!ret) {
		alert('Invalid date: ' + input.name);
		input.focus();	
	}
	return ret;
}

function validate(namn, input, regexp)
{
	if (input.value == '')
		return true;
	if (regexp instanceof Array) {
		for (var i=0; i < regexp.length; i++) {
			if (checkRegexp(input.value, regexp[i]))
				return true;
		}
	} else {
		if (checkRegexp(input.value, regexp))
			return true;
	}
	showValidationError(namn, input);
	return false;
}

function showValidationError(name, input)
{
	alert(name + ' har felaktigt format!');
	input.focus();
}

function onKeyPress(e, regex)
{
	if (isSpecialKey(e))
		return true;
	return regex.test(getCh(e));
}

function getCh(e)
{
	var key = window.event ? e.keyCode : e.which;
	if (isSpecialKey(e))
		return null;
	return String.fromCharCode(key);
}

function checkRegexp(value, regex)
{
	var ret = value.match(regex) == value;
	return ret;
}

function isSpecialKey(e)
{
	var key = window.event ? e.keyCode : e.which;
	return (key == 8 || key == 0 || key == 13 || key == 10);
}

function checkLength(e, value, length)
{
	var key = window.event ? e.keyCode : e.which;
	if (key == 8 || key == 0 || key == 13 || key == 10)
		return true;
	return value.length < length;
}

function checkValidDate(input)
{
	var parts = input.split("-");
	var yearfield;
	var monthfield = null;
	var dayfield = null;
	if (parts.length == 1) {
		var date = parts[0];
		if (date.length == 8) {
			yearfield = date.substring(0,4);
			monthfield = date.substring(4,6);
			dayfield = date.substring(6,8);
		} else {
			yearfield = date.substring(0,2);
			monthfield = date.substring(2,4);
			dayfield = date.substring(4,6);
		}
	} else {
		yearfield = parts[0];
		if (parts.length > 1) { 
			monthfield = parts[1]-1;
		}
		if (parts.length > 2) {
			dayfield = parts[2];
		}
	}
	if (monthfield != null) {
		if (monthfield.length > 0 && (monthfield < 0 || monthfield > 12)) {
			return false;
		}
	}
	if (dayfield != null) {
		if (dayfield.length > 0) {
			var dayobj = new Date(yearfield, monthfield, dayfield);
			if ((dayobj.getMonth()!=monthfield)||(dayobj.getDate()!=dayfield)||(dayobj.getFullYear()!=yearfield))
				return false;
		}
	}
	return true;
}

function suggest(name, str, url) {
	if (str.length == 0)
		return;
	url += '&name=' + name + '&value=' + str;

	handler = function(req) {
		var ss = document.getElementById(name + '_suggest')
		ss.innerHTML = req.responseText;
		ss.style.visibility = "visible";
	}

	AjaxRequest.get({
		"url": url,
		"onSuccess": handler
	});
}

function setSuggestion(name, value) {
	document.getElementById(name).value = value;
	ss = document.getElementById(name + '_suggest');
	ss.innerHTML = '';
	ss.style.visibility = "hidden";
}

function validateMandatory(label, input)
{
	if (input.value == '') {
		alert(label + ' är obligatoriskt!');
		return false;
	}
	return true;	
}

function saveForm()
{
	var saveElement = document.createElement('input');
	saveElement.setAttribute('type', 'hidden');
	saveElement.setAttribute('name', 'save');
	saveElement.setAttribute('value', 'Save');
	document.postform.appendChild(saveElement);
	document.postform.submit();
}

