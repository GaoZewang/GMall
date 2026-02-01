<?php

namespace app\command;

use support\Db;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeController extends Command
{
    protected static $defaultName = 'make:controller';
    protected static $defaultDescription = 'Generate controller from table';

    protected function configure()
    {
        $this
            ->addArgument('module', InputArgument::REQUIRED, 'module name')
            ->addArgument('table', InputArgument::REQUIRED, 'table name')
            ->addArgument('class', InputArgument::OPTIONAL, 'class name')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing file');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $module = strtolower($input->getArgument('module'));
        $table  = env('DB_PREFIX', '').$input->getArgument('table');
        $force  = $input->getOption('force');
        $class  = ucwords ($input->getArgument('class'));

        /*
        |--------------------------------------------------------------------------
        | 检查表是否存在
        |--------------------------------------------------------------------------
        */
        $exists = Db::select("
            SELECT TABLE_NAME
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name = ?
        ", [$table]);

        if (!$exists) {
            $output->writeln("<error>Table [$table] not exists</error>");
            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | 获取字段
        |--------------------------------------------------------------------------
        */
        $columns = Db::select("SHOW COLUMNS FROM `$table`");
        $fields  = array_column($columns, 'Field');

        /*
        |--------------------------------------------------------------------------
        | 路径
        |--------------------------------------------------------------------------
        */
        $dir  = base_path("app/$module/controller");
        $file = "$dir/{$class}Controller.php";

        // 创建目录（如果不存在）
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        /*
        |--------------------------------------------------------------------------
        | 文件存在判断
        |--------------------------------------------------------------------------
        */
        if (file_exists($file) && !$force) {
            $output->writeln("<comment>Controller already exists: $file (use -f to overwrite)</comment>");
            return self::SUCCESS;
        }

        /*
        |--------------------------------------------------------------------------
        | Controller 内容模板
        |--------------------------------------------------------------------------
        */

        // ========= 模板 =========
        $templatePath = base_path()
            .DIRECTORY_SEPARATOR.'console'
            .DIRECTORY_SEPARATOR.'generate'
            .DIRECTORY_SEPARATOR.'template'
            .DIRECTORY_SEPARATOR.'controller_template.txt';
        if (!$templatePath || !file_exists($templatePath)) {
            echo "Template file not found: " . $templatePath . "\n";
            exit(1);
        }
        $template = file_get_contents($templatePath);

        // 替换模板中的占位符
        $content = str_replace(
            ['{{module}}', '{{class}}','{{table}}'],
            [$module, $class , $table],
            $template
        );

        file_put_contents($file, $content);

        if ($force && file_exists($file)) {
            $output->writeln("<info>Controller overwritten: $file</info>");
        } else {
            $output->writeln("<info>Controller created: $file</info>");
        }

        return self::SUCCESS;
    }

}
