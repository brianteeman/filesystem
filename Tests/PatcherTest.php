<?php
/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Filesystem\Tests;

use Joomla\Filesystem\Patcher;
use Joomla\Filesystem\Path;
use Joomla\Test\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * A unit test class for Patcher
 */
class PatcherTest extends TestCase
{
	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass(): void
	{
		if (!\defined('JPATH_ROOT'))
		{
			self::markTestSkipped('Constant `JPATH_ROOT` is not defined.');
		}
	}

	/**
	 * Sets up the fixture.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp(): void
	{
		parent::setUp();

		// Make sure previous test files are cleaned up
		$this->_cleanupTestFiles();

		// Make some test files and folders
		mkdir(Path::clean(__DIR__ . '/tmp/patcher'), 0777, true);
	}

	/**
	 * Remove created files
	 *
	 * @return  void
	 */
	protected function tearDown(): void
	{
		$this->_cleanupTestFiles();
	}

	/**
	 * Convenience method to cleanup before and after test
	 *
	 * @return  void
	 */
	private function _cleanupTestFiles()
	{
		$this->_cleanupFile(Path::clean(__DIR__ . '/tmp/patcher/lao2tzu.diff'));
		$this->_cleanupFile(Path::clean(__DIR__ . '/tmp/patcher/lao'));
		$this->_cleanupFile(Path::clean(__DIR__ . '/tmp/patcher/tzu'));
		$this->_cleanupFile(Path::clean(__DIR__ . '/tmp/patcher'));
	}

	/**
	 * Convenience method to clean up for files test
	 *
	 * @param   string  $path  The path to clean
	 *
	 * @return  void
	 */
	private function _cleanupFile(string $path)
	{
		if (file_exists($path))
		{
			if (is_file($path))
			{
				unlink($path);
			}
			elseif (is_dir($path))
			{
				rmdir($path);
			}
		}
	}

	/**
	 * Data provider for testAdd
	 *
	 * @return  \Generator
	 */
	public function addData(): \Generator
	{
		$udiff = 'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,7 +1,6 @@
-The Way that can be told of is not the eternal Way;
-The name that can be named is not the eternal name.
 The Nameless is the origin of Heaven and Earth;
-The Named is the mother of all things.
+The named is the mother of all things.
+
 Therefore let there always be non-being,
   so we may see their subtlety,
 And let there always be being,
@@ -9,4 +8,7 @@
 The two are the same,
 But after they are produced,
   they have different names.
+They both may be called deep and profound.
+Deeper and more profound,
+The door of all subtleties!
';

		// Use of realpath to ensure test works for on all platforms
		yield [
			$udiff,
			realpath(__DIR__ . '/tmp/patcher'),
			0,
			[
				[
					'udiff' => $udiff,
					'root'  => realpath(__DIR__ . '/tmp/patcher') . DIRECTORY_SEPARATOR,
					'strip' => 0,
				],
			],
		];

		yield [
			$udiff,
			realpath(__DIR__ . '/tmp/patcher') . DIRECTORY_SEPARATOR,
			0,
			[
				[
					'udiff' => $udiff,
					'root'  => realpath(__DIR__ . '/tmp/patcher') . DIRECTORY_SEPARATOR,
					'strip' => 0,
				],
			],
		];

		yield [
			$udiff,
			null,
			0,
			[
				[
					'udiff' => $udiff,
					'root'  => '',
					'strip' => 0,
				],
			],
		];

		yield [
			$udiff,
			'',
			0,
			[
				[
					'udiff' => $udiff,
					'root'  => DIRECTORY_SEPARATOR,
					'strip' => 0,
				],
			],
		];
	}

	/**
	 * Test Patcher::add add a unified diff string to the patcher
	 *
	 * @param   string  $udiff     Unified diff input string
	 * @param   string  $root      The files root path
	 * @param   string  $strip     The number of '/' to strip
	 * @param   array   $expected  The expected array patches
	 *
	 * @dataProvider addData
	 */
	public function testAdd($udiff, $root, $strip, $expected)
	{
		$patcher = Patcher::getInstance()->reset();
		$patcher->add($udiff, $root, $strip);
		$this->assertSame(
			$expected,
			TestHelper::getValue($patcher, 'patches'),
			'Line:' . __LINE__ . ' The patcher cannot add the unified diff string.'
		);
	}

