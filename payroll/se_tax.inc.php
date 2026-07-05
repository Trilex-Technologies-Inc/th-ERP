<?php

function getPrelskatt()
{
	$amount = periodSum(taxable);
	$annanHuvudarbetsgivare = attribute(annan_huvudarbetsgivare);
	if ($annanHuvudarbetsgivare) {
		$skatt = (-1) * $amount * 0.3;
		return round($skatt);
	}
	$procent = attribute(skatteprocent);
	if ($procent > 0) {
		$skatt = $amount * $procent/100;
		return round((-1) * $skatt);
	}
	$year = date('Y', time());
	$periodlength = 30;
	$tableno = attribute(skattetabell);
	if ($tableno == 0)
		$tableno = 30;
	$column = attribute(skattekolumn);
	if ($column == 0)
		$column = 1;
	$row = find("select type, tax$column as value
	             from se_taxtable
				 where year=$year and periodlength=$periodlength and tableno=$tableno
				 and floor <= $amount and ceiling >= $amount");
	if ($row->type == 'B') {
		return round((-1) * $row->value);
	}
	if ($row->type == '%') {
		return round((-1) * $row->value * $amount);
	}
}

function getArbetsgivaravgiftProcent()
{
	$birthDate = getBirthDate();
	$birthYear = substr($birthDate, 0, 4);
	$year = getYear(time());
	$age = $year - $birthYear;
	if ($age >= 19 && $age <= 25) {
		return 22.71;
	}
	$age = getAge($birthDate);
	if ($age >= 66) {
		if ($birthYear <= 1937)
			return 24.26;
		return 10.21;
	}
	return 32.42;
}

?>
