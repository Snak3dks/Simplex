<?php
require_once('/../Fraction.php');

abstract class Simplex
{
    public $html;
    public $error_msg;

    protected $vars_count;
    protected $lims_count;
    protected $function_vars;
    protected $limitations;

    protected $allVarsCount;
    protected $marks;
    protected $basis;
    protected $matrix;

    protected $outRow;
    protected $inCol;

    /** checking input data for needed
     * @return mixed
     */
    abstract function checkInputData();

    /** checking free members for conditions
     * @return mixed
     */
    abstract function checkMembers();

    /** getting result element for next step
     * @return mixed
     */
    abstract function getResultElement();

    /** check conditions for getting resolve status
     * @return mixed
     */
    abstract function checkForResolve();

    /**
     * making marks by multiplication function_vars on (-1)
     */
    function makeMarks()
    {
        $marks = array();
        foreach ($this->function_vars as &$var) {
            $var = (int)$var;
            $marks[] = $var * (-1);
        }
        $this->marks = $marks;
    }

    /** making working data-object
     * @return mixed
     */
    function makeVariables()
    {
        $basisIndex = 0;
        $cells = array_fill($this->vars_count, count($this->limitations), 0);
        $this->marks = array_merge($this->marks, $cells, array(0));

        foreach ($this->limitations as &$limit) {
            $limit['member'] = (int)$limit['member'];
            $limit['X'] = array_merge($limit['X'], $cells);

            $limit['X'][] = $limit['member'];

            if ($limit['inequality'] == "more") {
                array_walk($limit['X'], function (&$value) {
                    $value *= (-1);
                });
            }

            // adding basis variable
            $index = $this->vars_count + $basisIndex;
            $limit['X'][$index] = 1;

            #region First Table Build
            $this->basis[$basisIndex] = $index;
            $limit['basis'] = ++$index;
            #endregion

            $basisIndex++;
        }
    }

    /**
     * building first table for UI
     * @return mixed
     */
    function buildFirstTable()
    {
        $html = "<table><tr><th>Базис</th>";

        for ($i = 0; $i < $this->allVarsCount; $i++) {
            $html .= "<th>X" . ($i + 1) . "</th>";
        }

        $html .= "<th>В.Ч</th><th></th>";
        $html .= "</tr>";

        foreach ($this->limitations as $limit) {
            $html .= "<tr><th>X" . $limit['basis'] . "</th>";
            foreach ($limit['X'] as $x) {
                $html .= "<td>$x</td>";
            }
            $html .= "<td class='option'>0</td></tr>";
        }

        $html .= "<tr><th>ƒ</th>";
        foreach ($this->marks as $mark) {
            $html .= "<td>$mark</td>";
        }

        $html .= "</tr><tr><th></th>";
        for ($i = 0; $i < count($this->marks) - 1; $i++) {
            $html .= "<td class='option'>" . $this->marks[$i] * (-1) . "</td>";
        }

        $html .= "</tr></table>";

        $this->html = $html;
    }

    /** building current simplex table
     * @return mixed
     */
    function buildCurrentTable()
    {
        $html = "<table><tr><th>Базис</th>";
        for ($i = 0; $i < $this->allVarsCount; $i++) {
            $html .= "<th>X" . ($i + 1) . "</th>";
        }
        $html .= "<th>В.Ч</th></tr>";

        $i = 0;
        //basis row
        foreach ($this->matrix as $rowKey => $row) {
            if ($i != $this->lims_count) {
                $html .= "<tr><th>X" . ($this->basis[$i] + 1) . "</th>";
            } else {
                $html .= "<tr><th>ƒ</th>";
            }
            foreach ($row as $cellKey => $cell) {
                $html .= "<td>" . $cell->show() . "</td>";
            }
            $i++;
        }
        $html .= "</tr></table>";
        $this->html .= $html;
    }

    /**
     * building matrix for method run
     */
    function buildMatrix()
    {
        $this->limitations[]["X"] = $this->marks;
        foreach ($this->limitations as $limit) {
            $row = array();
            for ($i = 0; $i < $this->allVarsCount + 1; $i++)
                $row[] = new Fraction($limit["X"][$i]);

            $this->matrix[] = $row;
        }
    }

    /** main method circle
     * @return bool
     */
    function mainCircle()
    {
        $this->outRow = $this->inCol = array("index" => null, "value" => null);

        // Костыль для обычного и двоистого методов
        $this->getResultelement();

        $this->basis[$this->outRow['index']] = $this->inCol['index'];

        $resultElem = new Fraction($this->matrix[$this->outRow['index']][$this->inCol["index"]]->getNum(), $this->matrix[$this->outRow['index']][$this->inCol["index"]]->getDenom());
        foreach ($this->matrix[$this->outRow['index']] as &$val) {
            $val = Fraction::divide($val, $resultElem);
        }
        for ($i = 0; $i < $this->lims_count + 1; $i++) {
            if ($i == $this->outRow['index']) {
                continue;
            }
            $futureNull = $this->matrix[$i][$this->inCol['index']];

            $sign = $futureNull->getNum() < 0 ? 1 : -1;
            $num = abs($futureNull->getNum());
            $denom = $futureNull->getDenom();

            for ($j = 0; $j < $this->allVarsCount + 1; $j++) {
                $this->matrix[$i][$j] = Fraction::add($this->matrix[$i][$j], new Fraction($this->matrix[$this->outRow['index']][$j]->getNum() * $sign * $num, $this->matrix[$this->outRow['index']][$j]->getDenom() * $denom)
                );
            }
        }

        return true;
    }

    /** main method start function
     * @return mixed
     */
    function run()
    {
        $this->buildMatrix();
        while ($this->mainCircle()) {
            $this->buildCurrentTable();
            if($this->checkForResolve()){
                $answer = array();
                $i = 0;
                foreach ($this->function_vars as $rowKey => $row) {
                    if ($this->basis[$rowKey] == $this->basis[$i]) {
                        $answer[] = ($this->basis[$i] + 1);
                    }
                    $i++;
                }
                $html = '';
                $i = 0;
                $counter = 0;
                foreach ($this->matrix as $rowKey => $row) {
                    if(in_array($rowKey + 1, $answer)){
                        foreach ($row as $cellKey => $cell) {
                            if($cellKey == $this->allVarsCount){
                                $counter++;
                                if(count($this->function_vars) == $counter){
                                    $html .= "<td><div>" . $cell->show() . "</div></td>";
                                }
                                else{
                                    $html .= "<td><div style='margin-right: 5px;'>" . $cell->show() . "</div><div><span class='breaking'>;</span></div></td>";
                                }
                            }
                        }
                    }
                    $i++;
                }
                $html = '<table class="result-table">
                            <tr>
                                <td>x<sup>*</sup></td>
                                <td>(</td>' . $html . '
                                <td>)</td>
                                <td>, ƒ(x<sup>*</sup>) = </td>
                                <td>' . $this->matrix[$this->lims_count][$this->allVarsCount]->show() .
                    '</td>
                            </tr>
                        </table>';

                $this->answer = $html;
                $this->html = '<div class="simple-little-table">' . $this->html . '</div>' . $this->answer;
                return false;
            }
        }
        return true;
    }

    public function __get($attr){
        return $this->$attr;
    }
}