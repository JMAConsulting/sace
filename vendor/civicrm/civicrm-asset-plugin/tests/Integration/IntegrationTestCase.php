<?php
namespace Civi\AssetPlugin\Integration;

use ProcessHelper\ProcessHelper as PH;

class IntegrationTestCase extends \PHPUnit\Framework\TestCase {

  public static function getComposerJson(): array {
    return [
      'authors' => [
        [
          'name' => 'Tester McFakus',
          'email' => 'tester@example.org',
        ],
      ],

      'config' => [
        'allow-plugins' => [
          'civicrm/civicrm-asset-plugin' => TRUE,
          'civicrm/composer-compile-plugin' => TRUE,
          'civicrm/composer-downloads-plugin' => TRUE,
          "composer/installers" => TRUE,
          'cweagans/composer-patches' => TRUE,
          "drupal/core-composer-scaffold" => TRUE,
          "drupal/core-project-message" => TRUE,
          "dealerdirect/phpcodesniffer-composer-installer" => TRUE,
        ],
      ],

      'extra' => [
        'compile-mode' => 'ALL',
        'enable-patching' => 'true',
      ],

      'repositories' => [
        'civicrm-asset-plugin' => [
          'type' => 'path',
          'url' => self::getPluginSourceDir(),
        ],

        'd8' => [
          'type' => 'composer',
          'url' => 'https://packages.drupal.org/8',
        ],

        // FIXME: Replace when there's a live ext bridge
        'api4' => [
          'type' => 'package',
          'package' => [
            'name' => 'civipkg/org.civicrm.api4',
            'version' => '4.4.3',
            'dist' => [
              'url' => 'https://github.com/civicrm/org.civicrm.api4/archive/4.4.2.zip',
              'type' => 'zip',
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * @return string
   *   The root folder of the civicrm-asset-plugin.
   */
  public static function getPluginSourceDir() {
    return dirname(dirname(__DIR__));
  }

  /**
   * @return string
   *   The path of the autogenerated composer project.
   */
  public static function getTestDir() {
    return self::$testDir;
  }

  private static $origDir;
  private static $testDir;

  /**
   * Create a temp folder with a "composer.json" file and chdir() into it.
   *
   * @param array $composerJson
   * @return string
   */
  public static function initTestProject($composerJson) {
    self::$origDir = getcwd();
    if (getenv('USE_TEST_PROJECT')) {
      self::$testDir = getenv('USE_TEST_PROJECT');
      @unlink(self::$testDir . DIRECTORY_SEPARATOR . 'composer.lock');
    }
    else {
      self::$testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'assetplg-' . md5(__DIR__ . time() . rand(0, 10000));
      self::cleanDir(self::$testDir);
    }

    if (!is_dir(self::$testDir)) {
      mkdir(self::$testDir);
    }
    file_put_contents(self::$testDir . DIRECTORY_SEPARATOR . 'composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    chdir(self::$testDir);
    return self::$testDir;
  }

  public static function tearDownAfterClass(): void {
    parent::tearDownAfterClass();

    if (self::$testDir) {
      chdir(self::$origDir);
      self::$origDir = NULL;

      if (getenv('USE_TEST_PROJECT')) {
        fwrite(STDERR, sprintf("\n\nTest project location (%s): %s\n", __CLASS__, self::$testDir));
      }
      else {
        self::cleanDir(self::$testDir);
      }
      self::$testDir = NULL;
    }
  }

  /**
   * If a directory exists, remove it.
   *
   * @param string $dir
   */
  private static function cleanDir($dir) {
    PH::runOk(['if [ -d @DIR ]; then rm -rf @DIR ; fi', 'DIR' => $dir]);
  }

  public function assertSameFileContent($expected, $actual) {
    $this->assertEquals(file_get_contents($expected), file_get_contents($actual));
  }

  public function assertFileIsSymlink($path) {
    $this->assertTrue(file_exists($path), "Path ($path) should exist (symlink file)");
    $this->assertTrue(is_link($path), "Path ($path) should be a symlink");

    // Insanity: The above conditions pass, and shell "readlink" is fine, but the PHP
    // variant complains: 'readlink(): No such file or directory'. clearstatcache() doesn't help.
    // $linkTgt = readlink($path);
    $linkTgt = trim(system("readlink " . escapeshellarg($path)));

    $this->assertTrue(is_string($linkTgt));
    $this->assertTrue(is_file(dirname($path) . '/' . $linkTgt), "Path ($path) should be symlinking pointing to a file. Found tgt ($linkTgt)");
  }

  public function assertFileIsNormal($path) {
    $this->assertTrue(file_exists($path), "Path ($path) should exist (normal file)");
    $this->assertTrue(is_file($path), "Path ($path) should be a normal file");
    $this->assertTrue(!is_link($path), "Path ($path) should not be a symlink");
  }

  public function assertDirIsSymlink($path) {
    $this->assertTrue(file_exists($path), "Path ($path) should exist (symlink dir)");
    $this->assertTrue(is_link($path), "Path ($path) should be a symlink");

    // Insanity: The above conditions pass, and shell "readlink" is fine, but the PHP
    // variant complains: 'readlink(): No such file or directory'. clearstatcache() doesn't help.
    // $linkTgt = readlink($path);
    $linkTgt = trim(system("readlink " . escapeshellarg($path)));
    $this->assertTrue(is_string($linkTgt));
    $this->assertTrue(is_dir(dirname($path) . '/' . $linkTgt), "Path ($path) should be symlinking pointing to a dir. Found tgt ($linkTgt");
  }

  public function assertDirIsNormal($path) {
    $this->assertTrue(file_exists($path), "Path ($path) should exist (normal dir)");
    $this->assertTrue(!is_link($path), "Path ($path) should not be a symlink");
    $this->assertTrue(is_dir($path), "Path ($path) should be a dir");
  }

}
