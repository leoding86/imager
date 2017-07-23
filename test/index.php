<?php

include('../src/Image.php');
include('../src/Color.php');
include('../src/Text.php');

use Leoding86\Imager as Imager;

$black = new Imager\Color(0, 0, 0); // Create black color from rbg
$red = new Imager\Color(255, 0, 0); // Create red color from rgb
$transparent_black = new Imager\Color('#66000000'); // Create transparent black color from hex
$bg = new Imager\Image('./images/bg.png'); // Create a image
$pic = new Imager\Image('./images/bg.png'); // Create another image
$pic_better_crop = new Imager\Image('./images/bg.png'); // Create 3rd image
$pic_better_crop->setBetterCrop(true); // Set 3rd image crop quanlity to better
$text_str = 'This is a wonderfull day !'; 
$text = new Imager\Text($text_str . ' bbox align left', 20, 0, $red, 'font.ttf'); // Create a text
$text_shadow = clone $text; // Clone a text
$text_shadow->color = $transparent_black; // Set text color

$pic->crop(300, 300); // Crop image

$bg->write($text_shadow, 200, 200); // Write text on image as text shadow
$bg->write($text, 198, 198);  // Write text on image

$text->setText($text_str . ' bbox align right')->getTextBoundingBox(); // Set new text string and re-calculate text bounding box size
$bg->write($text, 198, 230, Imager\Image::BBOX_ALIGN_RIGHT); // Write text on image with right align

$text->setColor($black)->setText($text_str . ' bbox align center')->getTextBoundingBox();
$bg->write($text, 198, 260, Imager\Image::BBOX_ALIGN_CENTER);

$bg->append($pic, 200, 300); // Add a image

$bg->append($pic_better_crop->crop(300, 300), 600, 300);

$bg->append($pic_better_crop->crop(100, 100)->border(5, 5, 10, 10, $red), 1000, 300); // Crop image and border it

$bg->save(dirname(__FILE__) . '\\images\\saved_image.jpg'); // save image