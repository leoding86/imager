<?php

namespace Leoding86\Imager;

use InvalidArgumentException;
use OutOfBoundsException;
use BadMethodCallException;

class Text
{
  private $text;
  private $font;
  private $pen;
  private $color;
  private $size;
  private $angle;
  private $box;
  private $bbox;

  public function __construct($text, $size, $angle, Color $color, $font)
  {
    $this->setText($text)
         ->setSize($size)
         ->setAngle($angle)
         ->setColor($color)
         ->setFont($font);

    $this->getTextBoundingBox();
  }

  public function __toString()
  {
    return $this->text;
  }

  public function __get($property)
  {
    if (property_exists($this, $property)) {
      return $this->$property;
    } else {
      throw new RuntimeException('Unkown property {Color::' . $property . '}');
    }
  }

  public function __set($property, $value)
  {
    if (property_exists($this, $property)) {
      $setter = 'set' . ucfirst($property);
      if (method_exists($this, $setter)) {
        call_user_func(array($this, $setter), $value);
        return $this;
      }
    }

    throw new BadMethodCallException('Unkown setter {Color::' . $setter . '}');
  }

  public function getTextBoundingBox()
  {
    $angle = $this->angle % 180;

    $result = call_user_func($this->bbox, $this->size, $angle, $this->font, $this->text);

    $this->box = array();
    if (
      ($angle >= 0 && $angle < 90) ||
      ($angle > -180 && $angle <= -90)
    ) { // 1,3象限
      $this->box['width'] = abs($result[2] - $result[6]);
      $this->box['height'] = abs($result[1] - $result[5]);
    } else { // 2,4象限
      $this->box['width'] = abs($result[0] - $result[4]);
      $this->box['height'] = abs($result[3] - $result[7]);
    }
  }

  public function setSize($size)
  {
    if (is_int($size) || is_float($size)) {
      $this->size = $size;
      return $this;
    } else {
      throw new InvalidArgumentException(get_class($this) . '::size must been float type');
    }
  }

  public function setText($text)
  {   
    if (is_string($text) && !empty($text)) {
      $this->text = $text;
      return $this;
    } else {
      throw new InvalidArgumentException(get_class($this) . '::text must been non-empty string');
    }
  }

  public function setFont($font)
  {
    if (is_file($font)) {
      if (preg_match('/\.ttf$/i', $font)) {
        $this->pen = 'imagettftext';
        $this->bbox = 'imagettfbbox';
        $this->font = $font;
      } else if (preg_match('/\.freetype$/i', $font)) {
        $this->pen = 'imagefttext';
        $this->bbox = 'imageftbbox';
        $this->font = $font;
      } else {
        throw new RuntimeException(get_class($this) . '::font is not ttf or freetype');
      }
    } else {
      throw new RuntimeException(get_class($this) . '::font must been a font type file');
    }

    return $this;
  }

  public function setAngle($angle)
  {
    if (is_int($angle) || is_float($angle)) {
      $this->angle = $angle;
      return $this;
    } else {
      throw new InvalidArgumentException(get_class($this) . '::angle must been float type');
    }
  }

  public function setColor(Color $color)
  {
    $this->color = $color;
    return $this;
  }

  public function writeTo(Image $image, $x, $y)
  {
    $image->write($this, $x, $y);
    return $this;
  }
}