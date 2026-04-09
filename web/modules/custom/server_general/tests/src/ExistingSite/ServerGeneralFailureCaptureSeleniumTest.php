<?php

declare(strict_types=1);

namespace Drupal\Tests\server_general\ExistingSite;

use Drupal\Tests\server_general\Traits\FailureCaptureTrait;

/**
 * Verifies FailureCaptureTrait produces a PNG screenshot on Selenium failure.
 *
 * Extends ServerGeneralSelenium2TestBase (real Chrome via WebDriver), so
 * FailureCaptureTrait captures a PNG screenshot instead of falling back to
 * HTML.
 *
 * Trait tearDown() takes precedence over the parent class tearDown() in PHP,
 * so the screenshot is captured before the WebDriver session is closed.
 */
class ServerGeneralFailureCaptureSeleniumTest extends ServerGeneralSelenium2TestBase {

  use FailureCaptureTrait;

  /**
   * Verifies the front page loads with the site heading present.
   */
  public function testFrontPageLoads(): void {
    $this->drupalGet('<front>');
    $this->assertSession()->elementExists('css', 'body');
  }

  /**
   * Intentionally fails to validate PNG screenshot capture via CI artifact.
   *
   * Remove this method after confirming the screenshot artifact appears
   * in GitHub Actions.
   */
  public function testIntentionalFailureForScreenshotValidation(): void {
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('THIS TEXT DOES NOT EXIST ON THE PAGE');
  }

}
