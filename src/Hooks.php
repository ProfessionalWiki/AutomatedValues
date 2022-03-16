<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use Content;
use EditPage;
use IContextSource;
use MediaWiki\MediaWikiServices;
use ProfessionalWiki\AutomatedValues\DataAccess\PageContentFetcher;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesDeserializer;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesJsonValidator;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesLookup;
use Title;
use Wikibase\DataModel\Entity\StatementListProvidingEntity;
use Wikibase\Repo\Content\EntityContent;

class Hooks {

	private const CONFIG_PAGE_TITLE = 'AutomatedValues';

	public static function onEditFilterMergedContent( IContextSource $context, Content $content ): void {
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
				RulesJsonValidator::newInstance(),
				[] // TODO
			),
			self::CONFIG_PAGE_TITLE
		);
	}

	public static function onContentHandlerDefaultModelFor( Title $title, ?string &$model ): void {
		if ( self::isConfigTitle( $title ) ) {
			$model = 'json'; // CONTENT_MODEL_JSON (string to make Psalm happy)
		}
	}

	private static function isConfigTitle( Title $title ): bool {
		return $title->getNamespace() === NS_MEDIAWIKI
			&& $title->getText() === self::CONFIG_PAGE_TITLE;
	}

	public static function onEditFilter( EditPage $editPage, string $text, string $section, string &$error ): void {
		if ( self::isConfigTitle( $editPage->getTitle() ) && !RulesJsonValidator::newInstance()->validate( $text ) ) {
			$error = \Html::errorBox( wfMessage( 'automated-values-config-invalid' )->escaped() );
		}
	}

}
