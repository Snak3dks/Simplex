<?php

class Fraction
{
    private $num = null;
    private $denom = null;
    private $number = null;

    function __construct($num, $denom = 1, $multiplicity = true)
    {
        if ($denom == 0) return null;
        $this->num = $num;
        $this->denom = $denom;
        //if($multiplicity){
            $this->checkMultiplicity();
        //}
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
                $class = " pad-left";
            }
            else{
                $class = "";
                $sign = "";
                $num = $this->num;
            }
            return '<div class="fraction'. $class .'">
			            <div class="num">
				            <div class="sign">'. $sign .'</div>' . $num . '
			            </div>
			            <div class="denom">' . $this->denom . '</div>
		            </div>';
            //return $this->num . "/" . $this->denom;
        }
    }

    /** get reduced var (integer, fractional)
     * @param bool|true $getFull
     * @return array|bool|Fraction|null
     */
    public function getReduced($getFull = true){
        if($this->num <- 0){
            return new Fraction($this->denom - abs($this->num), $this->denom, false);
        }

        $integer = (int)($this->num / $this->denom);
        if(($integer) != 0){
            $newNum = ($integer * $this->denom);
            $integerFraction = new Fraction($newNum, $this->denom, false);
            $fracational = self::subtract(new Fraction($this->num, $this->denom), $integerFraction);
        }
        else{
            return new Fraction($this->num, $this->denom, false);
        }

        if($getFull)
            return array('integer' => $integer, 'fractional' => $fracational);
        else
            return $fracational;
    }

    static function add(Fraction $a, Fraction $b, $multiplicity = true)
    {
        if (empty($a->denom) || empty($b->denom))
            return null;

        $denom = self::nok($a->denom, $b->denom);
        return new Fraction((($denom / $a->denom) * $a->num) + (($denom / $b->denom) * $b->num), $denom, $multiplicity);
    }

    static function subtract(Fraction $a, Fraction $b)
    {
        if (empty($a->denom) || empty($b->denom))
            return null;

        $denom = self::nok($a->denom, $b->denom);

        if ($a->num < 0 && $b->num < 0)
            return new Fraction(((($denom / $a->denom) * $a->num) + (($denom / $b->denom) * $b->num)), $denom, false);
        
        return new Fraction((($denom / $a->denom) * $a->num) - (($denom / $b->denom) * $b->num), $denom, false);
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

    /*
     * @param Fraction objects
     * return minimum of fractions
     */
    static function min(Fraction $a, Fraction $b)
    {
        if($a->getNum() > 0 && $b->getNum() > 0)
        {
            if(self::subtract($a, $b)->getNum() <= 0)
                return $a;
            else
                return $b;
        }
        elseif($a->getNum() < 0 && $b->getNum() < 0)
        {
            if(self::subtract(new Fraction($a->getNum()*(-1), $a->getDenom()),
                    new Fraction($b->getNum()*(-1), $b->getDenom()))->getNum() > 0 )
                return $a;
            else
                return $b;
        }
        elseif($a->getNum() < 0 )
            return -1;
        //return $a;
        else
            return 1;
        //return $b;
    }

    /*
     * @param Fraction objects
     * return maximum of fractions
     */
    static function max(Fraction $a, Fraction $b)
    {
        if($a->getNum() > 0 && $b->getNum() > 0)
        {
            if(self::subtract($a, $b)->getNum() > 0)
                return -1;
            //return $a;
            else
                return 1;
            //return $a;
        }
        elseif($a->getNum() < 0 && $b->getNum() < 0)
        {
            if(self::subtract(new Fraction($a->getNum()*(-1), $a->getDenom()),
                    new Fraction($b->getNum()*(-1), $b->getDenom()))->getNum() <= 0 )
                return -1;
            //return $a;
            else
                return 1;
            //return $a;
        }
        elseif($a->getNum() > 0 )
            return -1;
        //return $a;
        else
            return 1;
        //return $a;
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