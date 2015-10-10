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

        $this->checkInputData();
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
            if ($this->checkLimitsByMark() && $this->checkMembers()) {
                $this->makeVariables();
                $this->buildFirstTable();
                echo $this->html;
                $this->run();
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
        foreach ($this->limitations as $limit) {
            if ($limit['member'] < 0) {
                $this->error_msg = "Немає жодного додатнього вільного члену!";
                return false;
            }
        }

        return true;
    }

    /** finding row which will go out of basis
     * @return mixed
     */
    function findOutRow()
    {
        // TODO: Implement findOutRow() method.
    }

    /** finding column which will go into basis
     * @return mixed
     */
    function findInCol()
    {
        // TODO: Implement findInCol() method.
    }

    function checkForResolve()
    {
        // TODO: Implement checkForResolve() method.
    }
}