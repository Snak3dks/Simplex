<?php

abstract class Simplex
{
    /** checking input data for needed
     * @return mixed
     */
    abstract function checkInputData();

    /** checking free members for conditions
     * @return mixed
     */
    abstract function checkMembers();

    /** making working data-object
     * @return mixed
     */
    abstract function makeVariables();

    /** checking if method resolved successfully
     * @return mixed
     */
    abstract function checkForResolved();

    /** making marks by multiplication function_vars on (-1)
     * @return array
     */
    function makeMarks($function_variables)
    {
        $marks = array();
        foreach ($function_variables as $var)
        {
            $marks[] = $var * (-1);
        }
        return $marks;
    }

    /**
     * building first table for UI
     * @return mixed
     */
    function buildFirstTable()
    {
        return true;
    }

    /** building simplex table
     * @return mixed
     */
    function buildTable()
    {
        return true;
    }
}