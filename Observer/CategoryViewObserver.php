<?php
namespace Mauro\CategoryTracking\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mauro\CategoryTracking\Model\CategoryViewFactory;
use Mauro\CategoryTracking\Model\ResourceModel\CategoryView as CategoryViewResource;
use Mauro\CategoryTracking\Model\ResourceModel\CategoryView\CollectionFactory;
use Psr\Log\LoggerInterface;

class CategoryViewObserver implements ObserverInterface
{
    /**
     * @var CategoryViewFactory
     */
    protected $categoryViewFactory;

    /**
     * @var CategoryViewResource
     */
    protected $categoryViewResource;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param CategoryViewFactory $categoryViewFactory
     * @param CategoryViewResource $categoryViewResource
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CategoryViewFactory $categoryViewFactory,
        CategoryViewResource $categoryViewResource,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger
    ) {
        $this->categoryViewFactory = $categoryViewFactory;
        $this->categoryViewResource = $categoryViewResource;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    /**
     * Track category view
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
{
    $category = $observer->getEvent()->getCategory();
    
    if (!$category || !$category->getId()) {
        return;
    }

    $categoryId = $category->getId();
    $categoryName = $category->getName();
    $today = date('Y-m-d');

    try {
        $connection = $this->categoryViewResource->getConnection();
        $table = $this->categoryViewResource->getMainTable();

        $select = $connection->select()
            ->from($table, ['view_count'])
            ->where('category_id = ?', $categoryId)
            ->where('date = ?', $today);
        
        $existingRecord = $connection->fetchOne($select);

        if ($existingRecord) {
            $connection->update(
                $table,
                [
                    'category_name' => $categoryName,
                    'view_count' => new \Zend_Db_Expr('view_count + 1'),
                    'last_viewed_at' => date('Y-m-d H:i:s')
                ],
                [
                    'category_id = ?' => $categoryId,
                    'date = ?' => $today
                ]
            );
        } else {
            $connection->insert(
                $table,
                [
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                    'view_count' => 1,
                    'date' => $today,
                    'last_viewed_at' => date('Y-m-d H:i:s')
                ]
            );
        }
                
    } catch (\Exception $e) {
        $this->logger->error('Error tracking category view: ' . $e->getMessage());
    }
}
}
