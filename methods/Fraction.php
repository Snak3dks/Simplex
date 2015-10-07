<?php

class Fraction
{
    private $num = null;
    private $denom = null;
    private $number = null;

    function __construct($num, $denom = 1)
    {
        if ($denom == 0) return null;
        $this->num = $num;
        $this->denom = $denom;
        $this->checkMultiplicity();
    }

    public function getNum()
    {
        return $this->num;
    }

    public function getDenom()
    {
        return $this->denom;
    }

    public function show()
    {
        if (!is_null($this->number))
            return $this->number;
        else {
            if($this->num < 0) {
                $num = $this->num * (-1);
                $sign = "-";
            }
            else{
                $sign = "";
                $num = $this->num;
            }
            return '<span class="fraction">
                        <p class="unit">'. $sign .'</p>
                        <span class="numerator">' . $num . '</span>
                        <span class="denominator">' . $this->denom . '</span>
                    </span>';
            //return $this->num . "/" . $this->denom;
        }
    }


    static function add(Fraction $a, Fraction $b)
    {
        if (empty($a->denom) || empty($b->denom))
            return null;

        $denom = self::nok($a->denom, $b->denom);
        return new Fraction((($denom / $a->denom) * $a->num) + (($denom / $b->denom) * $b->num), $denom);
    }

    static function subtract(Fraction $a, Fraction $b)
    {
        if (empty($a->denom) || empty($b->denom))
            return null;

        $denom = self::nok($a->denom, $b->denom);

        if ($a->num < 0 && $b->num < 0)
            return new Fraction(((($denom / $a->denom) * $a->num) + (($denom / $b->denom) * $b->num)), $denom);

        return new Fraction((($denom / $a->denom) * $a->num) - (($denom / $b->denom) * $b->num), $denom);
    }

    static function multiply(Fraction $a, Fraction $b)
    {
        if (empty($a->denom) || empty($b->denom))
            return null;

        return new Fraction(($a->num * $b->num), ($a->denom * $b->denom));
    }

    static function divide(Fraction $a, Fraction $b)
    {
        if (empty($a->denom) || empty($b->denom))
            return null;

        if (($a->num >= 0 && $b->num < 0) || ($a->num <= 0 && $b->num < 0))
            return new Fraction(($a->num * $b->denom) * (-1), ($a->denom * $b->num) * (-1));

        return new Fraction(($a->num * $b->denom), ($a->denom * $b->num));
    }

    /**
     * @param $a , $b - numeric
     * @return int
     */
    function nod($a, $b)
    {
        $a = abs($a);
        $b = abs($b);

        $nod = null;
        while ($a <> 0 && $b <> 0) {
            if ($a > $b)
                $a = $a % $b;
            else
                $b = $b % $a;
            $nod = $a + $b;
        }
        return $nod;
    }

    /**
     * @param $a , $b - numeric
     * @return int, -1 if not found
     */
    function nok($a, $b)
    {
        $a = abs($a);
        $b = abs($b);

        $count = ($a > $b) ? $a : $b;

        for ($i = $count; ; $i++)
            if (!($i % $a) && !($i % $b))
                return $i;

        return -1;
    }

    private function checkMultiplicity()
    {
        if (is_int($this->num / $this->denom)) {
            $this->num /= $this->denom;
            $this->denom /= $this->denom;

            if (($this->number = $this->num / $this->denom) == 1) {
                $this->num = 1;
                $this->denom = 1;
            }
        } else {
            $nod = $this->nod($this->num, $this->denom);
            $this->num /= $nod;
            $this->denom /= $nod;
        }
    }
}