	/**
	 * Test Patcher::addFile add a unified diff file to the patcher
	 */
	public function testAddFile()
	{
		$udiff = 'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,7 +1,6 @@
-The Way that can be told of is not the eternal Way;
-The name that can be named is not the eternal name.
 The Nameless is the origin of Heaven and Earth;
-The Named is the mother of all things.
+The named is the mother of all things.
+
 Therefore let there always be non-being,
   so we may see their subtlety,
 And let there always be being,
@@ -9,4 +8,7 @@
 The two are the same,
 But after they are produced,
   they have different names.
+They both may be called deep and profound.
+Deeper and more profound,
+The door of all subtleties!
';

		// Use of realpath to ensure test works for on all platforms
		file_put_contents(__DIR__ . '/tmp/patcher/lao2tzu.diff', $udiff);
		$patcher = Patcher::getInstance()->reset();
		$patcher->addFile(__DIR__ . '/tmp/patcher/lao2tzu.diff', realpath(__DIR__ . '/tmp/patcher'));

		$this->assertSame(
			[
				[
					'udiff' => $udiff,
					'root'  => realpath(__DIR__ . '/tmp/patcher') . DIRECTORY_SEPARATOR,
					'strip' => 0,
				],
			],
			TestHelper::getValue($patcher, 'patches'),
			'Line:' . __LINE__ . ' The patcher cannot add the unified diff file.'
		);
	}

	/**
	 * Patcher::reset reset the patcher to its initial state
	 */
	public function testReset()
	{
		$udiff = 'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,7 +1,6 @@
-The Way that can be told of is not the eternal Way;
-The name that can be named is not the eternal name.
 The Nameless is the origin of Heaven and Earth;
-The Named is the mother of all things.
+The named is the mother of all things.
+
 Therefore let there always be non-being,
   so we may see their subtlety,
 And let there always be being,
@@ -9,4 +8,7 @@
 The two are the same,
 But after they are produced,
   they have different names.
+They both may be called deep and profound.
+Deeper and more profound,
+The door of all subtleties!
';
		$patcher = Patcher::getInstance()->reset();
		$patcher->add($udiff, __DIR__ . '/patcher/');
		$this->assertEquals(
			$patcher->reset(),
			$patcher,
			'Line:' . __LINE__ . ' The reset method does not return $this for chaining.'
		);
		$this->assertSame(
			[],
			TestHelper::getValue($patcher, 'sources'),
			'Line:' . __LINE__ . ' The patcher has not been reset.'
		);
		$this->assertSame(
			[],
			TestHelper::getValue($patcher, 'destinations'),
			'Line:' . __LINE__ . ' The patcher has not been reset.'
		);
		$this->assertSame(
			[],
			TestHelper::getValue($patcher, 'removals'),
			'Line:' . __LINE__ . ' The patcher has not been reset.'
		);
		$this->assertSame(
			[],
			TestHelper::getValue($patcher, 'patches'),
			'Line:' . __LINE__ . ' The patcher has not been reset.'
		);
	}

	/**
	 * Data provider for testApply
	 *
	 * @return  \Generator
	 */
	public function applyData(): \Generator
	{
		yield 'Test classical feature' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,7 +1,6 @@
-The Way that can be told of is not the eternal Way;
-The name that can be named is not the eternal name.
 The Nameless is the origin of Heaven and Earth;
-The Named is the mother of all things.
+The named is the mother of all things.
+
 Therefore let there always be non-being,
   so we may see their subtlety,
 And let there always be being,
@@ -9,4 +8,7 @@
 The two are the same,
 But after they are produced,
   they have different names.
+They both may be called deep and profound.
+Deeper and more profound,
+The door of all subtleties!
',
			__DIR__ . '/tmp/patcher',
			0,
			[
				__DIR__ . '/tmp/patcher/lao' =>
					'The Way that can be told of is not the eternal Way;
The name that can be named is not the eternal name.
The Nameless is the origin of Heaven and Earth;
The Named is the mother of all things.
Therefore let there always be non-being,
  so we may see their subtlety,
And let there always be being,
  so we may see their outcome.
The two are the same,
But after they are produced,
  they have different names.
',
			],
			[
				__DIR__ . '/tmp/patcher/tzu' =>
					'The Nameless is the origin of Heaven and Earth;
The named is the mother of all things.

Therefore let there always be non-being,
  so we may see their subtlety,
And let there always be being,
  so we may see their outcome.
The two are the same,
But after they are produced,
  they have different names.
They both may be called deep and profound.
Deeper and more profound,
The door of all subtleties!
',
			],
			1,
			false,
		];

