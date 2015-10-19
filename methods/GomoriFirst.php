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
            $simplex = new SimpleSimplexMethod($function_vars, $limitations, $vars_count, $lims_count);
            $this->html = $simplex->html;
            $this->error_msg = $simplex->error_msg;

            //echo $this->html;
            if($this->error_msg == ''){
                $this->run($simplex);
            }
        }

        function run($simplex_obj)
        {
            $non_integer_vars = $this->checkForIntegerVars($simplex_obj);
            if ($non_integer_vars == null)
            {
                return false;
            }
            else
            {
                $newMatrix = $this->buildCutOff($simplex_obj, $non_integer_vars);
                $newSimpleObj = new DualSimplexMethod();
                $newSimpleObj->runReady($newMatrix);

                $this->html .= $newSimpleObj->html;

                if(!$newSimpleObj){
                    $this->run($newSimpleObj);
                }
            }

            return true;
        }

        /** Check for integer options in simple-simplex result
         * @param $simplex_object
         * @return array|null
         */
        function checkForIntegerVars($simplex_object)
        {
            $matrix = $simplex_object->matrix;
            $allVarsCount = $simplex_object->allVarsCount;
            $limsCount = $simplex_object->lims_count;

            $non_integer_vars = null; // indexes of row with non-integer vars
            $i = 0;
            foreach ($matrix as $key => $row)
            {
                if ($i > $limsCount)
                {
                    break;
                }
                if (is_numeric($row[$allVarsCount]->show()))
                {
                    continue;
                }
                else
                {
                    $non_integer_vars[] = $key;
                }
                $i++;
            }

            return $non_integer_vars;
        }

        /**
         * @param $matrix
         * @param $non_int_vars
         * @return array
         */
        function buildCutOff($matrix, $non_int_vars)
        {
            // Getting non-integer free members fractional parts
            foreach ($non_int_vars as $var)
            {
                $max_fractional[$var] = $matrix->matrix[$var][$matrix->allVarsCount]->getReduced(false);
            }
            // Getting max of them
            uasort($max_fractional, 'Fraction::max');

            $first = current($max_fractional);
            // Getting correlations if it`s more then 1 equals max fractional parts
            foreach ($max_fractional as $key => $fractional)
            {
                if ($fractional == $first)
                {
                    $correlation = null;
                    for ($i = $matrix->lims_count; $i < $matrix->allVarsCount; $i++)
                    {
                        if ($correlation == null)
                        {
                            $correlation = Fraction::add($matrix->matrix[$key][$i]->getReduced(false), $matrix->matrix[$key][++$i]->getReduced(false), false);
                        }
                        if (($correlation != null) && (($i + 1) < $matrix->allVarsCount))
                        {
                            $correlation = Fraction::add($correlation, $matrix->matrix[$key][$i + 1]->getReduced(false), false);
                        }
                    }
                    $correlations[$key] = Fraction::divide($max_fractional[$key], $correlation);
                }
            }
            // Getting max correlation
            uasort($correlations, 'Fraction::max');
            // Getting generation row to build cutoff

            $generating_row = array_keys($correlations)[0]; //current($correlations);

            $newMatrix = $matrix->matrix;
            $allVarsCount = $matrix->allVarsCount + 1;
            $basis = $matrix->basis;
            $basis[] = $allVarsCount - 1;

            // Cutoff => new row and col in matrix
            foreach ($newMatrix as &$row)
            {
                $row[] = $row[$allVarsCount - 1];
                $row[$allVarsCount - 1] = new Fraction(0, 1);
            }
            $newMatrix[] = $newMatrix[$matrix->lims_count];

            foreach ($newMatrix[$matrix->lims_count] as $key => &$elem)
            {
                if (in_array($key, $basis) && $key != $allVarsCount - 1)
                {
                    $elem = new Fraction(0, 1);
                }
                elseif ($key == $allVarsCount - 1)
                {
                    $elem = new Fraction(1, 1);
                }
                else
                {
                    $elem = Fraction::multiply($newMatrix[$generating_row][$key], new Fraction(-1, 1));
                }
            }
            $newMatrix[$matrix->lims_count][$allVarsCount] = Fraction::multiply($first, new Fraction(-1, 1));
            $lims_count = $matrix->lims_count + 1;

            return (object)array('matrix' => $newMatrix, 'allVarsCount' => $allVarsCount, 'lims_count' => $lims_count, 'basis' => $basis);
        }
    }