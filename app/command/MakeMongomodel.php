<?php

namespace app\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Webman\Console\Util;

class MakeMongomodel extends Command
{
    protected static $defaultName = 'make:mongomodel';
    protected static $defaultDescription = 'make mongomodel';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::OPTIONAL, 'Name description');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $name = Util::nameToClass($name);
        $output->writeln("Make model $name");
        if (!($pos = strrpos($name, '/'))) {
            $name = ucfirst($name);
            $model_str = Util::guessPath(app_path(), 'model') ?: 'model';
            $file = app_path() . "/$model_str/$name.php";
            $namespace = $model_str === 'Model' ? 'App\Model' : 'app\model';
        } else {
            $name_str = substr($name, 0, $pos);
            if($real_name_str = Util::guessPath(app_path(), $name_str)) {
                $name_str = $real_name_str;
            } else if ($real_section_name = Util::guessPath(app_path(), strstr($name_str, '/', true))) {
                $upper = strtolower($real_section_name[0]) !== $real_section_name[0];
            } else if ($real_base_controller = Util::guessPath(app_path(), 'controller')) {
                $upper = strtolower($real_base_controller[0]) !== $real_base_controller[0];
            }
            $upper = $upper ?? strtolower($name_str[0]) !== $name_str[0];
            if ($upper && !$real_name_str) {
                $name_str = preg_replace_callback('/\/([a-z])/', function ($matches) {
                    return '/' . strtoupper($matches[1]);
                }, ucfirst($name_str));
            }
            $path = "$name_str/" . ($upper ? 'Model' : 'model');
            $name = ucfirst(substr($name, $pos + 1));
            $file = app_path() . "/$path/$name.php";
            $namespace = str_replace('/', '\\', ($upper ? 'App/' : 'app/') . $path);
        }
        $this->createModel($name, $namespace, $file);

        return self::SUCCESS;
    }

    /**
     * @param $class
     * @param $namespace
     * @param $file
     * @return void
     */
    protected function createModel($class, $namespace, $file)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $collection = Util::classToName($class);
        $collection_val = "'$collection'";
        $properties = " * {$collection}" . PHP_EOL;;
        try {
            $ret = \support\Db::connection('mongodb')->collection($collection)->first();
            if (!empty($ret)) {
                foreach ($ret as $key => $value) {
                    $type = gettype($value);
                    $properties .= " * @property $type \${$key} {$key}\n";
                    if ($key === '_id' && $type == 'object') {
                        $properties .= " * @property string \$id _id的string形式\n";
                    }
                }
            }
        } catch (\Throwable $e) {}
        $properties = rtrim($properties) ?: ' *';
        $model_content = <<<EOF
<?php

namespace $namespace;

use app\MongoModel;

/**
$properties
 */
class $class extends MongoModel
{
    /**
     * 模型关联的集合。
     *
     * @var string
     */
    protected \$collection = $collection_val;
    

}

EOF;
        file_put_contents($file, $model_content);
    }

}
