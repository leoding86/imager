<?php

namespace Leoding86\Imager;

use InvalidArgumentException;
use OutOfBoundsException;
use BadMethodCallException;

/**
 * This class is for creating text object for adding to image
 * 
 * @property string   $text
 * @property string   $font  Local font file
 * @property callable $pen   A function or method for adding text to image
 * @property Color    $color A color object
 * @property mixed    $size  Font size
 * @property mixed    $angle Text angle
 * @property array    $box   Text box, include width and height
 * @property callable $bbox  A function or method for get text bounding box
 * 
 * @author Leo Ding <leoding86@msn.com>
 */
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

  /**
   * Construct a text
   *
   * @param string $text
   * @param mixed  $size
   * @param mixed  $angle
   * @param Color  $color
   * @param string $font
   */
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
      trigger_error('Unkown property {Color::' . $property . '}', E_USER_ERROR);
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

    trigger_error('Unkown setter {Color::' . $setter . '}', E_USER_ERROR);
  }

  /**
   * The alias of calculateTextBoxSize for compatible old version
   *
   * @return void
   */
  public function getTextBoundingBox()
  {
    $this->calculateTextBoxSize();
  }

  /**
   * Calculate text box size
   * 
   * Remember to call this method after reset text, size, angle, font to get new text box size
   *
   * @return void
   */
  public function calculateTextBoxSize()
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

  /**
   * Setter of property size
   *
   * @param mixed $size
   * @return void
   */
  public function setSize($size)
  {
    if (is_int($size) || is_float($size)) {
      $this->size = $size;
      return $this;
    } else {
      throw new InvalidArgumentException(get_class($this) . '::size must been float type');
    }
  }

  /**
   * Setter of property text
   *
   * @param mixed $text
   * @return void
   */
  public function setText($text)
  {   
    if (is_string($text) && !empty($text)) {
      $this->text = $text;
      return $this;
    } else {
      throw new InvalidArgumentException(get_class($this) . '::text must been non-empty string');
    }
  }

  /**
   * Setter of property font
   *
   * @param  string $font
   * @return void
   */
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

  /**
   * Setter of propery angle
   *
   * @param  mixed $angle
   * @return void
   */
  public function setAngle($angle)
  {
    if (is_int($angle) || is_float($angle)) {
      $this->angle = $angle;
      return $this;
    } else {
      throw new InvalidArgumentException(get_class($this) . '::angle must been float type');
    }
  }

  /**
   * Setter of propery color
   *
   * @param  Color $color
   * @return void
   */
  public function setColor(Color $color)
  {
    $this->color = $color;
    return $this;
  }

  /**
   * Append current text to a image
   *
   * @param Image $image
   * @param int   $x
   * @param int   $y
   * @return void
   */
  public function writeTo(Image $image, $x, $y)
  {
    $image->write($this, $x, $y);
    return $this;
  }
}