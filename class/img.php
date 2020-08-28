<?php
/**
 * Class img
 * 
 * Permet de faire des traitements sur les images.
 * 
 * Exemple d'utilisation : 		
 * 		if($_POST['pic']){
 *			$myImg = new img('upload/'.$_GET['pic']);
 *			$myImg->watermark('upload/copy.png');
 *			$myImg->resize(100);
 *			$myImg->show();
 *			$myImg->store('upload/'.$_GET['pic'])
 * 		}
 * Auteur : Christophe THIBAULT
 * Version : 1.1
 */

class img {
	
	//declaration variable private
	private $image = '';
	private $temp = '';
	private $type = '';
	
	
	/* ----------------- CONSTRUCTOR ----------------- */
	public function img($sourceFile){
		$this->type = 'JPEG';
		if(file_exists($sourceFile)){
			$this->image = @imagecreatefromjpeg($sourceFile);
			if(!$this->image){
				$this->image = @imagecreatefrompng($sourceFile);
				$this->type = 'PNG';
				if(!$this->image){
					$this->image = @imagecreatefromgif($sourceFile);
					$this->type = 'GIF';
					if(!$this->image){
						$this->errorHandler();
					}
				}
			}
		} else {
			$this->errorHandler();
		}
		return;
	}
	
	/* ----------------- METHODES ----------------- */
	public function resize($width = 100, $height = 100, $aspectratio = true){
		$o_wd = imagesx($this->image);
		$o_ht = imagesy($this->image);
		if ($o_wd > $width){
			if(isset($aspectratio)&&$aspectratio) {
				$w = round($o_wd * $height / $o_ht);
				$h = round($o_ht * $width / $o_wd);
				if(($height-$h)<($width-$w)){
					$width =& $w;
				} else {
					$height =& $h;
				}
			}
			$this->temp = imageCreateTrueColor($width,$height);
			imageCopyResampled($this->temp, $this->image, 0, 0, 0, 0, $width, $height, $o_wd, $o_ht);
			$this->sync();
		}
		return;
	}

	public function resizeByMax($length = 65){
		$o_wd = imagesx($this->image);
		$o_ht = imagesy($this->image);
		
		if($o_wd > $o_ht){
			// Image horizontale
			$new_ht = $length;
			$new_wd = ($new_ht * $o_wd) / $o_ht;
		}else{
			// Image verticale
			$new_wd = $length;
			$new_ht = ($new_wd * $o_ht) / $o_wd;		
		}
		
		$this->temp = imageCreateTrueColor($new_wd,$new_ht);
		imageCopyResampled($this->temp, $this->image, 0, 0, 0, 0, $new_wd, $new_ht, $o_wd, $o_ht);
		$this->sync();
	}
	
	public function cropSquare(){
		$o_wd = imagesx($this->image);
		$o_ht = imagesy($this->image);
		if($o_wd>$o_ht){
			$offset = round(($o_wd - $o_ht) / 2);
			$this->temp = imageCreateTrueColor($o_ht,$o_ht);
			imageCopyResampled($this->temp, $this->image, 0, 0, $offset, 0, $o_ht, $o_ht, $o_ht, $o_ht);	
		}else{
			$offset = round(($o_ht - $o_wd) / 2);
			$this->temp = imageCreateTrueColor($o_wd,$o_wd);
			imageCopyResampled($this->temp, $this->image, 0, 0, 0, $offset, $o_wd, $o_wd, $o_wd, $o_wd);
		}
		$this->sync();
	}
	
	public function sync(){
		$this->image =& $this->temp;
		unset($this->temp);
		$this->temp = '';
		return;
	}
	
	public function show(){
		$this->_sendHeader();
		ImageJPEG($this->image);
		return;
	}
	
	public function _sendHeader(){
		header('Content-Type: image/jpeg');
	}
	
	public function errorHandler(){
		echo "error";
		exit();
	}
	
	public function store($file){
		if ($this->type == 'JPEG')
			ImageJPEG($this->image,$file);
		if ($this->type == 'PNG')
			ImagePNG($this->image,$file);
		if ($this->type == 'GIF')
			ImageGIF($this->image,$file);
		return;
	}
	
	public function watermark($pngImage, $left = 0, $top = 0){
		ImageAlphaBlending($this->image, true);
		$layer = ImageCreateFromPNG($pngImage); 
		$logoW = ImageSX($layer); 
		$logoH = ImageSY($layer); 
		ImageCopy($this->image, $layer, $left, $top, 0, 0, $logoW, $logoH); 
	}
	
	public function getSizeX(){
		return imagesx($this->image);
	}
	
	public function getSizeY(){
		return imagesy($this->image);
	}
}
?>
