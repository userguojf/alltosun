<?php
class  Vcode {
	private $width;
	private $height;
	private $codeNum;
	private $disturbColorNum;
	private $checkCode;
	private $image;

	function __construct($width=93, $height=36, $codeNum=4) {
		$this->width=$width;
		$this->height=$height;
		$this->codeNum=$codeNum;
		$number=floor($height*$width/15);
		
		if($number > 240-$codeNum)
			$this->disturbColorNum=240-$codeNum;
		else
			$this->disturbColorNum=$number;

		$this->checkCode=$this->createCheckCode();
	}
	
	function start(){
		$_SESSION["code"]=strtoupper($this->checkCode);
		$_SESSION["start_code_time"] = time();

		$this->outImg();
		return '';
	}

	private function outImg(){
		$this->getCreateImage();
		$this->setDisturbColor();
		$this->outputText();
		$this->outputImage();
	}


	private function getCreateImage(){
		$this->image=imagecreatetruecolor($this->width,$this->height);
		$backColor = imagecolorallocate($this->image, rand(225,255),rand(225,255),rand(225,255));    //±³¾°É«£¨Ëæ»ú£©
		 @imagefill($this->image, 0, 0, $backColor);
	
		$border=imageColorAllocate($this->image, 0, 0, 0);
		imageRectangle($this->image,0,0,$this->width-1,$this->height-1,$border);
	}
	private function createCheckCode(){
	    $ascii = '';
		$code  = "3456789abcdefghijkmnpqrstuvwxyABCDEFGHIJKMNPQRSTUVWXY";
		for($i=0;$i<$this->codeNum;$i++) {
			$char=$code{rand(0,strlen($code)-1)};
			
			$ascii.=$char;
		}
		return $ascii;

	}
	private function setDisturbColor() {
		for($i=0; $i<=$this->disturbColorNum; $i++) {
			$color = imagecolorallocate($this->image, rand(0,255), rand(0,255), rand(0,255));
			imagesetpixel($this->image,rand(1,$this->width-2),rand(1,$this->height-2),$color);
		}

		for($i=0; $i<4; $i++){
			$color=imagecolorallocate($this->image,rand(0,255),rand(0,255),rand(0,255));
			imagearc($this->image,rand(-10,$this->width),rand(-10,$this->height),rand(30,300),rand(20,200),55,44,$color);
		}
	}


	private function outputText() {
	    
		for ($i=0;$i<=$this->codeNum;$i++) {
			$fontcolor = imagecolorallocate($this->image, rand(0,128), rand(0,128), rand(0,128));
			$fontSize = 5;
			$x = floor($this->width/$this->codeNum)*$i+3;
			$y = rand(0,$this->height-imagefontheight($fontSize));
			$co = @$this->checkCode{$i};
			
			//imagettftext($this->image, $fontSize, 0, $x, $y, $fontcolor, $font_file, 'x');
			imagechar($this->image, $fontSize, $x, $y,$co , $fontcolor);
		  }
	}

	private function outputImage(){
		if(imagetypes() & IMG_GIF){
			header("Content-type: image/gif");
			imagegif($this->image);
		}elseif(imagetypes() & IMG_JPG){
			header("Content-type: image/jpeg");
			imagejpeg($this->image, "", 0.5);
		}elseif(imagetypes() & IMG_PNG){
			header("Content-type: image/png");
			imagepng($this->image);
		}elseif(imagetypes() & IMG_WBMP){
			 header("Content-type: image/vnd.wap.wbmp");
			 imagewbmp($this->image);
		}else{
			die("PHP");
		}
	}
	function __destruct(){
		imagedestroy($this->image);
	}
}
