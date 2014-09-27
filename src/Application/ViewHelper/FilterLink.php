<?php
namespace Application\ViewHelper;

use Zend\View\Helper\AbstractHelper;

/**
 * Class FilterLink
 *
 * Clear url link by cleaning non-ascii characters
 * and replacing non alphanum with a word separator
 *
 * @package Application\ViewHelper
 *
 * @author ismaail <contact@ismaail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 *
 */
class FilterLink extends AbstractHelper
{

    /**
     * @param string $string        The string to filter
     * @param string $separator     The words separator, default "_" underscore
     * @param bool $toLowerCase     Convert to lowercase, default is true
     *
     * @return string
     */
    public function __invoke($string, $separator = '_', $toLowerCase = true)
    {
        $trans = array(
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ă'=>'A',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'ă'=>'a', 'æ'=>'a', 'ƒ' => 'a',
            'Þ'=>'B', 'þ'=>'b',
            'Ç'=>'C', 'ç'=>'c',
            'Ð'=>'D',
            'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e',
            'Ğ'=>'G', 'ğ'=>'g',
            'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'İ'=>'I',
            'ı'=>'i', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i',
            'Ñ'=>'N', 'ñ'=>'n',
            'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'ö'=>'o', 'ø'=>'o',
            'ð'=>'o', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'Œ' => 'O', 'œ' => 'o',
            'Ŕ'=>'R', 'ŕ'=>'r',
            'Š'=>'S', 'š'=>'s', 'Ş'=>'S', 'ș'=>'s', 'Ș'=>'S', 'ş'=>'s', 'ß'=>'s',
            'ț'=>'t', 'Ț'=>'T',
            'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U',
            'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'µ' => 'u',
            'Ý'=>'Y', 'Ÿ'=>'Y', '¥'=>'Y',
            'ý'=>'y', 'ÿ'=>'y',
            'Ž'=>'Z', 'ž'=>'z'
        );

        // clean non-ascii characters
        $string = strtr($string, $trans);

        $string = utf8_decode($string);

        if ($toLowerCase) {
            $string = strtolower($string);
        }

        // Restore words separator if nulled
        if (null === $separator) {
            $separator = '_';
        }

        // replace non-alphanum with custom separator
        $string = preg_replace('/([^a-z0-9]+)/i', $separator, $string);

        // trim the separator
        $string = trim($string, $separator);

        return $string;
    }
}
