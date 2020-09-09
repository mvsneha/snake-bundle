<?php

namespace Dbu\SnakeBundle\Game;

use Symfony\Component\Console\Color;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Output\OutputInterface;

class Snake
{
    /**
     * @var Board
     */
    private $board;

    public const UP = 'up';
    public const RIGHT = 'right';
    public const DOWN = 'down';
    public const LEFT = 'left';

    private $direction = self::RIGHT;

    /**
     * @var Coordinate
     */
    private $head;

    /**
     * @var Coordinate[]
     */
    private $tail;

    /**
     * @var int
     */
    private $grow = 2;

    /**
     * @var int
     */
    private $color;

    public function __construct(Board $board, Coordinate $start)
    {
        $this->board = $board;
        $this->head = clone $start;
        $this->tail = [clone $start];
        $this->color = rand(0, 255);
    }

    public function setDirection(string $direction): void
    {
        if (self::UP === $this->direction && self::DOWN === $direction
            || self::DOWN === $this->direction && self::UP === $direction
            || self::LEFT === $this->direction && self::RIGHT === $direction
            || self::RIGHT === $this->direction && self::LEFT === $direction
        ) {
            // ignore direction reversal which would lead to immediate self-collision
            return;
        }
        $this->direction = $direction;
    }

    public function tick(OutputInterface $output, Cursor $cursor): void
    {
        switch ($this->direction) {
            case self::UP:
                $this->head->y--;
                break;
            case self::RIGHT:
                $this->head->x++;
                break;
            case self::DOWN:
                $this->head->y++;
                break;
            case self::LEFT:
                $this->head->x--;
                break;
        }

        $this->grow += $this->board->enter($this->head);

        $cursor->moveToPosition($this->head->x, $this->head->y);
        $output->write($this->rainbowColor()->apply(' '));
        $this->tail[] = clone $this->head;
        if ($this->grow > 0) {
            $this->grow--;
        } else {
            $coord = array_shift($this->tail);
            $this->board->leave($coord);
            $cursor->moveToPosition($coord->x, $coord->y);
            $output->write('<background> </background>');
        }
    }

    public function addGrow(int $amount): void
    {
        $this->grow += $amount;
    }

    private function rainbowColor(): Color {
        ++$this->color;
        if (256 === $this->color) {
            $this->color = 1;
        }
        $h = (int) ($this->color / 43);
        $f = (int) ($this->color - 43 * $h);
        $t = (int) ($f * 255 / 43);
        $q = 255 - $t;

        if ($h == 0) {
            return new Color('', sprintf('#FF%02x00', $t));
        } elseif ($h == 1) {
            return new Color('', sprintf('#%02xFF00', $q));
        } elseif ($h == 2) {
            return new Color('', sprintf('#00FF%02x', $t));
        } elseif ($h == 3) {
            return new Color('', sprintf('#00%02xFF', $q));
        } elseif ($h == 4) {
            return new Color('', sprintf('#%02x00FF', $t));
        } elseif ($h == 5) {
            return new Color('', sprintf('#FF00%02x', $q));
        }
    }
}
