<?php
namespace Application\Test\ViewHelper;

use PHPUnit_Framework_TestCase as TestCase;
use Application\ViewHelper\FilterLink;

class FilterLinkTest extends TestCase
{
    protected $helper;

    protected function setUp()
    {
        $this->helper = new FilterLink();
    }

    public function testInstanceOfAbstractHelper()
    {
        $this->assertInstanceOf('\Zend\View\Helper\AbstractHelper', $this->helper);
    }

    public function testEscapeNonAsciiCharacters()
    {
        $string = 'ŠšŽžÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýþÿ';
        $this->assertEquals(strtolower('SsZzAAAAAAACEEEEIIIINOOOOOOUUUUYBSaaaaaaaceeeeiiiionoooooouuuyby'), $this->helper->__invoke($string));
    }

    public function testEscapeNonAsciiCharecterWithoutLowerCase()
    {
        $string = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕŠŒŽšœžŸ¥µƒ';
        $this->assertEquals('AAAAAAACEEEEIIIIDNOOOOOOUUUUYBsaaaaaaaceeeeiiiionoooooouuuyybyRrSOZsozYYua', $this->helper->__invoke($string, null, false));
    }

    public function testDefaultWordsSeparator()
    {
        $url = 'Sample post éxample';

        $this->assertEquals('sample_post_example', $this->helper->__invoke($url));
    }

    public function testCustomWordsSeparator()
    {
        $url = 'Sample post éxample';

        $this->assertEquals('sample-post-example', $this->helper->__invoke($url, '-'));
    }

    public function testPreserveCase()
    {
        $url = 'Example Sample Post Name';

        $this->assertEquals('Example_Sample_Post_Name', $this->helper->__invoke($url, null, false));
    }

    public function testRemoveNonAlnumCharacters()
    {
        $url = 'Example: Sample Post #11 ?!';

        $this->assertEquals('example_sample_post_11', $this->helper->__invoke($url));
    }

    public function testNoSeparatorDuplication()
    {
        $url = 'Example::__Sample__Post_\__\_\__Name__';

        $this->assertEquals('example_sample_post_name', $this->helper->__invoke($url));
    }
}
