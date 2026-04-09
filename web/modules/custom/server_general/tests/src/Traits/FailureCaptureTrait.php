<?php

declare(strict_types=1);

namespace Drupal\Tests\server_general\Traits;

/**
 * Captures a screenshot or page HTML when a test fails.
 *
 * Tries a screenshot first (works with Selenium/WebDriver). Falls back to
 * HTML for BrowserKit tests where no real browser is available.
 *
 * Uses an absolute path from DTT_HTML_OUTPUT_DIRECTORY — a relative path
 * would resolve differently depending on CWD (Selenium tests run from
 * the Drupal docroot, BrowserKit tests from the project root).
 *
 * Requires DTT_HTML_OUTPUT_DIRECTORY to be set as an absolute path in
 * phpunit.xml (e.g. /var/www/html/test-html-output).
 */
trait FailureCaptureTrait {

  /**
   * Absolute path to the temp capture file written during tearDown.
   *
   * Non-nullable so MemoryManagementTrait::performMemoryCleanup() skips it.
   */
  protected string $failureCaptureFile = '';

  /**
   * {@inheritdoc}
   *
   * Captures page content while the Mink session is still alive and writes
   * it immediately to a temp file. Must run before parent::tearDown() which
   * closes the session.
   */
  public function tearDown(): void {
    $directory = getenv('DTT_HTML_OUTPUT_DIRECTORY') ?: ($_ENV['DTT_HTML_OUTPUT_DIRECTORY'] ?? '');

    if ($directory !== '') {
      try {
        // Resolve to absolute path using current CWD — stored on the object
        // so onNotSuccessfulTest() can find the file regardless of CWD changes.
        $absDirectory = str_starts_with($directory, DIRECTORY_SEPARATOR)
          ? $directory
          : getcwd() . DIRECTORY_SEPARATOR . $directory;

        if (!is_dir($absDirectory)) {
          mkdir($absDirectory, 0777, TRUE);
        }

        try {
          // Screenshot works for Selenium/WebDriver drivers.
          // Uses getDriverInstance() like DTT's ScreenShotTrait — getDriver()
          // returns DriverInterface which does not expose getScreenshot().
          $content = $this->getDriverInstance()->getScreenshot();
          $extension = 'png';
        }
        catch (\Throwable) {
          // BrowserKit has no screenshot support — fall back to HTML.
          $content = $this->getSession()->getPage()->getContent();
          $extension = 'html';
        }

        // Write immediately — MemoryManagementTrait nulls nullable properties
        // after tearDown(), so content cannot survive in a nullable property.
        $this->failureCaptureFile = $absDirectory . DIRECTORY_SEPARATOR . uniqid() . '_capture.' . $extension;
        file_put_contents($this->failureCaptureFile, $content);
      }
      catch (\Throwable) {
        // Session was never started or directory is not writable.
      }
    }

    // Parent tearDown stops the Mink session — must be called after capture.
    parent::tearDown();
  }

  /**
   * {@inheritdoc}
   *
   * Renames the temp capture file to mark it as a failure artifact.
   * Files from passing tests are not renamed and get cleaned up separately.
   */
  protected function onNotSuccessfulTest(\Throwable $t): void {
    if ($this->failureCaptureFile !== '' && file_exists($this->failureCaptureFile)) {
      $failure = str_replace('_capture.', '_failure.', $this->failureCaptureFile);
      rename($this->failureCaptureFile, $failure);
    }

    parent::onNotSuccessfulTest($t);
  }

}
