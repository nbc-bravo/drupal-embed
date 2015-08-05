<?php

/**
 * @file
 * Contains Drupal\embed\DomHelperTrait.
 */

namespace Drupal\embed;

use Drupal\Component\Utility\Html;

/**
 * Wrapper methods for manipulating DOM entries.
 *
 * This utility trait should only be used in application-level code, such as
 * classes that would implement ContainerInjectionInterface. Services registered
 * in the Container should not use this trait but inject the appropriate service
 * directly for easier testing.
 */
trait DomHelperTrait {

  /**
   * Rename a DOMNode tag.
   *
   * @param \DOMNode $node
   *   A DOMElement object.
   * @param string $name
   *   The new tag name.
   */
  protected function changeNodeName(\DOMNode &$node, $name = 'div') {
    if ($node->nodeName != $name) {
      /** @var \DOMElement $replacement_node */
      $replacement_node = $node->ownerDocument->createElement($name);

      // Copy all children of the original node to the new node.
      if ($node->childNodes->length) {
        foreach ($node->childNodes as $child) {
          $child = $replacement_node->ownerDocument->importNode($child, TRUE);
          $replacement_node->appendChild($child);
        }
      }

      // Copy all attributes of the original node to the new node.
      if ($node->attributes->length) {
        foreach ($node->attributes as $attribute) {
          $replacement_node->setAttribute($attribute->nodeName, $attribute->nodeValue);
        }
      }

      $node->parentNode->replaceChild($replacement_node, $node);
      $node = $replacement_node;
    }
  }

  /**
   * Set the contents of a DOMNode.
   *
   * @param \DOMNode $node
   *   A DOMNode object.
   * @param string $content
   *   The text or HTML that will replace the contents of $node.
   */
  protected function setNodeContent(\DOMNode $node, $content) {
    // Remove all children of the DOMNode.
    while ($node->hasChildNodes()) {
      $node->removeChild($node->firstChild);
    }

    if (strlen($content)) {
      // Load the contents into a new DOMDocument and retrieve the elements.
      $replacement_nodes = Html::load($content)->getElementsByTagName('body')->item(0);

      // Finally, import and append the contents to the original node.
      foreach ($replacement_nodes->childNodes as $replacement_node) {
        $replacement_node = $node->ownerDocument->importNode($replacement_node, TRUE);
        $node->appendChild($replacement_node);
      }
    }
  }

  /**
   * Replace the contents of a DOMNode.
   *
   * @param \DOMNode $node
   *   A DOMNode object.
   * @param string $content
   *   The text or HTML that will replace the contents of $node.
   */
  protected function replaceNodeContent(\DOMNode &$node, $content) {
    if (strlen($content)) {
      // Load the contents into a new DOMDocument and retrieve the element.
      $replacement_node = Html::load($content)->getElementsByTagName('body')
        ->item(0)
        ->childNodes
        ->item(0);

      // Import the updated DOMNode from the new DOMDocument into the original
      // one, importing also the child nodes of the replacement DOMNode.
      $replacement_node = $node->ownerDocument->importNode($replacement_node, TRUE);
      $node->parentNode->appendChild($replacement_node);
      $node->parentNode->removeChild($node);
      $node = $replacement_node;
    }
    else {
      $node->parentNode->removeChild($node);
    }
  }
}
