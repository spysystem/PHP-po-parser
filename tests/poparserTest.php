<?php

use Sepia\PoParser\Parser;
use Sepia\PoParser\Handler\FileHandler;
use Sepia\PoParser\Handler\StringHandler;

class PoParserTest extends \PHPUnit\Framework\TestCase
{
	public function tearDown(): void
	{
		parent::tearDown();

		if (file_exists(__DIR__.'/pofiles/temp.po')) {
			unlink(__DIR__.'/pofiles/temp.po');
		}
	}

	/**
	 * Test reading default options
	 */
	public function testGetDefaultOptions()
	{
		$parser = new Parser(new StringHandler(''));

		$options = $parser->getOptions();

		$defaultOptions = [
			Parser::OPTION_EOL_KEY => Parser::OPTION_EOL_VALUE,
			Parser::OPTION_EOC_KEY => Parser::OPTION_EOC_VALUE
		];

		static::assertEquals($options, $defaultOptions);
	}

	/**
	 * Test setting options
	 */
	public function testSetOptions()
	{
		$parser = new Parser(new StringHandler(''));

		$parser->setOptions([Parser::OPTION_EOL_KEY => "\n"]);
		$options = $parser->getOptions();

		$defaultOptions = [
			Parser::OPTION_EOL_KEY => "\n",
			Parser::OPTION_EOC_KEY => Parser::OPTION_EOC_VALUE
		];

		static::assertEquals($options, $defaultOptions);
	}

	/**
	 * Test changing source handler.
	 */
	public function testSetHandlerInterface()
	{
		$parser = new Parser(new StringHandler(''));

		$handler2 = new StringHandler('');
		$parser->setSourceHandle($handler2);

		static::assertEquals($handler2, $parser->getSourceHandle());
	}

	/**
	 * Test reading healthy po file.
	 */
	public function testRead()
	{
		try {
			$parser = Parser::parseFile(__DIR__.'/pofiles/healthy.po');
			$result = $parser->getEntries();
		} catch(\Exception $e) {
			$result = [];
			static::fail($e->getMessage());
		}

		static::assertCount(2, $result);

		// Read file without headers.
		// It should not skip first entry
		try {
			$parser = Parser::parseFile(__DIR__.'/pofiles/noheader.po');
			$result = $parser->getEntries();
		} catch(\Exception $e) {
			$result = [];
			static::fail($e->getMessage());
		}

		static::assertCount(2, $result, 'Did not read properly po file without headers.');
	}

	/**
	 * Tests reading the headers.
	 */
	public function testHeaders()
	{
		try {
			$parser = Parser::parseFile(__DIR__.'/pofiles/healthy.po');
			$headers = $parser->getHeaders();

			static::assertCount(18, $headers);
			static::assertEquals("\"Project-Id-Version: \\n\"", $headers[0]);
			static::assertEquals("\"Report-Msgid-Bugs-To: \\n\"", $headers[1]);
			static::assertEquals("\"POT-Creation-Date: 2013-09-25 15:55+0100\\n\"", $headers[2]);
			static::assertEquals("\"PO-Revision-Date: \\n\"", $headers[3]);
			static::assertEquals("\"Last-Translator: Raúl Ferràs <xxxxxxxxxx@xxxxxxx.xxxxx>\\n\"", $headers[4]);
			static::assertEquals("\"Language-Team: \\n\"", $headers[5]);
			static::assertEquals("\"MIME-Version: 1.0\\n\"", $headers[6]);
			static::assertEquals("\"Content-Type: text/plain; charset=UTF-8\\n\"", $headers[7]);
			static::assertEquals("\"Content-Transfer-Encoding: 8bit\\n\"", $headers[8]);
			static::assertEquals("\"Plural-Forms: nplurals=2; plural=n != 1;\\n\"", $headers[9]);
			static::assertEquals("\"X-Poedit-SourceCharset: UTF-8\\n\"", $headers[10]);
			static::assertEquals("\"X-Poedit-KeywordsList: __;_e;_n;_t\\n\"", $headers[11]);
			static::assertEquals("\"X-Textdomain-Support: yes\\n\"", $headers[12]);
			static::assertEquals("\"X-Poedit-Basepath: .\\n\"", $headers[13]);
			static::assertEquals("\"X-Generator: Poedit 1.5.7\\n\"", $headers[14]);
			static::assertEquals("\"X-Poedit-SearchPath-0: .\\n\"", $headers[15]);
			static::assertEquals("\"X-Poedit-SearchPath-1: ../..\\n\"", $headers[16]);
			static::assertEquals("\"X-Poedit-SearchPath-2: ../../../modules\\n\"", $headers[17]);
		} catch(\Exception $e) {
			static::fail($e->getMessage());
		}
	}

