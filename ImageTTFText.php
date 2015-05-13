<?php

class ImageTTFText
{
	private $quality = 85;  					// Качество jpg по-умолчанияю
	private $fontDir   = '/css_js/fonts';  		// Каталог шрифтов
	private $src = false;						// Исходное изображение
	
	//текущие настройки для нанесения текста
	private $props = array(
		'font' => 'georgia_bi',				// Файл шрифта
		'fontK' => 1,						// Коэффициент для размера шрифта
		'size' => 14,						// Размер шрифта
		'color' => '#000000',				// Цвет
		'align' => 'left',					// Выравнивание
		'leading' => false,					// Интерлиньяж в пикселях
		'def_leading' => 1.6,				// Интерлиньяж по умолчанию от размера шрифта
		'box' => 0							// Ширина бокса, в который нужно разместить текст
	);
	
	//вернет путь до корня сайта
	private static function root() {
		return getcwd();
	}
	
	//вернет путь до файла шрифта
	private function fontPath() {
		return self::root().$this->fontDir.'/'.$this->font;
	}
	
	public function __isset($name) {
		return isset($this->props) ? true : false;
	}
	
	public function __get($name) {
		
		if (isset($this->props[$name])) {
			return $this->props[$name];
		}
		
		return NULL;
	}
	
	public function __set($name, $value) {
		if (isset($this->props[$name])) {
			
			if ($name == 'align') {
				$arr = array('center', 'left', 'right');
				if (in_array($value, $arr)) {
					$this->props[$name] = $value;
				}
			} else if ($name == 'color') {
				$this->color($value);
			} else if ($name == 'font') {
				if (file_exists(self::root().$this->fontDir.'/'.$value.'.ttf')) {
					$this->props[$name] = $value.'.ttf';
				}
			} else {
				$this->props[$name] = $value;
			}
			
		}
	}
	
	public function __construct($target) {
		
		$target = trim($target);
		
		if (empty($target)) {
			return false;
		}
		
		if (substr($target, 0, 1) == '/') {
			$target = substr($target, 1);
		}
		
		$target = self::root().'/'.$target;
		
		if (file_exists($target)) {
			$type = exif_imagetype($target);
			
			switch ($type)
			{
				case IMAGETYPE_GIF:   $this->src = @imagecreatefromgif($target);   break;
				case IMAGETYPE_JPEG:  $this->src = @imagecreatefromjpeg($target);  break;
				case IMAGETYPE_PNG:   $this->src = @imagecreatefrompng($target);   break;
			}
		}
		
	}
	
	public function __destruct() {
		if ($this->src) imagedestroy($this->src);
	}
	
	/**
	 * Массовое присваивание параметров через массив
	 * @param array $arr
	 * @return $this
	 */
	public function set(array $arr) {
		if (is_array($arr)) {
			foreach ($arr as $key => $val) {
				if (isset($this->props[$key])) {
					$this->{$key} = $val;
				}
			}
		}
		
		return $this;
	}
	
	public function size($val) 		{ $this->{__FUNCTION__} = $val; return $this; }
	public function align($val) 	{ $this->{__FUNCTION__} = $val; return $this; }
	public function fontK($val) 	{ $this->{__FUNCTION__} = $val; return $this; }
	public function leading($val) 	{ $this->{__FUNCTION__} = $val; return $this; }
	public function font($val) 		{ $this->{__FUNCTION__} = $val; return $this; }
	public function box($val) 		{ $this->{__FUNCTION__} = $val; return $this; }
	
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
	
	
	public function text($x, $y, $text, $angle = 0) {
		if ($this->font && $this->src && $this->color) {
			
			$text = htmlspecialchars_decode($text);
			$text = str_replace(array('<br>', '<br/>', '<br />'), "\n", $text);
			
			$data = $data_tmp = explode("\n", $text);
			$font_size = $this->size*$this->fontK;
			
			//проверка на выход строки за пределы бокса
			if (!empty($this->box)) {
				$data = array();
				
				//пробегаем по строкам
				foreach ($data_tmp as $line) {
					$words = explode(' ', $line);
					$width = 0;
					$str = '';
					
					//пробегаем по словам
					foreach ($words as $key => $word) {
						
						if ($width > $this->box) {
							$width = 0;
							$data[] = trim($str);
							$str = '';
						}
						
						$sizes = imagettfbbox($font_size, $angle, $this->fontPath(), $word);
						$width += ($sizes[2] - $sizes[0]);
						$str .= $word.' ';
					}
					
					$data[] = trim($str);
				}
			}
			
			//пробегаем по строкам
			foreach ($data as $item) {
				
				$shift = 0;
				
				$sizes = imagettfbbox($font_size, $angle, $this->fontPath(), $item);
				$width = $sizes[2] - $sizes[0];
				
				if ($this->align == 'center') {
					$shift = $width/2;
				} else if ($this->align == 'right'){
					$shift = $width;
				}
				
				imagettftext(
					$this->src,
					$font_size,
					$angle,
					$x - $shift,
					$y + $font_size,
					$this->color,
					$this->fontPath(),
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
		
		$target = trim($target);
		
		if (empty($target)) {
			return false;
		}
		
		if (substr($target, 0, 1) == '/') {
			$target = substr($target, 1);
		}
		
		$target = self::root().'/'.$target;
		
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