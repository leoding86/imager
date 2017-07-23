<?php

namespace Leoding86\Imager;

use OutOfBoundsException;
use RuntimeException;

class Color
{
  private $red;
  private $green;
  private $blue;
  private $alpha;

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
      throw new OutOfBoundsExp('Unsupport color value');
    }

    $this->red = $red;
    $this->green = $green;
    $this->blue = $blue;
    $this->alpha = floor(127 * $alpha / 255);
  }

  public function __get($name)
  {
    if (property_exists($this, $name)) {
      return $this->$name;
    } else {
      throw new RuntimeException('Unkown property {Color::' . $name . '}');
    }
  }

  public function colorAllocate(Image $image)
  {
    if ($this->alpha !== null) {
      return imagecolorallocatealpha($image->getRes(), $this->red, $this->green, $this->blue, $this->alpha);
    } else {
      return imagecolorallocate($image->getRes(), $this->red, $this->green, $this->blue);
    }
  }

  public function fillImage(Image $image)
  {
    $allocatecolor = $this->colorAllocate($image);
    imagefill($image->getRes(), 0, 0, $allocatecolor);
    imagecolordeallocate($image->getRes(), $allocatecolor);
  }

  private function verifyChannel($channel)
  {
    return $channel >=0 && $channel <= 255;
  }

  private function verifyAlpha($alpha)
  {
    return $alpha === null || ($alpha >= 0 && $alpha <= 255);
  }
}