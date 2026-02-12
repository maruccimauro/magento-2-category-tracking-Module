<?php
namespace Mauro\CategoryTracking\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CategoryView extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('category_view_tracking', 'id');
    }
}