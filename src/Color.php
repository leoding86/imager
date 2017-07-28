<?php

namespace Leoding86\Imager;

use OutOfBoundsException;
use RuntimeException;

/**
 * This class is for creating a color for fill image or color text
 * 
 * @property mixed $red
 * @property mixed $green
 * @property mixed $blue
 * @property mixed $alpha
 * @author Leo Ding <leoding86@msn.com>
 */
class Color
{
  private $red;
  private $green;
  private $blue;
  private $alpha;

  /**
   * Constrcut a color object, support hex or rgb or rgba
   *
   * @param mixed $red   red channel or hex
   * @param mixed $green green channel
   * @param mixed $blue  blue channel
   * @param mixed $alpha alpha
   */
  public function __construct($red, $green = null, $blue = null, $alpha = null)
  {
    if (
      preg_match('/^#[0-9a-f]{6}$/i', $red) ||
      preg_match('/^#[0-9a-f]{8}$/i', $red)
    ) { // hex
      $input = $red;
      $hex_data = str_replace('#', '', strtoupper($input));

      if (strlen($hex_data) === 6) {
        list($red, $green, $blue) = sscanf($hex_data, '%02x%02x%02x');
      } else if (strlen($hex_data) === 8) {
        list($alpha, $red, $green, $blue) = sscanf($hex_data, '%02x%02x%02x%02x');
      }
    }

    if (
      !$this->verifyChannel($red) ||
      !$this->verifyChannel($green) ||
      !$this->verifyChannel($blue) ||
      !$this->verifyAlpha($alpha)
    ) {
      throw new OutOfBoundsException('Unsupport color value');
    }

    $this->red = $red;
    $this->green = $green;
    $this->blue = $blue;
    $this->alpha = floor(127 * $alpha / 255);
  }

  /**
   * Get property
   *
   * @param  string $name
   * @return mixed
   */
  public function __get($name)
  {
    if (property_exists($this, $name)) {
      return $this->$name;
    } else {
      trigger_error('Unkown property {Color::' . $name . '}', E_USER_ERROR);
    }
  }

  /**
   * Allocate color for a image
   *
   * @param  Image $image
   * @return int
   */
  public function colorAllocate(Image $image)
  {
    if ($this->alpha !== null) {
      return imagecolorallocatealpha($image->getRes(), $this->red, $this->green, $this->blue, $this->alpha);
    } else {
      return imagecolorallocate($image->getRes(), $this->red, $this->green, $this->blue);
    }
  }

  /**
   * Fill current color to a image
   * after fill image, de-allocate color
   *
   * @param  Image $image
   * @return void
   */
  public function fillImage(Image $image)
  {
    $allocatecolor = $this->colorAllocate($image);
    imagefill($image->getRes(), 0, 0, $allocatecolor);
    imagecolordeallocate($image->getRes(), $allocatecolor);
  }

  /**
   * rgb channel checker
   *
   * @param  mixed $channel channel value
   * @return void
   */
  private function verifyChannel($channel)
  {
    return $channel >=0 && $channel <= 255;
  }

  /**
   * alpha value checker
   *
   * @param  mixed $alpha alpha value
   * @return void
   */
  private function verifyAlpha($alpha)
  {
    return $alpha === null || ($alpha >= 0 && $alpha <= 255);
  }
}