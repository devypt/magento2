<?php
/**
 * Test format of layout files
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity\Layout;

class HandlesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test dependencies between handle attributes that is out of coverage by XSD
     *
     * @param string $layoutFile
     * @dataProvider layoutFilesDataProvider
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testHandleDeclaration($layoutFile)
    {
        $issues = array();
        $node = simplexml_load_file($layoutFile);
        $type = $node['type'];
        $parent = $node['parent'];
        $owner = $node['owner'];
        $label = $node['label'];
        if ($type) {
            switch ($type) {
                case 'page':
                    if ($owner) {
                        $issues[] = 'Attribute "owner" is inappropriate for page types';
                    }
                    break;
                case 'fragment':
                    if ($parent) {
                        $issues[] = 'Attribute "parent" is inappropriate for page fragment types';
                    }
                    if (!$owner) {
                        $issues[] = 'No attribute "owner" is specified for page fragment type';
                    }
                    break;
            }
        } else {
            if ($label) {
                $issues[] = 'Attribute "label" is defined, but "type" is not';
            }
            if ($parent || $owner) {
                $issues[] = 'Attribute "parent" and/or "owner" is defined, but "type" is not';
            }
        }
        if ($issues) {
            $this->fail("Issues found in handle declaration:\n" . implode("\n", $issues) . "\n");
        }
    }

    /**
     * Test dependencies between container attributes that is out of coverage by XSD
     *
     * @param string $layoutFile
     * @dataProvider layoutFilesDataProvider
     */
    public function testContainerDeclaration($layoutFile)
    {
        $issues = array();
        $xml = simplexml_load_file($layoutFile);
        $containers = $xml->xpath('/layout//container') ?: array();
        /** @var SimpleXMLElement $node */
        foreach ($containers as $node) {
            if (!isset($node['htmlTag']) && (isset($node['htmlId']) || isset($node['htmlClass']))) {
                $issues[] = $node->asXML();
            }
        }
        if ($issues) {
            $message = 'The following containers declare attribute "htmlId" and/or "htmlClass", but not "htmlTag":';
            $this->fail($message . "\n" . implode("\n", $issues) . "\n");
        }
    }

    /**
     * Test format of a layout file using XSD
     *
     * @param string $layoutFile
     * @dataProvider layoutFilesDataProvider
     */
    public function testLayoutFormat($layoutFile)
    {
        $schemaFile = BP . '/app/code/Magento/Core/etc/layout_single.xsd';
        $domLayout = new \Magento\Config\Dom(file_get_contents($layoutFile));
        $result = $domLayout->validate($schemaFile, $errors);
        $this->assertTrue($result, print_r($errors, true));
    }

    /**
     * @return array
     */
    public function layoutFilesDataProvider()
    {
        return \Magento\TestFramework\Utility\Files::init()->getLayoutFiles();
    }
}