	/**
	 * Tests multiline msgid
	 */
	public function testMultilineId()
	{
		try {
			$parser = Parser::parseFile(__DIR__.'/pofiles/multilines.po');
			$result = $parser->getEntries();
			$headers = $parser->getHeaders();

			static::assertCount(18, $headers);
			static::assertCount(9, $result);
		} catch(\Exception $e) {
			static::fail($e->getMessage());
		}
	}

	/**
	 *
	 */
	public function testPlurals()
	{
		try {
			$parser = Parser::parseFile(__DIR__.'/pofiles/plurals.po');
			$headers = $parser->getHeaders();
			$result = $parser->getEntries();

			static::assertCount(7, $headers);
			static::assertCount(15, $result);
		} catch(\Exception $e) {
			static::fail($e->getMessage());
		}
	}

	/**
	 * Tests for msgstr plurals.
	 */
	public function testPluralsMultiline()
	{
		try {
			$parser = Parser::parseFile(__DIR__.'/pofiles/pluralsMultiline.po');
			static::assertCount(2, $parser->getEntries());
			$entries = $parser->getEntries();
			foreach($entries as $id => $entry) {
				static::assertTrue(isset($entry['msgstr[0]']));
				static::assertTrue(isset($entry['msgstr[1]']));
			}
		} catch(\Exception $e) {
			static::fail($e->getMessage());
		}
	}

	/**
	 * Test Writing file
	 */
	public function testWrite()
	{
		// Read & write a simple file
		$parser = Parser::parseFile(__DIR__.'/pofiles/healthy.po');
		$parser->save(['filepath' => __DIR__.'/pofiles/temp.po']);

		static::assertFileEquals(__DIR__.'/pofiles/healthy.po', __DIR__.'/pofiles/temp.po');

		// Read & write a file with no headers
		$parser = Parser::parseFile(__DIR__.'/pofiles/noheader.po');
		$parser->save(['filepath' => __DIR__.'/pofiles/temp.po']);

		static::assertFileEquals(__DIR__.'/pofiles/noheader.po', __DIR__.'/pofiles/temp.po');

		// Read & write a po file with multilines
		$parser = Parser::parseFile(__DIR__.'/pofiles/multilines.po');
		$parser->save(['filepath' => __DIR__.'/pofiles/temp.po']);

		static::assertFileEquals(__DIR__.'/pofiles/multilines.po', __DIR__.'/pofiles/temp.po');

		// Read & write a po file with contexts
		$parser = Parser::parseFile(__DIR__.'/pofiles/context.po');
		$parser->save(['filepath' => __DIR__.'/pofiles/temp.po']);

		static::assertFileEquals(__DIR__.'/pofiles/context.po', __DIR__.'/pofiles/temp.po');


		// Read & write a po file with previous unstranslated strings
		$parser = Parser::parseFile(__DIR__.'/pofiles/previous_unstranslated.po');
		$parser->save(['filepath' => __DIR__.'/pofiles/temp.po']);

		static::assertFileEquals(__DIR__.'/pofiles/previous_unstranslated.po', __DIR__.'/pofiles/temp.po');

		// Read & write a po file with multiple flags
		$parser = Parser::parseFile(__DIR__.'/pofiles/multiflags.po');
		$parser->save(['filepath' => __DIR__.'/pofiles/temp.po']);

		static::assertFileEquals(__DIR__.'/pofiles/multiflags.po', __DIR__.'/pofiles/temp.po');

		unlink(__DIR__.'/pofiles/temp.po');
	}

	/**
	 * Test update entry, update plural forms
	 */
	public function testUpdatePlurals()
	{
		$msgid = '%s post not updated, somebody is editing it.';
		$msgstr = [
			"%s entrada no actualizada, alguien la está editando...",
			"%s entradas no actualizadas, alguien las está editando..."
		];

		$parser = Parser::parseFile(__DIR__.'/pofiles/plurals.po');

		$parser->setEntry($msgid, [
			'msgid'  => $msgid,
			'msgstr' => $msgstr
		]);

		$parser->save(['filepath' => __DIR__.'/pofiles/temp.po']);

		$parser = Parser::parseFile(__DIR__.'/pofiles/temp.po');
		$newPlurals = $parser->getEntries();
		static::assertEquals($newPlurals[$msgid]['msgstr'], $msgstr);
	}

