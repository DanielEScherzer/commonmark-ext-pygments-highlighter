<?php
declare( strict_types = 1 );

namespace DanielEScherzer\CommonMarkPygmentsHighlighter\Tests;

use DanielEScherzer\CommonMarkPygmentsHighlighter\PygmentsHighlighterExtension;
use DanielEScherzer\CommonMarkPygmentsHighlighter\PygmentsHighlighterRenderer;
use FilesystemIterator;
use GlobIterator;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Xml\MarkdownToXmlConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Pygments\PygmentizeProcessFailed;

#[CoversClass( PygmentsHighlighterExtension::class )]
#[CoversClass( PygmentsHighlighterRenderer::class )]
class PygmentsHighlighterOutputTest extends TestCase {

	#[DataProvider( 'provideTestCases' ) ]
	public function testHTMLOutput( string $markdownPath ) {
		$htmlPath = substr( $markdownPath, 0, -2 ) . 'html';

		$frontMatterExt = new FrontMatterExtension();
		$content = file_get_contents( $markdownPath );
		$parsed = $frontMatterExt->getFrontMatterParser()->parse( $content );
		$config = $parsed->getFrontMatter() ?? [];

		$env = new Environment( $config );
		// Always gets added to the environment so that the front matter is
		// understood and not shown
		$env->addExtension( $frontMatterExt );
		$env->addExtension( new PygmentsHighlighterExtension() );
		$env->addExtension( new CommonMarkCoreExtension() );

		$converter = new MarkdownConverter( $env );

		if ( isset( $config['except_exception'] ) ) {
			$this->assertFileDoesNotExist( $htmlPath );
			$this->expectException( PygmentizeProcessFailed::class );
			$this->expectExceptionMessage( $config['except_exception'] );
		}
		$result = $converter->convert( $content );
		if ( isset( $config['except_exception'] ) ) {
			$this->fail( 'Should have thrown by now' );
		}
		$result = trim( (string)$result );

		if ( getenv( 'TESTS_UPDATE_EXPECTED' ) === '1' ) {
			file_put_contents( $htmlPath, $result . "\n" );
		}

		$this->assertFileExists( $htmlPath );
		$htmlContents = file_get_contents( $htmlPath );

		$this->assertSame( trim( $htmlContents ), $result );
	}

	#[DataProvider( 'provideTestCases' ) ]
	public function testXMLOutput( string $markdownPath ) {
		$xmlPath = substr( $markdownPath, 0, -2 ) . 'xml';

		$frontMatterExt = new FrontMatterExtension();
		$content = file_get_contents( $markdownPath );
		$parsed = $frontMatterExt->getFrontMatterParser()->parse( $content );
		$config = $parsed->getFrontMatter() ?? [];

		$env = new Environment( $config );
		// Always gets added to the environment so that the front matter is
		// understood and not shown
		$env->addExtension( $frontMatterExt );
		$env->addExtension( new PygmentsHighlighterExtension() );
		$env->addExtension( new CommonMarkCoreExtension() );

		$converter = new MarkdownToXmlConverter( $env );
		$result = $converter->convert( $content );
		$result = trim( (string)$result );

		if ( getenv( 'TESTS_UPDATE_EXPECTED' ) === '1' ) {
			file_put_contents( $xmlPath, $result . "\n" );
		}

		$this->assertFileExists( $xmlPath );
		$xmlContents = file_get_contents( $xmlPath );

		$this->assertSame( trim( $xmlContents ), $result );
	}

	public static function provideTestCases() {
		$iterator = new GlobIterator(
			__DIR__ . '/data/*.md',
			FilesystemIterator::CURRENT_AS_PATHNAME
		);
		foreach ( $iterator as $path ) {
			yield $path => [ $path ];
		}
	}
}
