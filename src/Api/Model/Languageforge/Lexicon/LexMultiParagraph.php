<?php

namespace Api\Model\Languageforge\Lexicon;

use Api\Model\Mapper\ArrayOf;
use Api\Model\Mapper\ObjectForEncoding;
use LazyProperty\LazyPropertiesTrait;

function generateParagraph() {
    return new LexParagraph();
}

class LexMultiParagraph extends ObjectForEncoding
{
    use LazyPropertiesTrait;

    public function __construct($guid = '')
    {
        $this->setReadOnlyProp('guid');
        if (!$guid || !Guid::isValid($guid)) $guid = Guid::create();
        $this->guid = $guid;
        $this->initLazyProperties(['paragraphs'], false);
    }

    protected function createProperty($name) {
        switch ($name) {
            case 'paragraphs':
                return new ArrayOf('Api\Model\Languageforge\Lexicon\generateParagraph');
            default:
                return '';
        }
    }

    /** @var string */
    public $guid;

    /** @var string */
    public $inputSystem;

    /** @var ArrayOf LexParagraph */
    public $paragraphs;


    /**
     * @return string
     */
    public function toHTML() {
        $html = '';
        /** @var LexParagraph $paragraph */
        foreach ($this->paragraphs as $paragraph) {
            $html .= '<p';
            $html .= ' lang="' . $this->inputSystem . '"';
            $html .= ' data-guid="' . $paragraph->guid . '"';
            $html .= ' data-style-name="' . $paragraph->styleName . '"';
            $html .= '>';
            $html .= $paragraph->content;
            $html .= '</p>';
        }
        return $html;
    }

    /**
     * @param $html string
     */
    public function fromHTML($html) {
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $this->paragraphs->exchangeArray(array());
        /** @var \DOMElement $node */
        foreach ($dom->getElementsByTagName('p') as $node) {
            $this->inputSystem = $node->getAttribute('lang');
            $paragraph = new LexParagraph();
            $paragraph->guid = $node->getAttribute('data-guid');
            $paragraph->styleName = $node->getAttribute('data-style-name');
            $paragraph->content = LiftDecoder::sanitizeSpans($node, $this->inputSystem);
            $this->paragraphs->append($paragraph);
        }
    }
}
