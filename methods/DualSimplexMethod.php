<?php
require_once("Fraction.php");

class aDualSimplexMethod
{
    public $error_msg = "";
    public $html = "";

    private $limitations = array();
    private $func_factors = array();
    private $marks = array();

    private $basisCount = 0;
    private $varCount = 0;
    //basis + main vars
    private $allVarCount = 0;
    //індекс в.ч. в масиві
    private $vchIndex = 0;

    //головний масив для операцій
    private $matrix = array();
    //індекти базисів для побудови
    private $basis = array();

    public function __construct($func_factors, $limitations, $varCount, $basisCount)
    {
//        $func_factors = [6, 8, 1, 1];
//        $limitations = [["X"=>[1,2,-1,0],"inequality"=>"more", "member"=>3],
//                        ["X"=>[2,1,1,1],"inequality"=>"less", "member"=>2]];
        $this->limitations = $limitations;
        $this->func_factors = $func_factors;

        //test
//        $this->varCount = 4;
//        $this->basisCount = 2;

        $this->varCount = (int)$varCount;
        $this->basisCount = (int)$basisCount;

        $this->allVarCount = $this->varCount + $this->basisCount;
        $this->vchIndex = $this->allVarCount;

        //1)
        if($this->checkInputData()) //+
        {
            $this->makeVariables(); //+
            if($this->checkMembers())//+
            {
                $this->buildFirstTable();//+
                $this->buildTables();
                /*if($this->buildTables())
                    $this->html .= "<h3>f(x*) = ". $this->matrix[$this->basisCount][$this->vchIndex]->show() ."</h3>";*/
            }
        }
    }

    //перевірка на наявність від'ємних коеф. ф-ції цілі
    private function checkInputData()
    {
        foreach ($this->func_factors as $factor)
        {
            if($factor < 0)
            {
                $this->error_msg = "Один з коефіцієнтів f(x) < 0";
                return false;
            }
        }

        return true;
    }

    //перевірка на наявність хоча б одиного в.ч., в котрого є хоча б один від'ємний коефіцієнт
    private function checkMembers()
    {
        $atLeastOneNegativeMember = false;
        $oneNegativeFactor = false;

        foreach ($this->limitations as &$limit)
        {
            if($limit['X'][$this->vchIndex] < 0)
            {
                $atLeastOneNegativeMember = true;
                for($i = 0; $i < $this->allVarCount; $i++)
                {
                    if((int)$limit['X'][$i] < 0)
                    {
                        $oneNegativeFactor = true;
                        break;
                    }
                }

                if(!$oneNegativeFactor)
                {
                    $this->error_msg = "МПР задачі порожня!";
                    return false;
                }
            }
        }

        if(!$atLeastOneNegativeMember)
        {
            $this->error_msg = "Немає жодного від-ємного вільного члену!";
            return false;
        }

        return true;
    }

    //додає в кінець базисну змінну
    //якщо >=, міняє знак на протилежний
    private function makeVariables()
    {
        $basisIndex = 0;
        $cells = array_fill($this->varCount, count($this->limitations), 0);
        $this->allVarCount = $this->varCount + $this->basisCount;

        //додаємо оцінки, остання в масиві буде містити значення f(x)
        $this->marks = array_merge($this->func_factors, $cells, array(0));

        foreach ($this->limitations as &$limit)
        {
            $limit['member'] = (int)$limit['member'];

            $limit['X'] = array_merge($limit['X'], $cells);

            //в кінці масиву будемо зберігати в.ч. для зручності операцій
            $limit['X'][] = $limit['member'];

            //якщо знак обмеження >=
            if($limit['inequality'] == "more")
            {
                //змінюється знак
                array_walk($limit['X'], function(&$value){
                    $value *= (-1);
                });
            }

            //додається базисна змінна
            $index = $this->varCount + $basisIndex;
            $limit['X'][$index] = 1;

            //костиль, треба для побудови і-ї таблиці, лише для гарного виводу
            $this->basis[$basisIndex] = $index;

            //потрібно для побудови першої таблиці
            $limit['basis'] = ++$index;

            $basisIndex++;

        }
    }

    private function buildTables()
    {
        // створення єдиної матриці для роботи та перетворення всіх значень в дроби
        $this->limitations[]["X"] = $this->marks;
        foreach ($this->limitations as $limit)
        {
            $row = array();
            for($i = 0; $i < $this->allVarCount + 1; $i++)
                $row[] = new Fraction($limit["X"][$i]);

            $this->matrix[] = $row;
        }

        while($this->mainCircle())
        {
            //побудова поточної таблиці
            $this->buildCurrentTable();

            $atLeastOneNegativeVCH = false;
            //перевірка на знаходження розв'язку
            for($i = 0; $i < $this->basisCount; $i++)
                if($this->matrix[$i][$this->vchIndex]->getNum() < 0)
                    $atLeastOneNegativeVCH = true;

            if(!$atLeastOneNegativeVCH)
                return true;

            //перевірка на порожню множини
            $negative = false;
            for($i = 0; $i < $this->basisCount; $i++)
                if($this->matrix[$i][$this->vchIndex]->getNum() < 0)
                    for($j = 0; $j < $this->allVarCount; $j++)
                        if($this->matrix[$i][$j]->getNum() < 0)
                        {
                            $negative = true;
                            break;
                        }


            if(!$negative)
            {
                $this->error_msg = "МПР початкової задачі порожня!";
                return false;
            }

        }

        return true;
    }