		yield 'Test truncated hunk' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1 +1 @@
-The Way that can be told of is not the eternal Way;
+The named is the mother of all things.
',
			__DIR__ . '/tmp/patcher',
			0,
			[
				__DIR__ . '/tmp/patcher/lao' =>
					'The Way that can be told of is not the eternal Way;
The name that can be named is not the eternal name.
The Nameless is the origin of Heaven and Earth;
The Named is the mother of all things.
Therefore let there always be non-being,
  so we may see their subtlety,
And let there always be being,
  so we may see their outcome.
The two are the same,
But after they are produced,
  they have different names.
',
			],
			[
				__DIR__ . '/tmp/patcher/tzu' =>
					'The named is the mother of all things.
The name that can be named is not the eternal name.
The Nameless is the origin of Heaven and Earth;
The Named is the mother of all things.
Therefore let there always be non-being,
  so we may see their subtlety,
And let there always be being,
  so we may see their outcome.
The two are the same,
But after they are produced,
  they have different names.
',
			],
			1,
			false,
		];

		yield 'Test strip is null' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,7 +1,6 @@
-The Way that can be told of is not the eternal Way;
-The name that can be named is not the eternal name.
 The Nameless is the origin of Heaven and Earth;
-The Named is the mother of all things.
+The named is the mother of all things.
+
 Therefore let there always be non-being,
   so we may see their subtlety,
 And let there always be being,
@@ -9,4 +8,7 @@
 The two are the same,
 But after they are produced,
   they have different names.
+They both may be called deep and profound.
+Deeper and more profound,
+The door of all subtleties!
',
			__DIR__ . '/tmp/patcher',
			null,
			[
				__DIR__ . '/tmp/patcher/lao' =>
					'The Way that can be told of is not the eternal Way;
The name that can be named is not the eternal name.
The Nameless is the origin of Heaven and Earth;
The Named is the mother of all things.
Therefore let there always be non-being,
  so we may see their subtlety,
And let there always be being,
  so we may see their outcome.
The two are the same,
But after they are produced,
  they have different names.
',
			],
			[
				__DIR__ . '/tmp/patcher/tzu' =>
					'The Nameless is the origin of Heaven and Earth;
The named is the mother of all things.

Therefore let there always be non-being,
  so we may see their subtlety,
And let there always be being,
  so we may see their outcome.
The two are the same,
But after they are produced,
  they have different names.
They both may be called deep and profound.
Deeper and more profound,
The door of all subtleties!
',
			],
			1,
			false,
		];

		yield 'Test strip is different of 0' => [
			'Index: lao
===================================================================
--- /path/to/lao	2011-09-21 16:05:45.086909120 +0200
+++ /path/to/tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,7 +1,6 @@
-The Way that can be told of is not the eternal Way;
-The name that can be named is not the eternal name.
 The Nameless is the origin of Heaven and Earth;
-The Named is the mother of all things.
+The named is the mother of all things.
+
 Therefore let there always be non-being,
   so we may see their subtlety,
 And let there always be being,
@@ -9,4 +8,7 @@
 The two are the same,
 But after they are produced,
   they have different names.
+They both may be called deep and profound.
+Deeper and more profound,
+The door of all subtleties!
',
			__DIR__ . '/tmp/patcher',
			3,
			[
				__DIR__ . '/tmp/patcher/lao' =>
					'The Way that can be told of is not the eternal Way;
The name that can be named is not the eternal name.
The Nameless is the origin of Heaven and Earth;
The Named is the mother of all things.
Therefore let there always be non-being,
  so we may see their subtlety,
And let there always be being,
  so we may see their outcome.
The two are the same,
But after they are produced,
  they have different names.
',
			],
			[
				__DIR__ . '/tmp/patcher/tzu' =>
					'The Nameless is the origin of Heaven and Earth;
The named is the mother of all things.

Therefore let there always be non-being,
  so we may see their subtlety,
And let there always be being,
  so we may see their outcome.
The two are the same,
But after they are produced,
  they have different names.
They both may be called deep and profound.
Deeper and more profound,
The door of all subtleties!
',
			],
			1,
			false,
		];

		yield 'Test create file' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -0,0 +1,14 @@
