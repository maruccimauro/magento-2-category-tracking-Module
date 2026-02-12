<?php
namespace Mauro\CategoryTracking\Model\ResourceModel\CategoryView;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mauro\CategoryTracking\Model\CategoryView as CategoryViewModel;
use Mauro\CategoryTracking\Model\ResourceModel\CategoryView as CategoryViewResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            CategoryViewModel::class,
            CategoryViewResource::class
        );
    }
}