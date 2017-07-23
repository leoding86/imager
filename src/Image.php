<?php

namespace Leoding86\Imager;

use RuntimeException;

class Image
{
  const CROP_FIT_WIDTH = 1;
  const CROP_FIT_HEIGHT = 2;
  const CROP_FIT_AUTO = 3;
  const BBOX_ALIGN_LEFT = 1;
  const BBOX_ALIGN_RIGHT = 2;
  const BBOX_ALIGN_CENTER = 3;
  private $im;
  private $width;
  private $height;
  private $betterCrop;

  /**
   * 初始化载入创建图片资源
   *
   * @param mixed      $input
   * @param int|null   $height
   */
  public function __construct($input, $height = null)
  {
    $this->betterCrop = false;

    if (is_int($input) && $input > 0 && $height > 0) {
      $this->im = imagecreatetruecolor($input, $height);
    } else {
      if (is_file($input)) {
        $string = file_get_contents($input);
      } else if (is_string($input)) {
        $string = $input;
      }

      if (!($this->im = imagecreatefromstring($string))) {
        throw new RuntimeException('Cannot create image, unkown resource');
      }
    }

    $this->width = imagesx($this->im);
    $this->height = imagesy($this->im);
  }

  public function __get($property)
  {
    if (property_exists($this, $property)) {
      return $this->$property;
    } else {
      throw new RuntimeException('Unkown property {Color::' . $name . '}');
    }
  }

  private function replaceInstance(Image $image)
  {
    imagedestroy($this->im);

    foreach ($this as $prop => $val) {
      $this->$prop = $image->$prop;
    }

    return $this;
  }

  public function getRes()
  {
    return $this->im;
  }

  public function destroy()
  {
    imagedestroy($this->im);
    unset($this);
  }

  public function setBetterCrop($bool)
  {
    $this->betterCrop = $bool;
    return $this;
  }

  public function fillColor(Color $color, $x = 0, $y = 0)
  {
    $color = $color === null ? new Color(255, 255, 255) : $color;
    $color->fillImage($this);
    return $this;
  }

  public function crop($width, $height, $crop_type = self::CROP_FIT_AUTO, Color $bg_color = null)
  {
    $dst_image = new Image($width, $height);

    $dst_ratio = $width / $height;
    $src_ratio = $this->width / $this->height;

    $resize_method = $this->betterCrop ? 'imagecopyresampled' : 'imagecopyresized';

    if ($bg_color === null) {
      $bg_color = new Color(255, 255, 255);
    }

    $bg_color->fillImage($dst_image);

    if ($dst_ratio == $src_ratio) {
      call_user_func(
        $resize_method,
        $dst_image->getRes(), $this->im, 0, 0, 0, 0, $width, $height, $this->width, $this->height
      );

      return $this->replaceInstance($dst_image);
    }

    if ($crop_type === self::CROP_FIT_AUTO) {
      if ($dst_ratio > $src_ratio) {
        $crop_type = self::CROP_FIT_WIDTH;
      } else if ($dst_ratio < $src_ratio) {
        $crop_type = self::CROP_FIT_HEIGHT;
      }
    }
    
    if ($crop_type === self::CROP_FIT_WIDTH) {
      if ($dst_ratio > $src_ratio) {
        $dst_x = $dst_y = $src_x = 0;
        $src_y = ($this->height - $height * ($this->width / $width)) / 2;
        $dst_w = $width;
        $dst_h = $height;
        $src_w = $this->width;
        $src_h = $height * ($this->width / $width);
      } else if ($dst_ratio < $src_ratio) {
        $src_x = $src_y = $dst_x = 0;
        $dst_y = ($height - $this->height * ($width / $this->width)) / 2;
        $src_w = $this->width;
        $src_h = $this->height;
        $dst_w = $width;
        $dst_h = $this->height * ($width / $this->width);
      }
    } else if ($crop_type === self::CROP_FIT_HEIGHT) {
      if ($dst_ratio > $src_ratio) {
        $src_x = $src_y = $dst_y = 0;
        $dst_x = ($width - $this->width * ($height / $this->height)) / 2;
        $dst_w = $this->width * ($height / $this->height);
        $dst_h = $height;
        $src_w = $this->width;
        $src_h = $this->height;
      } else if ($dst_ratio < $src_ratio) {
        $dst_x = $dst_y = $src_y = 0;
        $dst_w = $width;
        $dst_h = $height;
        $src_x = ($this->width - $width * ($this->height / $height)) / 2;
        $src_w = $width * ($this->height / $height);
        $src_h = $this->height;
      }
    } else {
      throw new RuntimeException('Unkown or unsupported crop type ' . $crop_type);
    }

    call_user_func(
      $resize_method, $dst_image->getRes(), $this->im,
      $dst_x, $dst_y, $src_x, $src_y,
      $dst_w, $dst_h, $src_w, $src_h
    );

    return $this->replaceInstance($dst_image);
  }

  public function scale($scale)
  {
    $width = floor($this->width * $scale);
    $height = floor($this->height * $scale);

    $dst_image = new Image((int)$width, (int)$height);
    $resize_method = $this->betterCrop ? 'imagecopyresampled' : 'imagecopyresized';

    call_user_func(
      $resize_method, $dst_image->getRes(), $this->im,
      0, 0, 0, 0,
      $dst_image->width, $dst_image->height, $this->width, $this->height
    );

    return $this->replaceInstance($dst_image);
  }

  public function border($left, $top, $right, $bottom, Color $color = null)
  {
    $dst_image = new Image($this->width + $left + $right, $this->height + $top + $bottom);
    $dst_image->fillColor($color);
    $dst_image->append($this, $left, $top);
    return $this->replaceInstance($dst_image);
  }

  public function append(Image $src_image, $dst_x = 0, $dst_y = 0, $src_x = 0, $src_y = 0, $src_w = null, $src_h = null)
  {
    $src_w = $src_w === null ? $src_image->width : $src_w;
    $src_h = $src_h === null ? $src_image->height : $src_h;

    imagecopy($this->im, $src_image->getRes(), $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
    return $this;
  }

  public function appendTo(Image $dst_image, $dst_x = 0, $dst_y = 0, $src_x = 0, $src_y = 0, $src_w = null, $src_h = null)
  {
    $src_w = $src_w === null ? $this->width : $src_w;
    $src_h = $src_h === null ? $this->height : $src_h;

    imagecopy($dst_image->getRes(), $this->im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
    return $this;
  }

  public function write(Text $text, $x, $y, $align = self::BBOX_ALIGN_LEFT)
  {
    $colorallocate = $text->color->colorAllocate($this);

    if ($align === self::BBOX_ALIGN_CENTER) {
      $x = $x - round($text->box['width'] / 2);
    } else if ($align === self::BBOX_ALIGN_RIGHT) {
      $x = $x - round($text->box['width']);
    }

    call_user_func($text->pen, $this->im, $text->size, $text->angle, $x, $y, $colorallocate, $text->font, $text->text);
    return $this;
  }

  public function display()
  {
    header('Content-Type: image/jpeg');
    imagejpeg($this->im);
    imagedestroy($this->im);
  }

  public function save($file)
  {
    imagejpeg($this->im, $file, 70);
    imagedestroy($this->im);
  }
}
