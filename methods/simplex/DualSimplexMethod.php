<?php
require_once 'Simplex.php';

class DualSimplexMethod extends Simplex
{
    function __construct($function_vars, $limitations, $vars_count, $lims_count)
    {
        $this->vars_count = (int)$vars_count;
        $this->lims_count = (int)$lims_count;

        $this->allVarsCount = $this->vars_count + $this->lims_count;

        $this->function_vars = $function_vars;
        $this->limitations = $limitations;

        if($this->checkInputData()){
            $this->run();
        }
    }

    function checkInputData()
    {
        foreach ($this->function_vars as $factor) {
            if ($factor < 0) {
                $this->error_msg = "Один з коефіцієнтів f(x) < 0";
                return false;
            }
        }

        $isPositiveMarks = false;
        $this->makeMarks();
        foreach ($this->marks as $mark) {
            if ($mark > 0) {
                $isPositiveMarks = true;
                break;
            }
        }

        if (!$isPositiveMarks) {
            $this->makeVariables();
            if ($this->checkMembers()) {
                $this->buildFirstTable();
            }
        } else {
            $this->error_msg = "Серед оцінок є додатні.";
            return false;
        }

        return true;
    }

    function checkMembers()
    {
        $atLeastOneNegativeMember = false;
        $oneNegativeFactor = false;

        foreach ($this->limitations as &$limit) {
            if ($limit['X'][$this->allVarsCount] < 0) {
                $atLeastOneNegativeMember = true;
                for ($i = 0; $i < $this->allVarsCount; $i++) {
                    if ((int)$limit['X'][$i] < 0) {
                        $oneNegativeFactor = true;
                        break;
                    }
                }

                if (!$oneNegativeFactor) {
                    $this->error_msg = "МПР задачі порожня!";
                    return false;
                }
            }
        }

        if (!$atLeastOneNegativeMember) {
            $this->error_msg = "Немає жодного від-ємного вільного члену!";
            return false;
        }

        return true;
    }

    function getResultelement()
    {
        for ($i = 0; $i < $this->lims_count; $i++) {
            $free_member = $this->matrix[$i][$this->allVarsCount];
            if ($free_member->getNum() >= 0) {
                continue;
            }
            $absMin = null;
            if (!is_null($this->outRow["index"])) {
                $absMin = Fraction::subtract(new Fraction(($free_member->getNum() * (-1)), $free_member->getDenom()),
                    new Fraction(($this->outRow["value"]->getNum() * (-1)), $this->outRow["value"]->getDenom()));
            }
            if (is_null($this->outRow["index"]) || $absMin->getNum() > 0) {
                $this->outRow["index"] = $i;
                $this->outRow["value"] = $free_member;
            }
        }
        for ($i = 0; $i < $this->allVarsCount; $i++) {
            $element = $this->matrix[$this->outRow["index"]][$i];
            if ($element->getNum() >= 0) {
                continue;
            }
            $relation = Fraction::divide($this->matrix[$this->lims_count][$i], $element);
            $absMin = null;
            if (!is_null($this->inCol["index"])) {
                $absMin = Fraction::subtract($relation, $this->inCol["value"]);
            }
            if (is_null($this->inCol["index"]) || $absMin->getNum() < 0) {
                $this->inCol["index"] = $i;
                $this->inCol["value"] = $relation;
            }
        }
    }

    /** check conditions for getting resolve status
     * @return mixed
     */
    function checkForResolve()
    {
        $atLeastOneNegativeVCH = false;
        for ($i = 0; $i < $this->lims_count; $i++) {
            if ($this->matrix[$i][$this->allVarsCount]->getNum() < 0) {
                $atLeastOneNegativeVCH = true;
            }
        }

        if (!$atLeastOneNegativeVCH){
            return true;
        }

        $negative = false;
        for ($i = 0; $i < $this->lims_count; $i++) {
            if ($this->matrix[$i][$this->allVarsCount]->getNum() < 0) {
                for ($j = 0; $j < $this->allVarsCount; $j++) {
                    if ($this->matrix[$i][$j]->getNum() < 0) {
                        $negative = true;
                        break;
                    }
                }
            }
        }

        if (!$negative) {
            $this->error_msg = "МПР початкової задачі порожня!";
            return false;
        }

        return false;
    }
}