	/**
	 * Test update comments
	 */
	public function testUpdateComments()
	{
		$fileHandler = new FileHandler(__DIR__.'/pofiles/context.po');
		$parser = new Parser($fileHandler);
		$entries = $parser->parse();
		$options = $parser->getOptions();
		$ctxtGlue = $options['context-glue'];

		$msgid = 'Background Attachment'.$ctxtGlue.'Attachment';
		$entry = $entries[$msgid];

		$entry['ccomment'] = ['Test write ccomment'];
		$entry['tcomment'] = ['Test write tcomment'];

		$parser->setEntry($msgid, $entry);
		$parser->save(['filepath' => __DIR__.'/pofiles/temp.po']);

		$parser = Parser::parseFile(__DIR__.'/pofiles/temp.po');
		$entries = $parser->getEntries();

		static::assertEquals($entries[$msgid]['tcomment'][0], $entry['tcomment'][0]);
		static::assertEquals($entries[$msgid]['ccomment'][0], $entry['ccomment'][0]);
	}

//	/**
//	 * Test update with fuzzy flag.
//	 *
//	 * @todo
//	 */
//	public function testUpdateWithFuzzy()
//	{
//		$msgid = '%1$s-%2$s';
//
//		$parser = Parser::parseFile(__DIR__.'/pofiles/context.po');
//		$entries = $parser->getEntries();
//
//		$entries[$msgid]['msgstr'] = ['translate'];
//		$parser->setEntry($msgid, $entries[$msgid]);
//	}

	/**
	 * Test for success update headers
	 */
	public function testUpdateHeaders()
	{
		$parser = Parser::parseFile(__DIR__.'/pofiles/context.po');

		$newHeaders = [
			'"Project-Id-Version: \n"',
			'"Report-Msgid-Bugs-To: \n"',
			'"POT-Creation-Date: \n"',
			'"PO-Revision-Date: \n"',
			'"Last-Translator: none\n"',
			'"Language-Team: \n"',
			'"MIME-Version: 1.0\n"',
			'"Content-Type: text/plain; charset=UTF-8\n"',
			'"Content-Transfer-Encoding: 8bit\n"',
			'"Plural-Forms: nplurals=2; plural=n != 1;\n"'
		];

		$result = $parser->setHeaders($newHeaders)->getHeaders();
		static::assertNotEmpty($result);
		$parser->save(['filepath' => __DIR__.'/pofiles/temp.po']);

		$newPoFile = Parser::parseFile(__DIR__.'/pofiles/temp.po');
		$readHeaders = $newPoFile->getHeaders();
		static::assertEquals($newHeaders, $readHeaders);
	}

	/**
	 * Test for po files with no blank lines between entries
	 */
	public function testNoBlankLines()
	{
		$parser = Parser::parseFile(__DIR__.'/pofiles/noblankline.po');
		$entries = $parser->getEntries();

		$expected = [
			'one' => [
				'msgid'  => [0 => 'one'],
				'msgstr' => [0 => 'uno'],
			],
			'two' => [
				'msgid'  => [0 => 'two'],
				'msgstr' => [0 => 'dos']
			]
		];

		static::assertEquals($entries, $expected);
	}

	/**
	 *  Test for entries with multiple flags
	 */
	public function testFlags()
	{
		// Read po file with 'php-format' flag. Add 'fuzzy' flag.
		// Compare the result with the version that has 'php-format' and 'fuzzy' flags
		$parser = Parser::parseFile(__DIR__.'/pofiles/flags-phpformat.po');
		$entries = $parser->getEntries();

		foreach($entries as $msgid => $entry) {
			$entry['flags'][] = 'fuzzy';
			$parser->setEntry($msgid, $entry);
		}

		$parser->save(['filepath' => __DIR__.'/pofiles/temp.po']);
		static::assertFileEquals(__DIR__.'/pofiles/flags-phpformat-fuzzy.po', __DIR__.'/pofiles/temp.po');
	}

	/**
	 *  Test for reading previous unstranslated strings
	 */
	public function testPreviousUnstranslated()
	{
		$parser = Parser::parseFile(__DIR__.'/pofiles/previous_unstranslated.po');
		$entries = $parser->getEntries();

		$expected = [
			'this is a string' => [
				'msgid'    => ['this is a string'],
				'msgstr'   => ['this is a translation'],
				'previous' => [
					'msgid'  => ['this is a previous string'],
					'msgstr' => ['this is a previous translation string']
				]
			]
		];

		static::assertEquals($entries, $expected);
	}
}
