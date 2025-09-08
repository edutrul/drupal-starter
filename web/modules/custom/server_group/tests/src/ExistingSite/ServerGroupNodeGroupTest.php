<?php

namespace Drupal\Tests\server_group\ExistingSite;

use Symfony\Component\HttpFoundation\Response;
use Drupal\Tests\server_general\ExistingSite\ServerGeneralTestBase;

/**
 * Test 'group' for validating group access and membership
 */
class ServerGroupNodeGroupTest extends ServerGeneralTestBase {

  /**
   * {@inheritdoc}
   */
  public function testSubscribeFlow() {
    $user = $this->createUser();

    // Create Group node.
    $group_node = $this->createNode([
      'title' => 'Drupal WOWs',
      'type' => 'group',
      'uid' => 1,
      'body' => 'Loreum ipsum body here',
    ]);
    $group_node->save();
    $group_node_url = $group_node->toUrl();

    // Visit as anonymous.
    $this->drupalGet($group_node_url);
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->assertSession()->pageTextContains('You must be an authenticated user and be in this group to view the content.');

    // Visit as authenticated.
    $this->drupalLogin($user);
    $this->drupalGet($group_node_url);
    $this->assertSession()->elementExists('css', '.server-group-subscribe-url');

    // Simulate the click on subscribe link by going to this path.
    $this->drupalGet('/server-group/group/' . $group_node->id() . '/join');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->assertSession()->pageTextContains('Welcome! You have subscribed to ' . $group_node->label() . '.');

  }

}
