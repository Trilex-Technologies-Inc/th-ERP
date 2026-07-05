<?php
include('../include/fpdf/fpdf.php');

define('MARGIN', 5);
define('WIDTH', 205);
define('HEIGHT', 290);
define('ROWHEIGHT', 6);

class Cell
{
	var $w;
	var $h;
	var $txt;
	var $border;
	var $ln;
	var $align;
	var $fill;
	var $link;
	var $family;
	var $style;
	var $size;
}

class MyPDF extends FPDF
{
	var $x1;
	var $y1 = 0;
	var $keepTogether = 0;
	var $cells = array();
	var $family;
	var $style;
	var $size;

	function setFont($family, $style, $size)
	{
		$this->family = $family;
		$this->style = $style;
		$this->size = $size;
		parent::setFont($family, $style, $size);
	}

	function KeepTogether_begin()
	{
		$this->keepTogether = 1;
	}

	function KeepTogether_end()
	{
		if ($this->keepTogether == 0)
			return;
		$this->keepTogether = 0;
		if ($this->y + $this->y1 > $this->PageBreakTrigger) {
			$this->AddPage();
			//parent::Cell(100, ROWHEIGHT, 'New page', 0,1);
		}
		$this->keepTogether = 0;
		foreach ($this->cells as $cell) {
			$this->setFont($cell->family, $cell->style, $cell->size);
			$this->Cell($cell->w, $cell->h, $cell->txt, $cell->border,
			            $cell->ln, $cell->align, $cell->fill, $cell->link);
			$this->setFont($this->family, $this->style, $this->size);
		}
		$this->cells = array();
		$this->y1 = 0;
	}

	function Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='')
	{
		if (!$this->keepTogether) {
			parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
		} else {
			$cell = new Cell();
			$cell->w = $w;
			$cell->h = $h;
			$cell->txt = $txt;
			$cell->border = $border;
			$cell->ln = $ln;
			$cell->align = $align;
			$cell->fill = $fill;
			$cell->link = $link;
			$cell->family = $this->family;
			$cell->style = $this->style;
			$cell->size = $this->size;
			$this->cells[] = $cell;
			if($ln>0)
			{
				$this->y1+=$h;
			}
		}
	}
}

?>
