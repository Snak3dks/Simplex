<?php
require_once 'Simplex.php';

class SimpleSimplexMethod extends Simplex
{
    protected $vars_count;
    protected $lims_count;
    protected $function_vars;
    protected $limitations;

    protected $marks;

    public $error_msg;


    function __construct($vars_count, $lims_count, $function_vars, $limitations)
    {
        $this->vars_count = $vars_count;
        $this->lims_count = $lims_count;
        $this->function_vars = $function_vars;
        $this->limitations = $limitations;
    }

    /** checking input data for needed
     * @return mixed
     */
    function checkInputData()
    {
        $this->marks = $this->makeMarks($this->function_vars);
        foreach ($this->marks as $mark)
        {
            if($mark > 0)
            {
                return true;
            }
        }
        $this->error_msg = "Серед оцінок немає жодної додатньої.";

        return true;
    }

    /** checking free members for conditions
     * @return mixed
     */
    function checkMembers()
    {
        // TODO: не має був від'ємних
        return true;
    }

    /** making working data-object
     * @return mixed
     */
    function makeVariables()
    {
        // TODO: Implement makeVariables() method.
    }

    /** checking if method resolved successfully
     * @return mixed
     */
    function checkForResolved()
    {
        // TODO: Implement checkForResolved() method.
    }
}