+The Nameless is the origin of Heaven and Earth;
+The named is the mother of all things.
+
+Therefore let there always be non-being,
+  so we may see their subtlety,
+And let there always be being,
+  so we may see their outcome.
+The two are the same,
+But after they are produced,
+  they have different names.
+They both may be called deep and profound.
+Deeper and more profound,
+The door of all subtleties!
+
',
			__DIR__ . '/tmp/patcher',
			0,
			[],
			[
				__DIR__ . '/tmp/patcher/tzu' =>
					'The Nameless is the origin of Heaven and Earth;
The named is the mother of all things.

Therefore let there always be non-being,
  so we may see their subtlety,
And let there always be being,
  so we may see their outcome.
The two are the same,
But after they are produced,
  they have different names.
They both may be called deep and profound.
Deeper and more profound,
The door of all subtleties!
',
			],
			1,
			false,
		];

		yield 'Test patch itself' => [
			'Index: lao
===================================================================
--- tzu	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,7 +1,6 @@
-The Way that can be told of is not the eternal Way;
-The name that can be named is not the eternal name.
 The Nameless is the origin of Heaven and Earth;
-The Named is the mother of all things.
+The named is the mother of all things.
+
 Therefore let there always be non-being,
   so we may see their subtlety,
 And let there always be being,
@@ -9,4 +8,7 @@
 The two are the same,
 But after they are produced,
   they have different names.
+They both may be called deep and profound.
+Deeper and more profound,
+The door of all subtleties!
',
			__DIR__ . '/tmp/patcher',
			0,
			[
				__DIR__ . '/tmp/patcher/tzu' =>
					'The Way that can be told of is not the eternal Way;
The name that can be named is not the eternal name.
The Nameless is the origin of Heaven and Earth;
The Named is the mother of all things.
Therefore let there always be non-being,
  so we may see their subtlety,
And let there always be being,
  so we may see their outcome.
The two are the same,
But after they are produced,
  they have different names.
',
			],
			[
				__DIR__ . '/tmp/patcher/tzu' =>
					'The Nameless is the origin of Heaven and Earth;
The named is the mother of all things.

Therefore let there always be non-being,
  so we may see their subtlety,
And let there always be being,
  so we may see their outcome.
The two are the same,
But after they are produced,
  they have different names.
They both may be called deep and profound.
Deeper and more profound,
The door of all subtleties!
',
			],
			1,
			false,
		];

		yield 'Test delete' => [
			'Index: lao
===================================================================
--- tzu	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,11 +1,0 @@
-The Way that can be told of is not the eternal Way;
-The name that can be named is not the eternal name.
-The Nameless is the origin of Heaven and Earth;
-The Named is the mother of all things.
-Therefore let there always be non-being,
-  so we may see their subtlety,
-And let there always be being,
-  so we may see their outcome.
-The two are the same,
-But after they are produced,
-  they have different names.
',
			__DIR__ . '/tmp/patcher',
			0,
			[
				__DIR__ . '/tmp/patcher/tzu' =>
					'The Way that can be told of is not the eternal Way;
The name that can be named is not the eternal name.
The Nameless is the origin of Heaven and Earth;
The Named is the mother of all things.
Therefore let there always be non-being,
  so we may see their subtlety,
And let there always be being,
  so we may see their outcome.
The two are the same,
But after they are produced,
  they have different names.
',
			],
			[
				__DIR__ . '/tmp/patcher/tzu' => null,
			],
			1,
			false,
		];

		yield 'Test unexpected eof after header 1' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
',
			__DIR__ . '/tmp/patcher',
			0,
			[],
			[],
			1,
			\RuntimeException::class,
		];

		yield 'Test unexpected eof after header 2' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200',
			__DIR__ . '/tmp/patcher',
			0,
			[],
			[],
			1,
			\RuntimeException::class,
		];

		yield 'Test unexpected eof in header' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200',
			__DIR__ . '/tmp/patcher',
			0,
			[],
			[],
			1,
			\RuntimeException::class,
		];

		yield 'Test invalid diff in header' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
',
			__DIR__ . '/tmp/patcher',
			0,
			[],
			[],
			1,
			\RuntimeException::class,
		];

		yield 'Test unexpected eof after hunk 1' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,11 +1,0 @@',
			__DIR__ . '/tmp/patcher',
			0,
			[],
			[],
			1,
			\RuntimeException::class,
		];

		yield 'Test unexpected eof after hunk 2' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,11 +1,11 @@
