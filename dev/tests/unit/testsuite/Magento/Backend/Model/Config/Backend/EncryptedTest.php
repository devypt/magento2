<?php
/**
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
namespace Magento\Backend\Model\Config\Backend;

class EncryptedTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_helperMock;

    /** @var \Magento\Backend\Model\Config\Backend\Encrypted */
    protected $_model;

    protected function setUp()
    {
        $contextMock = $this->getMock('Magento\Core\Model\Context', array(), array(), '', false);
        $this->_helperMock = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $resourceMock = $this->getMock('Magento\Core\Model\Resource\AbstractResource',
            array('_construct', '_getReadAdapter', '_getWriteAdapter', 'getIdFieldName'),
            array(), '', false);
        $collectionMock = $this->getMock('Magento\Data\Collection\Db', array(), array(), '', false);
        $registry = $this->getMock('Magento\Core\Model\Registry');
        $storeManager = $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false);
        $coreConfig = $this->getMock('Magento\Core\Model\Config', array(), array(), '', false);
        $this->_model = new \Magento\Backend\Model\Config\Backend\Encrypted(
            $this->_helperMock, $contextMock, $registry, $storeManager, $coreConfig, $resourceMock, $collectionMock
        );

    }

    public function testProcessValue()
    {
        $value = 'someValue';
        $result = 'some value from parent class';
        $this->_helperMock->expects($this->once())->method('decrypt')->with($value)->will($this->returnValue($result));
        $this->assertEquals($result, $this->_model->processValue($value));
    }
}
