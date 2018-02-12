<?php

namespace app\components;

use Yii;

class LetterAvatar extends \YoHang88\LetterAvatar\LetterAvatar
{

    /**
     * @return \Intervention\Image\Image
     */
    public function generate()
    {
        $words = $this->break_words($this->name);

        $number_of_word = 1;
        $this->name_initials = '';
        foreach ($words as $word) {

            if ($number_of_word > 2)
                break;

            $this->name_initials .= strtoupper(trim($word[0]));

            $number_of_word++;
        }

        $color = $this->stringToColor($this->name);

        if ($this->shape == 'circle') {
            $canvas = $this->image_manager->canvas(480, 480);

            $canvas->circle(480, 240, 240, function ($draw) use ($color) {
                $draw->background($color);
            });

        } else {

            $canvas = $this->image_manager->canvas(480, 480, $color);
        }

        $canvas->text($this->name_initials, 240, 240, function ($font) {
            $font->file(Yii::getAlias('@vendor') . '/yohang88/letter-avatar/src/fonts/arial-bold.ttf');
            $font->size(220);
            $font->color('#ffffff');
            $font->valign('middle');
            $font->align('center');
        });

        return $canvas->resize($this->size, $this->size);
    }


    protected function stringToColor($string)
    {
        $darker = 1.5;
        $rgb = substr(dechex(crc32(str_repeat($string, 3) . md5($string))), 0, 6);
        list($R16, $G16, $B16) = str_split($rgb, 2);
        $R = sprintf("%02X", floor(hexdec($R16) / $darker));
        $G = sprintf("%02X", floor(hexdec($G16) / $darker));
        $B = sprintf("%02X", floor(hexdec($B16) / $darker));
        return '#' . $R . $G . $B;
    }
}
