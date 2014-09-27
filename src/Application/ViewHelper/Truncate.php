<?php
namespace Application\ViewHelper;

use Zend\View\Helper\AbstractHelper;

/**
 * Class Truncate
 *
 * Truncate text to a number of words
 *
 * @package Application\ViewHelper
 *
 * @author ismaail <contact@ismaail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @todo Add test and check if it works with HTML tags
 */
class Truncate extends AbstractHelper
{
    /**
     * @param string $string    Trget string to truncate
     * @param integer $limit    maximum number of words
     * @param string $suffix    Suffix to add to the result text
     *
     * @return string
     */
    public function __invoke($string, $limit, $suffix = ' .....')
    {
        $length = strlen($string);

        if ($length > $limit) {
            $lastSpace  = strrpos(substr($string, 0, $limit), ' ');
            $string     = substr($string, 0, $lastSpace);
            return trim($string) . $suffix;
        } else {
            return trim($string);
        }
    }
}
