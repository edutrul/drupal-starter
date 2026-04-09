<?php

declare(strict_types=1);

namespace Drupal\Tests\server_general\Traits;

/**
 * Captures a screenshot or page HTML when a test fails.
 *
 * Tries a screenshot first (works with Selenium/WebDriver). Falls back to
 * HTML for BrowserKit tests where no real browser is available.
 *
 * Requires DTT_HTML_OUTPUT_DIRECTORY to be set in phpunit.xml.
 */
trait FailureCaptureTrait {

  /**
   * Captured page content (PNG bytes or HTML string).
   */
  private ?string $capturedContent = NULL;

  /**
   * File extension matching the captured content ('png' or 'html').
   */
  private string $capturedExtension = 'html';

  /**
   * {@inheritdoc}
   *
   * Captures page content while the Mink session is still alive.
   * Must run before parent::tearDown() which stops the session.
   */
  public function tearDown(): void {
    try {
      // Screenshot works for Selenium/WebDriver drivers.
      $this->capturedContent = $this->getSession()->getDriver()->getScreenshot();
      $this->capturedExtension = 'png';
    }
    catch (\Throwable) {
      // BrowserKit has no screenshot support — fall back to HTML.
      try {
        $this->capturedContent = $this->getSession()->getPage()->getContent();
        $this->capturedExtension = 'html';
      }
      catch (\Throwable) {
        // Session was never started.
      }
    }

    // Parent tearDown stops the Mink session — must be called after capture.
    parent::tearDown();
  }

  /**
   * {@inheritdoc}
   *
   * Writes the captured content to the output directory on failure.
   */
  protected function onNotSuccessfulTest(\Throwable $t): void {
    $directory = getenv('DTT_HTML_OUTPUT_DIRECTORY') ?: ($_ENV['DTT_HTML_OUTPUT_DIRECTORY'] ?? '');

    if ($directory !== '' && $this->capturedContent !== NULL) {
      try {
        if (!is_dir($directory)) {
          mkdir($directory, 0777, TRUE);
        }
        $filename = $directory . DIRECTORY_SEPARATOR . uniqid() . '_failure.' . $this->capturedExtension;
        file_put_contents($filename, $this->capturedContent);
      }
      catch (\Throwable) {
        // Silently ignore write errors — always re-throw the original.
      }
    }

    parent::onNotSuccessfulTest($t);
  }

}
