<?php
/**
 * AVE.cms
 * autor: Repellent
 * @package AVE.cms
 * version 1.0
 * 2016
 * @filesource
 */
ob_start();
ob_implicit_flush(0);

define('BASE_DIR', str_replace("\\", "/", dirname(dirname(dirname(__FILE__)))));

require_once(BASE_DIR . '/inc/init.php');

if (! check_permission('adminpanel'))
{
	header('Location:/index.php');
	exit;
}

class TextImage
{
	
	// если true, возвращает теги IMG, в которых изображение вкладывается как base64
	public $return_as_html = true;
	public $embed_image = false;
	public $do_cache = true;
	public $cache_folder = '../../uploads/txtimages';// создаем папку , куда будем складывать созданные изображения
	
	function __construct()
	{
		if($this->do_cache === true){
			if(!is_dir($this->cache_folder)){
				if(!mkdir($this->cache_folder, 0755)){
					$this->do_cache = false;
				}
				if(!is_writable($this->cache_folder)){
					$this->do_cache = false;
				}
			}
			else {
				if(!is_writable($this->cache_folder)){
					$this->do_cache = false;
					if(!chmod($this->cache_folder, 0755)){
						$this->do_cache = false;
					}
					else {
						if(is_writable($this->cache_folder)){
							$this->do_cache = true;
						}
					}
				}
			}
		}
		else {
			$this->do_cache = false;
		}
	}
	
	
	private function cache($text, $font, $size, $color, $trans = 0, $shadow = false, $shadow_off = false)
	{
		if($this->do_cache){
			$hash = md5($text . $font . $size . $color . $trans . $shadow . $shadow_off);
			if(file_exists($this->cache_folder . '/' . $hash . '.png')){
				$image = file_get_contents($this->cache_folder . '/' . $hash . '.png');
				$text = file_get_contents($this->cache_folder . '/' . $hash . '.txt');
				if($this->return_as_html !== true){
					return $image;
				}
				else {
					if ($this->embed_image) {
						return '<img alt="'.$text.'" class="textImage" src="data:image/png;base64,'.base64_encode($image).'" />';
					}
					else {
						return '<img alt="'.$text.'" class="textImage" src="/'.$this->cache_folder . '/' . $hash . '.png'.'" />';
					}
				}
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	
	public function generate($text, $font, $size, $color, $display_alt = 1, $trans = 0, $shadow = false, $shadow_off = false)
	{
		
		$cache = $this->cache($text, $font, $size, $color, $trans, $shadow, $shadow_off);
		if($cache !== false){ return $cache; }
		if ($display_alt == 1){
		$text = ( !empty($text) ? trim(urldecode($text)) : "Заполните все поля!" );
		$textWeb = str_replace(array("\n", "\r\n", "\r"), " ", $text);
		}
		else {
		$text = ( !empty($text) ? trim(urldecode($text)) : "Нет сопоставлений" );
		$textWeb = str_replace(array("\n", "\r\n", "\r"), " ", '');	
		}
		if(file_exists($font)){ $font = $font; }
		elseif(file_exists($font.'.ttf')){ $font = $font.'.ttf'; }
		elseif(file_exists($font.'.otf')){ $font = $font.'.otf'; }
		elseif(file_exists(BASE_DIR . '/fields/text_to_image/fonts/'.$font.'')){ $font = BASE_DIR . '/fields/text_to_image/fonts/'.$font.''; }
		elseif(file_exists(BASE_DIR . '/fields/text_to_image/fonts/'.$font.'.ttf')){ $font = BASE_DIR . '/fields/text_to_image/fonts/'.$font.'.ttf'; }
		elseif(file_exists(BASE_DIR . '/fields/text_to_image/fonts/'.$font.'.otf')){ $font = BASE_DIR . '/fields/text_to_image/fonts/'.$font.'.otf'; }
		else {
		$error_image = "iVBORw0KGgoAAAANSUhEUgAAAI0AAAANCAYAAACU7u19AAAEB0lEQVRYhe2YT2jcRR";
        $error_image.= "THP7MsEkppaYzFxjStpIdQBVu81NsDe+il4MFrVUSlerClYlOqJRSpMZUGVFqLPVQt";
        $error_image.= "VA+CB5uDCuEdaj14EFEqGMH4b6mkVSyhlBIyHn5v4+z4m9ndbMCU5AvLb2fem/e+78";
        $error_image.= "3Mm9/8YAXLHl7kWDv6FS9yolMjK7jtUWlV0YuMVxIDWjaygmWHSjUn9SJdwBHgL+BO";
        $error_image.= "4KhTvRnI33Cq+8raXuQs8C3wgFN93Is8BvQA9wDjTnXCi5wzHYD7TO8OYNh8rjOft7";
        $error_image.= "zIQYrFPAu861SvepFRpzqU4b8XWA1sAD52qhczPL62YT841QvGf9L8rbM8nIjjBb4v";
        $error_image.= "8THPy4tsBd5yqg97kUeBfmA38AlQc6ofNovD7Iw61SEvcsypvlR/Brlu4OpUZzM8qs";
        $error_image.= "ArlmOAbV7kEDAXxPpIGVeA7KIBngNOOtWaF+mz9lggn/QiW5zqj15kPfBHILviVMe8";
        $error_image.= "yAiAU30/SvaE6Ry3vhETPwOccapTXmQz8JQXuQ5cdKqXIn7ZiuhUTwc+R8xGisdYNH";
        $error_image.= "zGqb5qeluN13/iLfMR8XoBuGx8PjK9DZG/Vip7JfFMcT2V4mHyc071so0ZcKqvheOd";
        $error_image.= "6qkEVypArxc5GP6APpNvdKo1C/g3it0Z4gKwy/7vAj4zR9UmibiRkW1yqlPmcwrYCG";
        $error_image.= "wvWTBE3J9MGfQiPcB0mzzmK6oldxOJeFM+vMhO4AtgJuOn5Tja5JrjMVBfMIY/c+Nj";
        $error_image.= "VCnKzvHISX3X3+1Fng9E10I9qwb3WvPBYBdv499jBy+yGthLUf5mgd4cqRLMJfprQa";
        $error_image.= "W634scCHeFF1lDUWq7gac75DFXFm/sIxqzx47cEfLIxrEAxPmKeaTymRrfgGbHU82p";
        $error_image.= "vtlEZ8aLrIr6dgNvB+1hYNSpXoWGRdkqmpZvp/qdF9kT9V0HXvQi/RQleaADHnUODf";
        $error_image.= "GW+Dhttp8A3mvDfjKOBWA+Xwvkkcv3XLPJmPQig010PgeGgK+Cvn6neiVod9UnylCN";
        $error_image.= "niF+tXcZbCJ+B77xIjtyJGwiZ8tkTvUXipKb4lGGrsD2IPCzNcviDX0ArAIecqoTOc";
        $error_image.= "5lyMWxAK4pHj9F89qdGF+GfVXKS1G97x3gZS9SPzPP27tNiEvAB8B2c7ofWO9FDpi8";
        $error_image.= "Dzhpu3qaYqK22LegLxM+j3qRaYob27Ddng55EaFI6Hl71+oNblVrgflvTnYL22927w";
        $error_image.= "LGgVsRj57Id4g1XuQwjbenhngTPgAGgWdLYku1k3GU6OyI3jtzXFM86jn+29qbg9vT";
        $error_image.= "WoqTIcX1CIsBL3Im+P/6ohj9n5GLI4x3KaDTnLf7utDxRzwv0g3Ugq6zndpcyiiJd9";
        $error_image.= "lhMb787gQ+rTeiq9ztjNQNoiHeJYJmt6FFHf8PmykzDVQO9iwAAAAASUVORK5CYII=";
			
			if($this->return_as_html !== true){
				return base64_decode($error_image);
			}
			else {
				return '<img alt="Ошибка при загрузке шрифта!" class="textImage" src="data:image/png;base64,'.$error_image.'" />';
			}
		}
		
		$sizes = $this->getfontbox($size, 0, $font, $text);
		$colors = $this->color($color);
		
		if ($shadow !== false) {
			if (is_numeric($shadow) && $shadow <= 4) {
				if ($shadow_off != false) {
					$i_base = $shadow_off;
				}
				$im = imagecreatetruecolor($sizes['width']+$shadow+$i_base+1, $sizes['height']+$shadow+$i_base+1);
			}
			else {
				$im = imagecreatetruecolor($sizes['width'], $sizes['height']);
			}
		}
		else {
			$im = imagecreatetruecolor($sizes['width'], $sizes['height']);
		}
		imagesavealpha($im, true);
		
		if (is_numeric($trans) && $trans <= 127 && $trans >= 0) {
			
		}
		
		
		$color = imagecolorallocatealpha($im, $colors[0], $colors[1], $colors[2], $trans);
		
		$trans_color = imagecolorallocatealpha($im, 0, 0, 0, 127);
		imagefill($im, 0, 0, $trans_color);
		
		if ($shadow !== false && $shadow <= 4) {
			if ($shadow_off != false) {
				$i_base = $shadow_off;
			}
			else {
				$i_base = 0;
			}
			for ($i=$i_base,$o=64; $i < $shadow+$i_base; $i++,$o+=16) {
				$shadow_color = imagecolorallocatealpha($im, 100, 100, 100, $o);
				imagettftext($im, $size, 0, $sizes['left']+$i+1, $sizes['top']+$i+1, $shadow_color, $font, $text);
			}
		}
		
		imagettftext($im, $size, 0, $sizes['left'], $sizes['top'], $color, $font, $text);
		
		ob_start();
		imagepng($im);
		$image = ob_get_clean(); 
		imagedestroy($im);
		
		if($this->do_cache){
			$hash = md5($text . $font . $size . $color . $shadow);
			file_put_contents($this->cache_folder . '/' . $hash . '.png', $image);
			file_put_contents($this->cache_folder . '/' . $hash . '.txt', $textWeb);
		}
		
		if($this->return_as_html !== true){
			return $image;
		}
		else {
			if ($this->embed_image == true) {
				return '<img alt="'.$textWeb.'" class="textImage" src="data:image/png;base64,'.base64_encode($image).'" />';
			}
			else {
				if ($this->do_cache == false) {
					return '<img alt="'.$textWeb.'" class="textImage" src="data:image/png;base64,'.base64_encode($image).'" />';
				}
				else {
					return '<img alt="'.$textWeb.'" class="textImage" src="/'.$this->cache_folder . '/' . $hash . '.png'.'" />';
				}
			}
		}
	}
	
	private function getfontbox($font_size, $font_angle, $font_file, $text)
	{
		$box   = imagettfbbox($font_size, $font_angle, $font_file, $text);
		if( !$box ) return false;
		$min_x = min( array($box[0], $box[2], $box[4], $box[6]) );
		$max_x = max( array($box[0], $box[2], $box[4], $box[6]) );
		$min_y = min( array($box[1], $box[3], $box[5], $box[7]) );
		$max_y = max( array($box[1], $box[3], $box[5], $box[7]) );
		$width  = ( $max_x - $min_x );
		$height = ( $max_y - $min_y );
		$left   = abs( $min_x ) + $width;
		$top    = abs( $min_y ) + $height;
		$img     = @imagecreatetruecolor( $width << 2, $height << 2 );
		$white   =  imagecolorallocate( $img, 255, 255, 255 );
		$black   =  imagecolorallocate( $img, 0, 0, 0 );
		imagefilledrectangle($img, 0, 0, imagesx($img), imagesy($img), $black);
		imagettftext( $img, $font_size,
		              $font_angle, $left, $top,
		              $white, $font_file, $text);
		$rleft  = $w4 = $width<<2;
		$rright = 0;
		$rbottom   = 0;
		$rtop = $h4 = $height<<2;
		for( $x = 0; $x < $w4; $x++ )
		  for( $y = 0; $y < $h4; $y++ )
		    if( imagecolorat( $img, $x, $y ) ){
		      $rleft   = min( $rleft, $x );
		      $rright  = max( $rright, $x );
		      $rtop    = min( $rtop, $y );
		      $rbottom = max( $rbottom, $y );
		    }
		imagedestroy( $img );
		return array( "left"   => $left - $rleft,
		              "top"    => $top  - $rtop,
		              "width"  => $rright - $rleft + 1,
		              "height" => $rbottom - $rtop + 1 );
	}
	
	private function color($color)
	{
		if($color[0].$color[1].$color[2] == "rgb"){
			preg_match("/^rgb\((\d*),\s(\d*),\s(\d*)\)$/", $color, $colors);
			return array($colors[1], $colors[2], $colors[3]);
		}
		else {
			if ($color[0] == '#')
		        $color = substr($color, 1);

		    if (strlen($color) == 6)
		        list($r, $g, $b) = array($color[0].$color[1],
		                                 $color[2].$color[3],
		                                 $color[4].$color[5]);
		    elseif (strlen($color) == 3)
		        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		    else
		        return false;

		    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

		    return array($r, $g, $b);
		}
	}
	
}
?>