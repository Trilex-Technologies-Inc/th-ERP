function isNumeric(e)
{
	var regex = /\d/;
	return isValue(e, regex);
}

function isTime(e)
{
	var regex = /\d:/;
	return isValue(e, regex);	
}

function isValid(e, regex)
{
	var key = window.event ? e.keyCode : e.which;
	if (key == 8 || key == 0)
		return true;
	var ch = String.fromCharCode(key);
	return regex.test(ch);
}