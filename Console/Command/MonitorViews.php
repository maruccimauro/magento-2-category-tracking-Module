<?php
namespace Mauro\CategoryTracking\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Mauro\CategoryTracking\Model\ResourceModel\CategoryView\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

class MonitorViews extends Command
{
    const OPTION_INTERVAL = 'interval';
    
    protected $collectionFactory;
    protected $connection;
    protected $tableName;
    protected $output;

    public function __construct(
        CollectionFactory $collectionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->connection = $resourceConnection->getConnection();
        $this->tableName = $resourceConnection->getTableName('category_view_tracking');
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('mauro:category:monitor')
             ->setDescription('My personal monitor of category views')
             ->addOption(
                self::OPTION_INTERVAL,
                'i',
                InputOption::VALUE_OPTIONAL,
                'Update interval in seconds (default: 1)',
                1
            );
        
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $interval = (int)$input->getOption(self::OPTION_INTERVAL);
        
        while (true) {
            $this->clearScreen();
            $this->displayStats();
            sleep($interval);
        }
        
        return Command::SUCCESS;
    }

    protected function clearScreen()
    {
        if (function_exists('system')) {
            system('clear');
        }
        flush();
    }

    protected function renderTitle($title, $color = 'white')
    {
        $this->renderLine("╔══════════════════════════════════════════════════════════════════════════╗", $color);
        $this->renderLine("║           <fg={$color}>{$title}</>", $color);
        $this->renderLine("╚══════════════════════════════════════════════════════════════════════════╝", $color);
    }

    protected function renderSubTitle($title, $color = 'white')
    {
        $this->renderLine("┌─────────────────────────────────────────────────────────────────────────┐", $color);
        $this->renderLine("│           <fg={$color}>{$title}</>", $color);
        $this->renderLine("└─────────────────────────────────────────────────────────────────────────┘", $color);
    }

    protected function renderEmptyLines($count)
    {
        $count = max($count, 1);
        for ($i = 1; $i <= $count; $i++) {
           $this->renderLine("");
        }
    }

    protected function renderLine($line, $color = 'white')
    {
        $this->output->writeln("<fg={$color}>{$line}</>");
    }

    protected function renderHeaderSection()
    {
        $this->renderTitle("My Personal Category View Monitor", "white");
        $now = date('Y-m-d H:i:s');
        $this->renderTitle("Last Update: {$now}", "white");
    }

    protected function renderBodySection()
    {
        $totalViews = $this->getTotalViews();
        
        if ($totalViews == 0) {
            $this->renderEmptyLines(1);
            $this->renderLine('No view data available for today yet.', 'red');
            $this->renderLine('Visit some categories to start seeing statistics.', 'green');
            return;
        }
        $this->renderEmptyLines(1);
        $this->renderLine("TOTAL VISITS TODAY: {$totalViews}", "white");
        $this->displayCategoryRanking('TOP 5 MOST VIEWED CATEGORIES TODAY', 'green', 'DESC', $totalViews);
        $this->displayCategoryRanking('TOP 5 LEAST VIEWED CATEGORIES TODAY', 'blue', 'ASC', $totalViews);
    }


    protected function renderFooterSection()
    {
        $this->renderEmptyLines(2);
        $this->renderSubTitle("Press Ctrl+C to exit", "white");
    }

    protected function displayStats()
    {
        $this->renderHeaderSection();
        $this->renderBodySection();
        $this->renderFooterSection();
    }

    protected function getTotalViews()
    {
        $query = "SELECT SUM(view_count) as total FROM {$this->tableName} WHERE date = CURDATE()";
        $result = $this->connection->fetchOne($query);
        return $result ?: 0;
    }

    protected function displayCategoryRanking($title, $titleColor, $order, $totalViews)
    {
        $this->renderEmptyLines(1);
        $this->renderSubTitle($title, $titleColor);

        $categories = $this->getCategoriesByOrder($order, $totalViews);
        
        if (!empty($categories)) {
            foreach ($categories as $index => $category) {
                $this->displayCategoryRow($index + 1, $category);
            }
        } else {
            $this->renderLine('No data available', 'comment');
        }
    }

    protected function getCategoriesByOrder($order, $totalViews)
    {
        $query = "
            SELECT 
                category_id,
                category_name,
                view_count,
                ROUND((view_count / {$totalViews}) * 100, 2) as percentage
            FROM {$this->tableName}
            WHERE date = CURDATE()
            ORDER BY view_count {$order}
            LIMIT 5
        ";
        
        return $this->connection->fetchAll($query);
    }

    protected function displayCategoryRow($position, $category)
    {
        $idWidth = 8;
        $nameWidth = 15;
        $viewsWidth = 6;

        $idColumn = str_pad('ID(' . $category['category_id'] . ')', $idWidth);
        $nameColumn = str_pad(substr($category['category_name'], 0, $nameWidth), $nameWidth);
        $views = str_pad($category['view_count'], $viewsWidth, ' ', STR_PAD_LEFT);
        $percentage = str_pad($category['percentage'] . '%', 8, ' ', STR_PAD_LEFT);
        $bar = $this->createProgressBar($category['percentage'], 20);
        $color = $this->getColorByPosition($position);

        $this->renderLine(sprintf(
            "  <fg=%s>%d.</> %s │ %s │ %s views │ %s %s",
            $color,
            $position,
            $idColumn,
            $nameColumn,
            $views,
            $percentage,
            $bar
        ));
    }

    protected function createProgressBar($percentage, $length = 20)
    {
        $filled = round(($percentage / 100) * $length);
        $empty = $length - $filled;
        
        $bar = str_repeat('█', $filled) . str_repeat('░', $empty);
        
        if ($percentage >= 50) {
            return "<fg=green>{$bar}</>";
        } elseif ($percentage >= 20) {
            return "<fg=yellow>{$bar}</>";
        } else {
            return "<fg=red>{$bar}</>";
        }
    }

    protected function getColorByPosition($position)
    {
        $colors = [
            1 => 'yellow',
            2 => 'white',
            3 => 'magenta',
            4 => 'cyan',
            5 => 'blue'
        ];
        
        return $colors[$position] ?? 'white';
    }
}
