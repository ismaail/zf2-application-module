<?php
namespace Application\Test\ViewHelper;

use PHPUnit_Framework_TestCase as TestCase;
use Application\ViewHelper\Truncate;

/**
 * Class TruncateTest
 * @package Application\Test\ViewHelper
 */
class TruncateTest extends TestCase
{
    protected $helper;

    protected $sampleText = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';

    protected $sampleHtml = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt.</p><p>Duis aute irure dolor in reprehenderit in voluptate <a href="http://www.firephp.org/" target="_blank">velit esse</a> cillum dolore eu fugiat nulla pariatur.</p>';

    public function setup()
    {
        $this->helper = new Truncate();
    }

    public function testInstanceOfAbstractHelper()
    {
        $this->assertInstanceOf('\Zend\View\Helper\AbstractHelper', $this->helper);
    }

    public function testTruncateWords()
    {
        $text = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut...';

        $this->assertEquals($text, $this->helper->__invoke($this->sampleText, 100));
    }

    public function testTruncateWordsCustomSuffix()
    {
        $text = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut ....';

        $this->assertEquals($text, $this->helper->__invoke($this->sampleText, 100, ' ....'));
    }

    public function testTruncateHtml()
    {
        $text = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt.</p><p>Duis...</p>';

        $this->assertEquals($text, $this->helper->__invoke($this->sampleHtml, 100));
    }

    public function testTruncateHtml140Chars()
    {
        $text = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt.</p><p>Duis aute irure dolor in reprehenderit in voluptate <a href="http://www.firephp.org/" target="_blank">velit...</a></p>';

        $this->assertEquals($text, $this->helper->__invoke($this->sampleHtml, 155));
    }
}
