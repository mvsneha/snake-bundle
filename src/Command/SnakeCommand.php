<?php

namespace Dbu\SnakeBundle\Command;

use Dbu\SnakeBundle\Game\Game;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SnakeCommand extends Command
{
    protected static $defaultName = 'game:snake';

    private $game;

    public function __construct(Game $game = null)
    {
        $this->game = $game ?: new Game(80, 24);

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Run the snake game in Symfony console (only one more time...)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input instanceof StreamableInputInterface && $stream = $input->getStream()) {
            $inputStream = $stream;
        } else {
            $inputStream = STDIN;
        }

        stream_set_blocking($inputStream, FALSE);
        $sttyMode = shell_exec('stty -g');
        shell_exec('stty -icanon -echo');

        $cursor = new Cursor($output);
        $cursor->hide();

        $output->getFormatter()->setStyle('snake', new OutputFormatterStyle('green', 'black', ['bold']));
        $output->getFormatter()->setStyle('goal', new OutputFormatterStyle('yellow', 'black'));
        $output->getFormatter()->setStyle('background', new OutputFormatterStyle('cyan', 'black'));
        $output->getFormatter()->setStyle('crash', new OutputFormatterStyle('red', 'black', ['bold', 'blink']));

        $this->game->run($inputStream, $output, $cursor);

        $cursor->show();
        stream_set_blocking($inputStream, TRUE);
        shell_exec(sprintf('stty %s', $sttyMode));

        return 0;
    }
}
