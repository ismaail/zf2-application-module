<?php
namespace Application\ViewHelper;

use Zend\View\Helper\AbstractHelper;
use Zend\Filter\StripTags as StripTagsFilter;

/**
 * Class StripTags
 * @package Blog\ViewHelper
 */
class StripTags extends AbstractHelper
{
    /**
     * @var StripTagsFilter
     */
    protected $filter;

    /**
     * @return StripTagsFilter
     */
    protected function getFilter()
    {
        if (! $this->filter) {
            $this->filter = new StripTagsFilter();
        }

        return $this->filter;
    }

    /**
     * @param string $text
     * @param array $options
     *
     * @return string
     */
    public function __invoke($text, $options = array())
    {
        if (isset($options['allowTags'])) {
            $this->getFilter()->setTagsAllowed($options['allowTags']);
        }

        if (isset($options['allowAttribs'])) {
            $this->getFilter()->setAttributesAllowed($options['allowAttribs']);
        }

        return $this->getFilter()->filter($text);
    }
}
