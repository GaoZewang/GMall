<?php

namespace app\command;

use support\Db;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class MakeModel extends BaseCommand
{
    protected static $defaultName = 'make:model';
    protected static $defaultDescription = 'Generate model from table';




    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 在这里添加你的命令逻辑
        $data=$this->check($input, $output,'model');
        if($data['code']!=200){
            $msg=$data['msg'];
            $output->writeln("<error>>Model overwritten: $msg</error>");
        }

        // ========= 模板 =========
        $templatePath = base_path()
            .DIRECTORY_SEPARATOR.'console'
            .DIRECTORY_SEPARATOR. 'generate'
            .DIRECTORY_SEPARATOR. 'template'
            .DIRECTORY_SEPARATOR. 'model_template.txt';
        if (!$templatePath || !file_exists($templatePath)) {
            echo "Template file not found: " . $templatePath . "\n";
            exit(1);
        }
        $template = file_get_contents($templatePath);
        $fillable = implode("',\n        '", $data['fields']);
        $content  = str_replace(
            ['{{module}}', '{{table}}', '{{class}}', '{{fillable}}'],
            [$data['module'], $data['table'], $data['class'], $fillable],
            $template
        );
        $file=$data['file'];
        file_put_contents($file, $content);

        $output->writeln("<info>Model created: $file</info>");

        return self::SUCCESS;
    }


//    protected function execute(InputInterface $input, OutputInterface $output): int
//    {
//        $module = strtolower($input->getArgument('module'));
//        $table  = getenv('DB_PREFIX','gm_').$input->getArgument('table');
//        $force  = $input->getOption('force');
//        $class  = ucwords ($input->getArgument('class')).'Model';
//        // ========= 检查表是否存在 =========
//        $exists = Db::select("
//                SELECT TABLE_NAME
//                FROM information_schema.tables
//                WHERE table_schema = DATABASE()
//                AND table_name = ?
//            ", [$table]);
//        if (!$exists) {
//            $output->writeln("<error>Table [$table] not exists</error>");
//            return self::FAILURE;
//        }
//
//        // ========= 获取字段 =========
//        $columns = Db::select("SHOW COLUMNS FROM `$table`");
//
//        $fields  = array_column($columns, 'Field');
//
//        // ========= 路径 =========
//        $dir  = base_path("app/$module/model");
//        $file = "$dir/$class.php";
//
//        // ========= 创建目录 =========
//        if (!is_dir($dir)) {
//            mkdir($dir, 0777, true);
//        }
//
//        if (file_exists($file) && !$force) {
//            $output->writeln("<comment>Model already exists: $file (use -f to overwrite)</comment>");
//            return self::SUCCESS;
//        }
//        // ========= 生成 fillable =========
//        $fillable = implode("',\n        '", $fields);
//
//        // ========= 模板 =========
//        $templatePath = base_path()
//            .DIRECTORY_SEPARATOR.'console'
//            .DIRECTORY_SEPARATOR. 'generate'
//            .DIRECTORY_SEPARATOR. 'template'
//            .DIRECTORY_SEPARATOR. 'model_template.txt';
//        if (!$templatePath || !file_exists($templatePath)) {
//            echo "Template file not found: " . $templatePath . "\n";
//            exit(1);
//        }
//        $template = file_get_contents($templatePath);
//
//        $content  = str_replace(
//            ['{{module}}', '{{table}}', '{{class}}', '{{fillable}}'],
//            [$module, $table, $class, $fillable],
//            $template
//        );
//        file_put_contents($file, $content);
//
//        $output->writeln("<info>Model created: $file</info>");
//
//        return self::SUCCESS;
//    }
}
