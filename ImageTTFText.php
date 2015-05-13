<?php

class ImageTTFText
{
	private $quality = 85;  					// Качество jpg по-умолчанияю
	private $fontDir   = '/css_js/fonts';  		// Каталог шрифтов
	private $src = false;						// Исходное изображение
	
	
	private $font = 'georgia_bi';				// Файл шрифта
	private $font_k = 1;						// Коэффициент для размера шрифта
	private $size = 14;							// Размер шрифта
	private $color = '#000000';					// Цвет
	private $align = 'left';					// Выравнивание
	private $leading = false;					// Интерлиньяж в пикселях
	private $def_leading = 1.6;					// Интерлиньяж по умолчанию от размера шрифта
	
	private static function root() {
		return getcwd();
	}
	
	public function __construct($path) {
		$path = self::root().$path;
		
		if (file_exists($path)) {
			$type = exif_imagetype($path);
			
			switch ($type)
			{
				case IMAGETYPE_GIF:   $this->src = @imagecreatefromgif($path);   break;
				case IMAGETYPE_JPEG:  $this->src = @imagecreatefromjpeg($path);  break;
				case IMAGETYPE_PNG:   $this->src = @imagecreatefrompng($path);   break;
			}
		}
		
	}
	
	public function __destruct() {
		if ($this->src) imagedestroy($this->src);
	}
	
	public function size($size) {
//		if (is_int($size)) {
		$this->size = $size;
//		}
		
		return $this;
	}
	
	public function align($align) {
		$arr = array('center', 'left', 'right');
		
		$this->align = 'left';
		
		if (in_array($align, $arr)) {
			$this->align = $align;
		}
		
		return $this;
	}
	
	public function font($name) {
		$this->font = false;
		if (file_exists(self::root().$this->fontDir.'/'.$name.'.ttf')) {
			$this->font = $name.'.ttf';
		}
		
		return $this;
	}
	
	/**
	 * Устанавливает коэффициент для размера шрифта
	 * @param $k
	 * @return $this
	 */
	public function fontK($k) {
		$this->font_k = $k;
		return $this;
	}
	
	/**
	 * @param $color
	 * @param int $alpha - от 0 до 127
	 */
	public function color($color, $alpha = 0) {
		if ($alpha < 0) $alpha = 0;
		if ($alpha > 127) $alpha = 127;
		
		$this->color = false;
		
		if ($this->src) {
			list($r, $g, $b) = array_map('hexdec', str_split(ltrim($color, '#'), 2));
			
			if ($alpha > 0) {
				$this->color = imagecolorallocatealpha($this->src, $r+1, $g+1, $b+1, $alpha);
			} else {
				$this->color = imagecolorallocate($this->src, $r+1, $g+1, $b+1);
			}
			
		}
		
		return $this;
	}
	
	
	/**
	 * Устанавливает интерлиньяж, если false то будет рассчитывать от текущего размера шрифта
	 * @param $leading
	 * @return $this
	 */
	public function leading($leading) {
		$this->leading = $leading;
		return $this;
	}
	
	
	public function text($x, $y, $text, $angle = 0) {
		
		if ($this->font && $this->src && $this->color) {
			
			$text = htmlspecialchars_decode($text);
			$text = str_replace(array('<br>', '<br/>', '<br />'), "\n", $text);
			
			$data = explode("\n", $text);
			$font_size = $this->size*$this->font_k;
			
			foreach ($data as $item) {
				
				$shift = 0;
				
				if ($this->align == 'center') {
					$sizes = imagettfbbox($font_size, $angle, self::root().$this->fontDir.'/'.$this->font, $item);
					$width = $sizes[2] - $sizes[0];
					$shift = $width/2;
				} else if ($this->align == 'right'){
					$sizes = imagettfbbox($font_size, $angle, self::root().$this->fontDir.'/'.$this->font, $item);
					$width = $sizes[2] - $sizes[0];
					$shift = $width;
				}
				
				imagettftext(
					$this->src,
					$font_size,
					$angle,
					$x - $shift,
					$y + $font_size,
					$this->color,
					self::root().$this->fontDir.'/'.$this->font,
					$item
				);
				
				if ($this->leading) {
					$y += $this->leading;
				} else {
					$y += $font_size*$this->def_leading;
				}
				
				
			}
			
		}
		
		return $this;
	}
	
	
	/**
	 * Сохраняет изображение в файл
	 * @param $target
	 * @param bool $replace
	 * @return bool
	 */
	
	public function save($target, $replace = true) {
		$target = self::root().$target;
		
		if (file_exists($target) && !$replace) return false;
		
		$path_info = pathinfo($target);
		$ext = strtolower($path_info['extension']);
		
		switch ($ext)
		{
			case "gif":
				imagegif ($this->src, $target);
				break;
			
			case "jpg" :
			case "jpeg":
				imagejpeg($this->src, $target, $this->quality);
				break;
			
			case "png":
				imagepng($this->src, $target);
				break;
			
			default: return false;
		}
		return true;
	}
	
	
	public function render($filename) {
		header('Content-Type: image/png');
		header('Content-Disposition: attachment; filename=' . $filename.'.png');
		
		imagepng($this->src);
		
		imagedestroy($this->src);
	}
}

?>