<?php
declare( strict_types = 1 );

namespace DanielEScherzer\CommonMarkPygmentsHighlighter;

use League\CommonMark\Extension\CommonMark\Renderer\Block\FencedCodeRenderer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Xml\XmlNodeRendererInterface;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;
use Ramsey\Pygments\PygmentizeProcessFailed;
use Ramsey\Pygments\Pygments;
use Stringable;

class PygmentsHighlighterRenderer implements
	NodeRendererInterface,
	XmlNodeRendererInterface,
	ConfigurationAwareInterface
{

	public const string ON_EXCEPTION_IGNORE = 'ignore';
	public const string ON_EXCEPTION_WARN = 'warn';
	public const string ON_EXCEPTION_THROW = 'throw';

	private FencedCodeRenderer $base;
	private ConfigurationInterface $config;

	public function __construct() {
		$this->base = new FencedCodeRenderer();
	}

	public function setConfiguration( ConfigurationInterface $config ): void {
		$this->config = $config;
	}

	/**
	 * Use base renderer unless we can highlight
	 */
	public function render(
		Node $node,
		ChildNodeRendererInterface $childRenderer
	): Stringable {
		// Base renderer will enforce that $node is FencedCode
		$fallback = $this->base->render( $node, $childRenderer );

		$infoWords = $node->getInfoWords();
		if ( $infoWords && $infoWords[0] !== '' ) {
			$lang = $infoWords[0];
			if ( str_starts_with( $lang, 'language-' ) ) {
				$lang = substr( $lang, strlen( 'language-' ) );
			}
			$path = $this->config->get(
				'pygments_highlighter/pygmentize_path'
			);
			if ( $path === null ) {
				// Use default
				$pygments = new Pygments();
			} else {
				$pygments = new Pygments( $path );
			}
			try {
				$rendered = $pygments->highlight(
					$node->getLiteral(),
					$lang,
					'html'
				);
			} catch ( PygmentizeProcessFailed $e ) {
				$onException = $this->config->get(
					'pygments_highlighter/on_exception'
				);
				if ( $onException === self::ON_EXCEPTION_IGNORE ) {
					return $fallback;
				}
				if ( $onException === self::ON_EXCEPTION_THROW ) {
					throw $e;
				}
				// either ON_EXCEPTION_WARN or a bad configuration value
				$errMessage = "Highlighting failed: " . $e->getMessage();
				if ( $onException !== self::ON_EXCEPTION_WARN ) {
					// Should be impossible since the configuration is validated
					// @codeCoverageIgnoreStart
					$errMessage .= ' [configuration for behavior on exceptions'
						. ' is invalid, falling back to showing a warning]';
					// @codeCoverageIgnoreEnd
				}
				return new HTMLElement(
					'div',
					[ 'class' => 'pygments-highlight' ],
					[
						new HTMLElement(
							'span',
							[ 'class' => 'pygments-highlighter-failed' ],
							htmlspecialchars( $errMessage )
						),
						$fallback,
					]
				);
			}
			return new HTMLElement(
				'div',
				[ 'class' => 'pygments-highlighter' ],
				$rendered
			);
		}

		return $fallback;
	}

	/**
	 * Can't extend FencedCodeRenderer, so manually delegate
	 */
	public function getXmlTagName( Node $node ): string {
		return $this->base->getXmlTagName( $node );
	}

	/**
	 * Can't extend FencedCodeRenderer, so manually delegate
	 */
	public function getXmlAttributes( Node $node ): array {
		return $this->base->getXmlAttributes( $node );
	}
}
