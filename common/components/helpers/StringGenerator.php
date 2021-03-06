<?php
namespace common\components\helpers;

class StringGenerator
{
    public static function generate(int $stringLength, bool $allowNumbers = true, bool $allowLetters = false, bool $allowUppercase = false): string
    {
        if ($stringLength <= 0) {
            return '';
        }
        if (!$allowNumbers && !$allowLetters) {
            throw new \Exception('You must allow at least numbers or letters');
        }
        $charset = [];
        if ($allowNumbers) {
            for ($i = 0; $i < 10; $i++) {
                $charset[] = (string)$i;
            }
        }
        if ($allowLetters) {
            $base = ord('a');
            $baseUppercase = ord('A');
            for ($i = 0; $i < 26; $i++) {
                $charset[] = chr($base + $i);
                if ($allowUppercase) {
                    $charset[] = chr($baseUppercase + $i);
                }
            }
        }
        
        $result = '';
        $charsetLength = count($charset) - 1;
        for ($i = 0; $i < $stringLength; $i++) {
            $result .= $charset[mt_rand(0, $charsetLength)];
        }
        
        return $result;
    }
}
