<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use Content;
use IContextSource;
use MediaWiki\MediaWikiServices;
use ProfessionalWiki\AutomatedValues\DataAccess\PageContentFetcher;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesDeserializer;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesLookup;
use ProfessionalWiki\AutomatedValues\DataAccess\RuleValidator;
use ProfessionalWiki\AutomatedValues\Domain\Rules;
use Status;
use Title;
use User;
use Wikibase\DataModel\Entity\StatementListProvidingEntity;
use Wikibase\DataModel\Statement\StatementListProvider;
use Wikibase\DataModel\Term\FingerprintProvider;
use Wikibase\Repo\Content\EntityContent;

class Hooks {

	public static function onEditFilterMergedContent( IContextSource $context, Content $content, Status $status, string $summary, User $user, bool $minorEdit ): void {
		if ( $content instanceof EntityContent ) {
			try {
				$entity = $content->getEntity();
			}
			catch ( \Exception $ex ) {
			}

			if ( isset( $entity ) && $entity instanceof StatementListProvidingEntity ) {
				self::getRulesLookup()->getRules()->applyTo( $entity );
			}
		}
	}

	private static function getRulesLookup(): RulesLookup {
		return new RulesLookup(
			new PageContentFetcher(
				MediaWikiServices::getInstance()->getTitleParser(),
				MediaWikiServices::getInstance()->getRevisionLookup()
			),
			new RulesDeserializer(
				RuleValidator::newInstance(),
				[] // TODO
			)
		);
	}

	public static function onContentHandlerDefaultModelFor( Title $title, ?string &$model ): void {
		if ( $title->getNamespace() === NS_MEDIAWIKI && $title->getText() === 'AutomatedValues' ) {
			$model = 'json'; // CONTENT_MODEL_JSON (string to make Psalm happy)
		}
	}

}
