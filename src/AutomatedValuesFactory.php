<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use MediaWiki\MediaWikiServices;
use ProfessionalWiki\AutomatedValues\DataAccess\CombiningRulesLookup;
use ProfessionalWiki\AutomatedValues\DataAccess\PageContentFetcher;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesDeserializer;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesJsonValidator;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesLookup;
use ProfessionalWiki\AutomatedValues\DataAccess\WikiRulesLookup;
use Title;

class AutomatedValuesFactory {

	private const CONFIG_PAGE_TITLE = 'AutomatedValues';

	protected static ?self $instance;

	public static function getInstance(): self {
		self::$instance ??= new self();
		return self::$instance;
	}

	final protected function __construct() {
	}

	public function getRulesLookup(): RulesLookup {
		$deserializer = new RulesDeserializer(
			RulesJsonValidator::newInstance(),
			MediaWikiServices::getInstance()->getMainConfig()->get( 'AutomatedValuesDefaultLanguages' )
		);

		return new CombiningRulesLookup(
			MediaWikiServices::getInstance()->getMainConfig()->get( 'AutomatedValuesRules' ),
			$deserializer,
			new WikiRulesLookup(
				new PageContentFetcher(
					MediaWikiServices::getInstance()->getTitleParser(),
					MediaWikiServices::getInstance()->getRevisionLookup()
				),
				$deserializer,
				self::CONFIG_PAGE_TITLE
			),
			MediaWikiServices::getInstance()->getMainConfig()->get( 'AutomatedValuesEnableInWikiConfig' )
		);
	}

	public function isConfigTitle( Title $title ): bool {
		return $title->getNamespace() === NS_MEDIAWIKI
			&& $title->getText() === self::CONFIG_PAGE_TITLE;
	}

}
