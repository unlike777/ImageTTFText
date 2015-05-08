ImageTTFText.php Copyright 2015

PHP Класс для нанесения надписей на изображение

##Example

```php
$img = new ImageTTFText('/test.jpg');

$img->setFont('times')
	->setSize(25)
	->setColor('#313141');
	
$img->text(10, 50, 'Надпись');

$img->save('/output.jpg');
$img->render('output');
```