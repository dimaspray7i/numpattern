<?php

namespace App\Services;

/**
 * PatternService
 *
 * Generates number sequences for the game.
 * Answer is computed server-side and returned with the sequence,
 * but only the sequence is sent to the client.
 *
 * Pattern types:
 *   - arithmetic   : a, a+d, a+2d, ...
 *   - geometric    : a, a*r, a*r², ...
 *   - incremental  : differences themselves increase: +2, +4, +6, ...
 *   - mixed        : combination (hard only)
 */
class PatternService
{
    // ─── PUBLIC ──────────────────────────────────

    /**
     * Generate a question array with sequence, correct answer, and type.
     *
     * @param  string  $difficulty  easy|medium|hard
     * @return array{ sequence: int[], answer: int, type: string }
     */
    public function generate(string $difficulty): array
    {
        $generators = match ($difficulty) {
            'easy'   => ['arithmetic', 'geometric'],
            'medium' => ['arithmetic', 'geometric', 'incremental'],
            'hard'   => ['arithmetic', 'geometric', 'incremental', 'mixed'],
            default  => ['arithmetic'],
        };

        $type = $generators[array_rand($generators)];

        return match ($type) {
            'arithmetic'  => $this->arithmetic($difficulty),
            'geometric'   => $this->geometric($difficulty),
            'incremental' => $this->incremental($difficulty),
            'mixed'       => $this->mixed(),
            default       => $this->arithmetic($difficulty),
        };
    }

    /**
     * Adjust difficulty based on streaks.
     * correct_streak >= 3 → upgrade
     * wrong_streak   >= 2 → downgrade
     */
    public function adjustDifficulty(string $current, int $correctStreak, int $wrongStreak): string
    {
        $levels = ['easy', 'medium', 'hard'];
        $index  = array_search($current, $levels);

        if ($correctStreak >= 3 && $index < 2) {
            return $levels[$index + 1];
        }

        if ($wrongStreak >= 2 && $index > 0) {
            return $levels[$index - 1];
        }

        return $current;
    }

    // ─── GENERATORS ──────────────────────────────

    private function arithmetic(string $difficulty): array
    {
        [$minStart, $maxStart, $minDiff, $maxDiff] = match ($difficulty) {
            'easy'   => [1,  20,  1,  5],
            'medium' => [5,  50,  3,  15],
            'hard'   => [10, 200, 10, 50],
            default  => [1,  20,  1,  5],
        };

        $start = rand($minStart, $maxStart);
        $d     = rand($minDiff, $maxDiff) * (rand(0, 1) ? 1 : -1);

        // Ensure sequence stays positive
        if ($start + $d * 4 < 0) $d = abs($d);

        $length   = rand(4, 6);
        $sequence = [];
        for ($i = 0; $i < $length; $i++) {
            $sequence[] = $start + $d * $i;
        }

        $answer = $start + $d * $length;

        return ['sequence' => $sequence, 'answer' => $answer, 'type' => 'arithmetic'];
    }

    private function geometric(string $difficulty): array
    {
        [$minStart, $maxStart, $ratios] = match ($difficulty) {
            'easy'   => [1, 5,  [2, 3]],
            'medium' => [1, 10, [2, 3, 4]],
            'hard'   => [1, 20, [2, 3, 4, 5]],
            default  => [1, 5,  [2, 3]],
        };

        $start    = rand($minStart, $maxStart);
        $ratio    = $ratios[array_rand($ratios)];
        $length   = rand(4, 5);
        $sequence = [];

        for ($i = 0; $i < $length; $i++) {
            $sequence[] = (int) round($start * pow($ratio, $i));
        }

        $answer = (int) round($start * pow($ratio, $length));

        return ['sequence' => $sequence, 'answer' => $answer, 'type' => 'geometric'];
    }

    private function incremental(string $difficulty): array
    {
        [$minStart, $maxStart, $step] = match ($difficulty) {
            'easy'   => [1,  10, rand(1, 2)],
            'medium' => [5,  30, rand(2, 4)],
            'hard'   => [10, 80, rand(5, 10)],
            default  => [1,  10, 1],
        };

        // Differences: step, step*2, step*3, ...
        $start    = rand($minStart, $maxStart);
        $length   = rand(4, 6);
        $sequence = [$start];
        $current  = $start;

        for ($i = 1; $i < $length; $i++) {
            $current    += $step * $i;
            $sequence[]  = $current;
        }

        $answer = $current + $step * $length;

        return ['sequence' => $sequence, 'answer' => $answer, 'type' => 'incremental'];
    }

    private function mixed(): array
    {
        // Odd-position: arithmetic, even-position: different arithmetic (two interleaved)
        $a1 = rand(10, 50);
        $d1 = rand(5, 20);
        $a2 = rand(10, 50);
        $d2 = rand(5, 20);

        // 6-element interleaved, question mark on 7th
        $sequence = [
            $a1,
            $a2,
            $a1 + $d1,
            $a2 + $d2,
            $a1 + 2 * $d1,
            $a2 + 2 * $d2,
        ];

        // Answer: next in first series (odd index)
        $answer = $a1 + 3 * $d1;

        return ['sequence' => $sequence, 'answer' => $answer, 'type' => 'mixed'];
    }
}
