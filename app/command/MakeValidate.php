<?php

namespace app\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use support\Db;

class MakeValidator extends Command
{
    protected static $defaultName = 'make:validator';
    protected static $defaultDescription = 'Generate validator from table';

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
        $table  = $input->getArgument('table');
        $force  = $input->getOption('force');

        $class = $this->studly($table);

        // ========= 检查表是否存在 =========
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

        // ========= 获取字段信息 =========
        $columns = Db::select("SHOW COLUMNS FROM `$table`");

        $rulesArray = [];
        foreach ($columns as $col) {
            $field = $col['Field'];
            $type = $col['Type'];
            $nullable = strtolower($col['Null']) === 'yes';

            // 自动映射字段类型到验证规则
            $rule = $nullable ? '' : 'required';

            if (stripos($type, 'int') !== false) {
                $rule .= ($rule ? '|' : '') . 'integer';
            } elseif (stripos($type, 'decimal') !== false || stripos($type, 'float') !== false) {
                $rule .= ($rule ? '|' : '') . 'numeric';
            } elseif (stripos($type, 'date') !== false || stripos($type, 'timestamp') !== false) {
                $rule .= ($rule ? '|' : '') . 'date';
            } else {
                // 默认字符串
                $rule .= ($rule ? '|' : '') . 'string';
            }

            $rulesArray[] = "            '$field' => '$rule',";
        }

        $rulesStr = implode("\n", $rulesArray);

        // ========= 加载模板 =========
        $templatePath = realpath(base_path() . '/resources/templates/validate_template.txt');
        if (!$templatePath || !file_exists($templatePath)) {
            $output->writeln("<error>Validator template not found!</error>");
            return self::FAILURE;
        }

        $template = file_get_contents($templatePath);

        // ========= 替换占位符 =========
        $content = str_replace(
            ['{{module}}', '{{class}}', '{{rules}}'],
            [$module, $class, $rulesStr],
            $template
        );

        // ========= 写入文件 =========
        $dir  = base_path("app/$module/validate");
        $file = "$dir/{$class}Validator.php";

        if (!is_dir($dir)) mkdir($dir, 0777, true);

        if (file_exists($file) && !$force) {
            $output->writeln("<comment>Validator already exists: $file (use -f to overwrite)</comment>");
            return self::SUCCESS;
        }

        // 自动加 PHP 标签
        $content = "<?php\n\n" . $content;
        file_put_contents($file, $content);

        $output->writeln($force ? "<info>Validator overwritten: $file</info>" : "<info>Validator created: $file</info>");

        return self::SUCCESS;
    }

    // 下划线转驼峰
    protected function studly($value)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
    }
}