    // головний цикл
    private function mainCircle()
    {

        $minRow = array("index"=>null, "value"=>null);
        $minCol = array("index"=>null, "value"=>null);

        //2) Знаходиться рядок, що виводиться з базису
        for($i = 0; $i < $this->basisCount; $i++)
        {
            // в.ч. рядка
            $vch = $this->matrix[$i][$this->vchIndex];

            if($vch->getNum() >= 0)
                continue;

            if(!is_null($minRow["index"]))
            {

                $absMin = Fraction::subtract(new Fraction(($vch->getNum()*(-1)), $vch->getDenom()),
                    new Fraction(($minRow["value"]->getNum()*(-1)), $minRow["value"]->getDenom()));
            }


            if(is_null($minRow["index"]) || $absMin->getNum() > 0)
            {
                $minRow["index"] = $i;
                $minRow["value"] = $vch;
            }
        }

        //3) Знаходження стовпчика, що буде вводитись в базис
        for($i = 0; $i < $this->allVarCount; $i++)
        {
            $element = $this->matrix[$minRow["index"]][$i];

            if($element->getNum() >= 0)
                continue;

            $relation = Fraction::divide($this->matrix[$this->basisCount][$i], $element);

            if(!is_null($minCol["index"]))
                $absMin = Fraction::subtract($relation, $minCol["value"]);

            if(is_null($minCol["index"]) || $absMin->getNum() < 0)
            {
                $minCol["index"] = $i;
                $minCol["value"] = $relation;
            }
        }

        //для побудови
        $this->basis[$minRow['index']] = $minCol['index'];

        //4) перетворення як в прямому
        //4.1) створюємо одиницю в розв'язуючому рядку
        //роз'язуючий елемент
        $resultElem = new Fraction($this->matrix[$minRow['index']][$minCol["index"]]->getNum(),
            $this->matrix[$minRow['index']][$minCol["index"]]->getDenom());
        foreach ($this->matrix[$minRow['index']] as &$val)
        {
            $val = Fraction::divide($val, $resultElem);
        }

        for($i = 0; $i < $this->basisCount + 1; $i++)
        {
            if($i == $minRow['index'])
                continue;

            $futureNull = $this->matrix[$i][$minCol['index']];

            $sign = $futureNull->getNum() < 0 ? 1 : -1;
            $num = abs($futureNull->getNum());
            $denom = $futureNull->getDenom();

            for($j = 0; $j < $this->allVarCount + 1; $j++)
                $this->matrix[$i][$j] = Fraction::add(
                    $this->matrix[$i][$j],
                    new Fraction($this->matrix[$minRow['index']][$j]->getNum()*$sign*$num, $this->matrix[$minRow['index']][$j]->getDenom()*$denom)
                );
        }

        return true;
    }

    //побудова i-ї таблиці
    private function buildCurrentTable()
    {
        $html = "<table><tr><th>Базис</th>";
        for($i = 0; $i < $this->allVarCount; $i++)
        {
            $html .= "<th>X". ($i + 1) ."</th>";
        }
        $html .= "<th>В.Ч</th></tr>";

        $i = 0;
        //рядок з базисом
        foreach ($this->matrix as $rowKey => $row)
        {
            if($i != $this->basisCount)
                $html .= "<tr><th>X". ($this->basis[$i] + 1) ."</th>";
            else
                $html .= "<tr><th>ƒ</th>";


            foreach ($row as $cellKey => $cell) {
                $html .= "<td>". $cell->show() ."</td>";
            }

            $i++;
        }


        $html .= "</tr></table>";

        $this->html .= $html;
    }

    //побудова 0-ї симплекс таблиці
    private function buildFirstTable()
    {
        $html = "<table><tr><th>Базис</th>";

        for($i = 0; $i < $this->allVarCount; $i++)
        {
            $html .= "<th>X". ($i + 1) ."</th>";
        }

        $html .= "<th>В.Ч</th><th></th>";
        $html .= "</tr>";

        //рядок з базисом
        foreach ($this->limitations as $limit)
        {
            $html .= "<tr><th>X". $limit['basis'] ."</th>";
            foreach ($limit['X'] as $x)
            {
                $html .= "<td>$x</td>";
            }
            // дод. змінні ( в 2їстому завжди будуть 0лі)
            $html .= "<td class='option'>0</td></tr>";
        }

        //оцінки
        $html .= "<tr><th>ƒ</th>";
        foreach ($this->marks as &$mark)
        {
            $mark *= -1;
            $html .= "<td>$mark</td>";
        }

        //нижня добудова
        $html .= "</tr><tr><th></th>";
        for($i = 0; $i < count($this->marks) - 1; $i++)
        {
            $html .= "<td class='option'>". $this->marks[$i]*(-1) ."</td>";
        }

        $html .= "</tr></table>";

        $this->html = $html;
    }
}