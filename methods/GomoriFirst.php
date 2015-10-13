<?php

require_once('Fraction.php');
require_once('simplex/SimpleSimplexMethod.php');
require_once('simplex/DualSimplexMethod.php');

class GomoriFirst
{
    public $html = '';
    public $error_msg = '';

    function __construct($function_vars, $limitations, $vars_count, $lims_count)
    {
        /*$this->vars_count = (int)$vars_count;
        $this->lims_count = (int)$lims_count;
        $this->allVarsCount = $this->vars_count + $this->lims_count;
        $this->function_vars = $function_vars;
        $this->limitations = $limitations;*/

        $simplex = new SimpleSimplexMethod($function_vars, $limitations, $vars_count, $lims_count);
        $this->html = $simplex->html;
        $this->error_msg = $simplex->error_msg;

        $non_integer_vars = $this->checkForIntegerVars($simplex);
        if ($non_integer_vars == null) {
            $msg = 'Already optimal';
        } else {
            var_dump($non_integer_vars);

            $test = $simplex->matrix[$non_integer_vars[0]][$simplex->allVarsCount]->getReduced();
//            var_dump($test);

            if (count($non_integer_vars) == 1) {

            } else {

            }
        }
    }

    /** Check for integer options in simple-simplex result
     * @param $simplex
     * @return array|null
     */
    function checkForIntegerVars($simplex)
    {
        $simplex_matrix = $simplex->matrix;
        $simplex_allVarsCount = $simplex->allVarsCount;
        $simplex_limsCount = $simplex->lims_count;

        $non_integer_vars = null; // indexes of row with non-integer vars
        $i = 0;
        foreach ($simplex_matrix as $key => $row) {
            if ($i > $simplex_limsCount) {
                break;
            }
            if (is_numeric($row[$simplex_allVarsCount]->show())) {
                continue;
            } else {
                $non_integer_vars[] = $key;
            }
            $i++;
        }

        return $non_integer_vars;
    }

}