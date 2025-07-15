<?php
declare( strict_types = 1 );

namespace DanielEScherzer\CommonMarkPygmentsHighlighter;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

class PygmentsHighlighterExtension implements ConfigurableExtensionInterface {

	public function configureSchema(
		ConfigurationBuilderInterface $builder
	): void {
		$builder->addSchema( 'pygments_highlighter', Expect::structure( [
			'pygmentize_path' => Expect::anyOf(
				Expect::string(),
				Expect::null()
			)->default( null ),
			'on_exception' => Expect::anyOf(
				PygmentsHighlighterRenderer::ON_EXCEPTION_IGNORE,
				PygmentsHighlighterRenderer::ON_EXCEPTION_WARN,
				PygmentsHighlighterRenderer::ON_EXCEPTION_THROW
			)->default( PygmentsHighlighterRenderer::ON_EXCEPTION_WARN ),
		] ) );
	}

	public function register( EnvironmentBuilderInterface $environment ): void {
		$environment->addRenderer(
			FencedCode::class,
			new PygmentsHighlighterRenderer(),
			// Higher priority than CommonMark
			10
		);
	}
}
