<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class evalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:evalCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '公式計算';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            $expression = $this->ask('請輸入要計算的數學表達式');

            if (!$expression) {

                throw new \Exception("沒有參數");
            }

            //乘號統一更換
            $expression = str_replace(["x", "X"], "*", $expression);

            //空格去除
            $expression = str_replace(" ", "", $expression);

            preg_match_all('/(\+|\-|\*|\/)/', $expression, $matchesSymbol);
            preg_match_all('/\d+/', $expression, $matchesNumber);

            if (count($matchesSymbol[0]) >= count($matchesNumber[0])) {
                
                throw new \Exception("表達式符號填寫有誤");
            }

            //匹配其他字符
            $pregResult = preg_match('/[A-Za-z]+/', $expression);

            if ($pregResult > 0) {

                throw new \Exception("表達式含有其它字符");
            }

            //匹配括號內容并計算
            $expression = $this->bracket($expression);

            //計算括號后匹配符號
            preg_match_all('/(\+|\-|\*|\/)/', $expression, $matchesSymbol);

            $matchesSymbol = $matchesSymbol[0];

            //計算括號后匹配數字
            preg_match_all('/\d+/', $expression, $matchesNumber);

            $matchesNumber = $matchesNumber[0];

            $expression2 = "";
            foreach ($matchesNumber as $kf => $kv) {

                if (isset($matchesSymbol[$kf])) {

                    if ($matchesSymbol[$kf] === "*" || $matchesSymbol[$kf] === "/") {

                        if (in_array("+", $matchesSymbol) || in_array("-", $matchesSymbol)) {

                            $expression2 .= "(";
                        }

                        $expression2 .= "{$kv}{$matchesSymbol[$kf]}";

                    } elseif ($kf > 0 && ($matchesSymbol[$kf] === "+" || $matchesSymbol[$kf] === "-")) {

                        //是否補上後面的括號
                        if ($matchesSymbol[$kf - 1] === "*" || $matchesSymbol[$kf - 1] === "/") {

                            $expression2 .= "{$kv}){$matchesSymbol[$kf]}";

                        } else {

                            $expression2 .= "{$kv}{$matchesSymbol[$kf]}";
                        }

                    } else {

                        $expression2 .= "{$kv}{$matchesSymbol[$kf]}";
                    }
                } else {

                    $expression2 .= $kv;
                    if ($matchesSymbol[$kf - 1] === "*" || $matchesSymbol[$kf - 1] === "/") {

                        $expression2 .= ")";
                    }
                }
            }

            if (!strpos($expression2, "+") || !strpos($expression2, "+")) {
                $expression2 = substr($expression2, 0, -1);
            }

            $expression2 = $this->bracket($expression2);

            //乘除已計算完畢，剩下加減
            $resultLast = $this->calculate($expression2);

            $this->info("計算結果： " . $resultLast);

        } catch (\Exception $e) {

            $this->info("計算表達式或結果有誤：" . $e->getMessage());
        }
    }

    private function calculate($expression)
    {
        if (strpos($expression, '+')) {

            return $this->add(explode('+', $expression));

        } elseif (strpos($expression, '-')) {

            return $this->subtract(explode('-', $expression));

        } elseif (strpos($expression, '*')) {

            return $this->multiply(explode('*', $expression));

        } elseif (strpos($expression, '/')) {

            return $this->divide(explode('/', $expression));

        } else {

            return $expression;
        }
    }

    private function add(array $numbers)
    {
        $sum = 0;
        foreach ($numbers as $number) {

            $sum += $number;
        }
        return $sum;
    }

    private function subtract(array $numbers)
    {
        $result = $numbers[0];
        for ($i = 1; $i < count($numbers); $i++) {

            $result -= $numbers[$i];
        }
        return $result;
    }

    private function multiply(array $numbers)
    {
        $result = $numbers[0];
        for ($i = 1; $i < count($numbers); $i++) {

            $result *= $numbers[$i];
        }
        return $result;
    }

    private function divide(array $numbers)
    {
        $result = $numbers[0];
        for ($i = 1; $i < count($numbers); $i++) {

            $result /= $numbers[$i];
        }
        return $result;
    }

    private function bracket($expression)
    {
        preg_match_all('/\(([^)]+)\)/', $expression, $matches);
        foreach ($matches[1] as $match) {

            $result = $this->calculate($match);
            $expression = str_replace('(' . $match . ')', $result, $expression);
        }
        return $expression;
    }
}
