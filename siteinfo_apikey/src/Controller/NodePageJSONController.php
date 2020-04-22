<?php

namespace Drupal\siteinfo_apikey\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * NodePageJSONController for the siteinfo_apikey module.
 */
class NodePageJSONController extends ControllerBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a NodePageJSONController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Function to render a page node type as JSON.
   *
   * @param string $siteinfo_apikey
   *   string: site api key value from request URL.
   * @param int $nid
   *   integer: node ID value from request URL.
   *
   * @return res
   *
   *   JSON response with node details or error.
   */
  public function nodeJson($siteinfo_apikey, $nid) {

    // Get Site API Key from Drupal Configuration.
    $saved_key = $this->config('system.site')->get('siteapikey');

    // Check if API Key is Valid.
    if ($siteinfo_apikey !== $saved_key) {

      $res = [
        'Access Denied',
        'Message : Invalid Site API Key.',
      ];

      // Return the JSON Response.
      return new JsonResponse($res);

    }

    // Check if Node ID is numeric.
    if (!is_numeric($nid) && $nid <= 0) {

      $res = [
        'Access Denied',
        'Message : Please enter a numeric Node ID.',
      ];

      // Return the JSON Response.
      return new JsonResponse($res);

    }

    // Get Node Information from NID.
    $node = $this->entityTypeManager->getStorage('node')->load($nid);

    // Check if the node is loaded and it is of type 'page'.
    if (!empty($node) && $node->getType() === 'page') {

      // Select Node Details.
      $n_title = $node->getTitle();
      $n_body = $node->body->getString();
      $n_type = $node->getType();

      // Prepare JSON response.
      $res = [
        'Node ID' => $nid,
        'Title' => $n_title,
        'Body' => $n_body,
        'Type' => $n_type,
      ];

      // Return the JSON Response.
      return new JsonResponse($res);
    }

    // If Node does not exist or is not of type 'Page'.
    else {

      $res = [
        'Access Denied',
        'Message: No Node found with ID provided or Node is not page type.',
      ];

      // Return the JSON Response.
      return new JsonResponse($res);
    }

  }

}
