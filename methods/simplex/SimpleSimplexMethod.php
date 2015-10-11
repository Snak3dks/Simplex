<?php
require_once 'Simplex.php';

class SimpleSimplexMethod extends Simplex
{
    function __construct($function_vars, $limitations, $vars_count, $lims_count)
    {
        $this->vars_count = (int)$vars_count;
        $this->lims_count = (int)$lims_count;

        $this->allVarsCount = $this->vars_count + $this->lims_count;

        $this->function_vars = $function_vars;
        $this->limitations = $limitations;

        if ($this->checkInputData()) {
            $this->run();
        }
    }

    /** checking input data for needed
     * @return mixed
     */
    function checkInputData()
    {
        $isPositiveMarks = false;
        $this->makeMarks();
        foreach ($this->marks as $mark) {
            if ($mark > 0) {
                $isPositiveMarks = true;
                break;
            }
        }

        if ($isPositiveMarks) {
            $this->makeVariables();
            if ($this->checkLimitsByMark() && $this->checkMembers()) {
                $this->buildFirstTable();
            }
        } else {
            $this->error_msg = "Серед оцінок немає жодної додатньої.";
            return false;
        }

        return true;
    }

    /** check elements by mark row
     * @return bool
     */
    function checkLimitsByMark()
    {
        $onePositive = false;
        for ($i = 0; $i < $this->lims_count; $i++) {
            $onePositive = false;
            for ($j = 0; $j < $this->vars_count; $j++) {
                if ($this->limitations[$i]['X'][$j] >= 0 && $this->marks[$j] > 0) {
                    $onePositive = true;
                }
            }
        }
        if (!$onePositive) {
            $this->error_msg = "МПР задачі необмежена знизу!";
            return false;
        }
        return true;
    }

    /** checking free members for conditions
     * @return mixed
     */
    function checkMembers()
    {
        $isPositive = false;
        foreach ($this->limitations as $limit) {
            if ($limit['member'] > 0) {
                $isPositive = true;
                break;
            }
        }

        if (!$isPositive) {
            $this->error_msg = "Немає жодного додатнього вільного члену!";
            return false;
        }

        return true;
    }

    function getResultelement()
    {
        for ($i = 0; $i < $this->allVarsCount; $i++) {
            $mark = $this->matrix[$this->lims_count][$i];
            if ($mark->getNum() <= 0) {
                continue;
            }
            $absMax = null;
            if (!is_null($this->inCol["index"])) {
                $absMax = Fraction::subtract(new Fraction(($mark->getNum()), $mark->getDenom()),
                    new Fraction(($this->inCol["value"]->getNum()), $this->inCol["value"]->getDenom()));
            }

            if (is_null($this->inCol["index"]) || $absMax->getNum() > 0) {
                $this->inCol["index"] = $i;
                $this->inCol["value"] = $mark;
            }
        }

        for ($i = 0; $i < $this->lims_count; $i++) {
            $free_member = $this->matrix[$i][$this->allVarsCount];
            if ($free_member->getNum() <= 0) {
                continue;
            }

            $relation = Fraction::divide($free_member, $this->matrix[$i][$this->inCol["index"]]);

            $absMin = null;
            if (!is_null($this->outRow["index"])) {
                $absMin = Fraction::subtract($this->outRow["value"], $relation);
            }
            if (is_null($this->outRow["index"]) || $absMin->getNum() > 0) {
                $this->outRow["index"] = $i;
                $this->outRow["value"] = $relation;
            }
        }
    }

    /** check conditions for getting resolve status
     * @return mixed
     */
    function checkForResolve()
    {
        $atLeastOnePositiveMark = false;
        for ($i = 0; $i < $this->allVarsCount; $i++) {
            if($this->matrix[$this->lims_count][$i]->getNum() > 0){
                $atLeastOnePositiveMark = true;
            }
        }

        if (!$atLeastOnePositiveMark){
            return true;
        }

        $positive = false;
        for ($i = 0; $i < $this->allVarsCount; $i++) {
            if($this->matrix[$this->lims_count][$i]->getNum() > 0){
                for ($j = 0; $j < $this->lims_count; $j++) {
                    if($this->matrix[$j][$i]->getNum() > 0){
                        $positive = true;
                        break;
                    }
                }
            }
        }

        if (!$positive) {
            $this->error_msg = "МПР початкової задачі порожня!";
            return false;
        }

        return false;
    }

}