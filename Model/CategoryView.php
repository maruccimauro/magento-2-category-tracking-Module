<?php
namespace Mauro\CategoryTracking\Model;

use Magento\Framework\Model\AbstractModel;

class CategoryView extends AbstractModel
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'mauro_category_view';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Mauro\CategoryTracking\Model\ResourceModel\CategoryView::class);
        // Deshabilitar el cache para este modelo
        $this->_cacheTag = false;
    }
}