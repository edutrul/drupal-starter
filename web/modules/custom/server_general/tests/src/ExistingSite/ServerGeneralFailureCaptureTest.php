<?php

declare(strict_types=1);

namespace Drupal\Tests\server_general\ExistingSite;

use Drupal\Tests\server_general\Traits\FailureCaptureTrait;
use Symfony\Component\HttpFoundation\Response;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Verifies automatic failure capture via FailureCaptureTrait.
 *
 * On failure, a screenshot (Selenium) or page HTML (BrowserKit) is saved
 * to DTT_HTML_OUTPUT_DIRECTORY and uploaded as a GitHub Actions artifact.
 */
class ServerGeneralFailureCaptureTest extends ExistingSiteBase {

  use FailureCaptureTrait;

  /**
   * Verifies the site front page returns a 200 response.
   */
  public function testFrontPageLoads(): void {
    $this->drupalGet('<front>');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
  }

}
