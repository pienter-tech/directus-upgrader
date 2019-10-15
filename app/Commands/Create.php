<?php

namespace DirectusTools\Commands;

use DirectusTools\Arguments\Common;
use DirectusTools\Commands\Helpers\DotEnvCommands;
use DirectusTools\Commands\Helpers\CloneCommands;
use DirectusTools\Commands\Helpers\GitIgnoreCommands;
use DirectusTools\Commands\Helpers\ComposerCommands;
use DirectusTools\Exceptions\ArgumentNotFoundException;
use DirectusTools\Exceptions\FileException;
use DirectusTools\Exceptions\RunException;
use League\CLImate\Argument\Manager;

class Create extends CommandClass
{
    use DotEnvCommands, ComposerCommands, CloneCommands, GitIgnoreCommands;
    /** @var string */
    private $root;
    /** @var string */
    private $name;
    /** @var bool */
    private $dotEnv;
    /** @var bool */
    private $composer;
    /** @var bool */
    private $quiet;

    /**
     * @return  string
     */
    static function name()
    {
        return 'create';
    }

    /**
     * @return  array
     * @throws ArgumentNotFoundException
     */
    static function arguments()
    {
        return [
            'root' => Common::getArgument('root'),
            'dotEnv' => Common::getArgument('dotEnv'),
            'quiet' => Common::getArgument('quiet'),
            'name' => [
                'prefix' => 'n',
                'longPrefix' => 'name',
                'description' => 'Set project name',
                'castTo' => 'string',
                'defaultValue' => '',
            ],
        ];
    }

    /**
     * @param Manager $arguments
     * @return void
     */
    public function setArguments($arguments)
    {
        $this->name = empty($arguments->get('name')) ? $arguments->get('name') : 'new-directus-project';
        if (empty($arguments->get('root'))) {
            $this->root = getcwd() . "/" . $this->name;
        } else {
            $this->root = $arguments->get('root') . '/' . $this->name;
        }

        $this->dotEnv = $arguments->get('dotEnv');
        $this->quiet = $arguments->get('quiet');
    }

    /**
     * @return void
     * @throws RunException
     * @throws FileException
     */
    public function run()
    {
        $this->updateDirectus($this->root);
        $customComposerContent = [
            'name' => $this->name,
            'description' => 'Project created by directus tools',
            'require' => [],
        ];
        if ($this->dotEnv) {
            $this->addDotenv();
            $customComposerContent['require']['vlucas/phpdotenv'] = '^3.6';
        }

        $this->createCustomComposer($customComposerContent);

        $this->info();
    }

    public function info()
    {
        $this->cli->info('Run composer update');
    }
}
