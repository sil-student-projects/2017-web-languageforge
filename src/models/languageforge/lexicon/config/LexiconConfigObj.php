<?php

namespace models\languageforge\lexicon\config;

use models\mapper\ObjectForEncoding;

class LexiconConfigObj extends ObjectForEncoding {

	// config types
	const FIELDLIST = 'fields';
	const MULTITEXT = 'multitext';
	const OPTIONLIST = 'optionlist';
	const MULTIOPTIONLIST = 'multioptionlist';
	
	// fields
	const LEXEME = 'lexeme';
	const DEFINITION = 'definition';
	const GLOSS = 'gloss';
	const POS = 'partOfSpeech';
	const SEMDOM = 'semanticDomain';
	const EXAMPLE_SENTENCE = 'sentence';
	const EXAMPLE_TRANSLATION = 'translation';

	// field lists
	const SENSES_LIST = 'senses';
	const EXAMPLES_LIST = 'examples';
	const CUSTOM_FIELDS_LIST = 'customFields';
	

	// comments
	const COMMENTS_LIST = 'comments';
	const REPLIES_LIST = 'replies';
	
	public function __construct() {
		$this->hideIfEmpty = false;
	}
	
	/**
	 * @var string
	 */
	public $type;
	
	/**
	 * @var boolean
	 */
	public $hideIfEmpty;

}

?>
