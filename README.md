ImageTTFText.php Copyright 2015

PHP Класс для нанесения текста на изображение

##Example

```php
$img = new ImageTTFText('test.jpg');

$img->font('times')
	->size(25)
	->align('right')
	->color('#313141');
	
###OR

$img->set(array(
	'font' => 'georgia_bi',				// Файл шрифта
    'fontK' => 1,						// Коэффициент для размера шрифта
    'size' => 14,						// Размер шрифта
    'color' => '#313141',				// Цвет
    'align' => 'right',					// Выравнивание
    'leading' => false,					// Интерлиньяж в пикселях
    'def_leading' => 1.6,				// Интерлиньяж по умолчанию от размера шрифта
));
	
$img->text(10, 50, 'Надпись');

$img->save('output.jpg');
$img->render('output');
```