+The Way that can be told of is not the eternal Way;
+The name that can be named is not the eternal name.
-The Nameless is the origin of Heaven and Earth;
',
			__DIR__ . '/tmp/patcher',
			0,
			[],
			[],
			1,
			\RuntimeException::class,
		];

		yield 'Test unexpected remove line' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,1 +1,1 @@
-The Way that can be told of is not the eternal Way;
-The name that can be named is not the eternal name.
+The Nameless is the origin of Heaven and Earth;
',
			__DIR__ . '/tmp/patcher',
			0,
			[],
			[],
			1,
			\RuntimeException::class,
		];

		yield 'Test unexpected add line' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,1 +1,1 @@
+The Way that can be told of is not the eternal Way;
+The name that can be named is not the eternal name.
-The Nameless is the origin of Heaven and Earth;
',
			__DIR__ . '/tmp/patcher',
			0,
			[],
			[],
			1,
			\RuntimeException::class,
		];

		yield 'Test unexisting source' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,7 +1,6 @@
-The Way that can be told of is not the eternal Way;
-The name that can be named is not the eternal name.
 The Nameless is the origin of Heaven and Earth;
-The Named is the mother of all things.
+The named is the mother of all things.
+
 Therefore let there always be non-being,
   so we may see their subtlety,
 And let there always be being,
@@ -9,4 +8,7 @@
 The two are the same,
 But after they are produced,
   they have different names.
+They both may be called deep and profound.
+Deeper and more profound,
+The door of all subtleties!
',
			__DIR__ . '/tmp/patcher',
			0,
			[],
			[],
			1,
			\RuntimeException::class,
		];

		yield 'Test failed verify' => [
			'Index: lao
===================================================================
--- lao	2011-09-21 16:05:45.086909120 +0200
+++ tzu	2011-09-21 16:05:41.156878938 +0200
@@ -1,7 +1,6 @@
-The Way that can be told of is not the eternal Way;
-The name that can be named is not the eternal name.
 The Nameless is the origin of Heaven and Earth;
-The Named is the mother of all things.
+The named is the mother of all things.
+
 Therefore let there always be non-being,
   so we may see their subtlety,
 And let there always be being,
@@ -9,4 +8,7 @@
 The two are the same,
 But after they are produced,
   they have different names.
+They both may be called deep and profound.
+Deeper and more profound,
+The door of all subtleties!
',
			__DIR__ . '/tmp/patcher',
			0,
			[
				__DIR__ . '/tmp/patcher/lao' => '',
			],
			[],
			1,
			\RuntimeException::class,
		];
	}

	/**
	 * Patcher::apply apply the patches
	 *
	 * @param   string   $udiff         Unified diff input string
	 * @param   string   $root          The files root path
	 * @param   string   $strip         The number of '/' to strip
	 * @param   array    $sources       The source files
	 * @param   array    $destinations  The destinations files
	 * @param   integer  $result        The number of files patched
	 * @param   mixed    $throw         The exception throw, false for no exception
	 *
	 * @dataProvider applyData
	 */
	public function testApply($udiff, $root, $strip, $sources, $destinations, $result, $throw)
	{
		if ($throw)
		{
			$this->expectException($throw);
		}

		foreach ($sources as $path => $content)
		{
			file_put_contents($path, $content);
		}

		$patcher = Patcher::getInstance()->reset();
		$patcher->add($udiff, $root, $strip);
		$this->assertEquals(
			$result,
			$patcher->apply(),
			'Line:' . __LINE__ . ' The patcher did not patch ' . $result . ' file(s).'
		);

		foreach ($destinations as $path => $content)
		{
			if (\is_null($content))
			{
				$this->assertFalse(
					is_file($path),
					'Line:' . __LINE__ . ' The patcher did not succeed in patching ' . $path
				);
			}
			else
			{
				// Remove all vertical characters to ensure system independed compare
				$content = preg_replace('/\v/', '', $content);
				$data = file_get_contents($path);
				$data = preg_replace('/\v/', '', $data);

				$this->assertEquals(
					$content,
					$data,
					'Line:' . __LINE__ . ' The patcher did not succeed in patching ' . $path
				);
			}
		}
	}
}
