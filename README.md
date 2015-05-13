ImageTTFText.php Copyright 2015

PHP Класс для нанесения текста на изображение

##Example

```php
$img = new ImageTTFText('test.jpg');

$img->font('times')
	->size(25)
	->align('right')
	->color('#313141');
	
$img->text(10, 50, 'Надпись');

$img->save('/output.jpg');
$img->render('output');
```