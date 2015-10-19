<html>
<head>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/bootstrap.css"/>
    <link rel="stylesheet" href="css/simplex-table.css"/>

    <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.1.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="js/bootstrap-number-input.js"></script>
</head>
<body>
<div class="container text-center">
    <?php
        if (isset($_POST['init']))
        {
        $vars = $_POST['init']['variables'];
        $limits = $_POST['init']['limitations'];

        $disabled = '<option value="equals">=</option>';
        if ($_POST['method'] == 2)
        {
            $disabled = '<option value="more" %s>≥</option><option value="less" %s>≤</option>';
        }
        if($_POST['method'] == 3){
            $disabled = '<option value="less" %s>≤</option>';
        }
    ?>
    <div class="title">
        <?php
            switch ($_POST['method'])
            {
                case '1':
                    $title = '<h2>Симплекс-метод</h2>';
                    break;
                case '2':
                    $title = '<h2>Двоїстий симплекс-метод</h2>';
                    break;
                case '3':
                    $title = '<h2>Перший алгоритм Гоморі</h2>';
                    break;
                default:
                    $title = '';
                    break;
            }
            echo $title;
        ?>
        <h3>Заповніть коефіцієнти при змінних, натиніть 'Далі'.</h3>

        <p>(Базисні змінні будуть додані автоматично!)</p>
    </div>
    <form action='index.php' method='post' id="init-form" class="form-inline" style="margin: 0 auto;">
        <div class="form-group" style="display: block;">
            <table class='simple-little-table'>
                <?php
                    for ($i = 0; $i < $limits; $i++)
                    {
                        echo "<tr>";
                        for ($j = 0; $j < $vars; $j++)
                        {
                            echo "<td>
                                        <input class='form-control number-input' type='number' name='row" . $i . "col" . $j . "' value='0' required='required' />
                                        <strong>x<sub>" . ($j + 1) . "</sub></strong>
                                  </td>";
                        }
                        echo '<td width="80" align="center"><select name="usl' . $i . '" size="1" class="input form-control" required="required">' . $disabled . '</select></td>
                              <td width="80" align="center"><input class="form-control number-input" type="number" name="r' . $i . '" value="0" required="required" /></td>';
                        echo "</tr>";
                    }
                ?>
            </table>
        </div>
        <div class="form-group" style="margin: 40px auto;text-align: center;display: block;">
            <h4>Функція цілі ƒ(x)</h4>
            <?php
                if ($_POST['method'] == 2)
                {
                    $min = "min='0'";
                }
                for ($i = 0; $i < $vars; $i++)
                {
                    echo "<input class='form-control number-input' style='width:50px' name=fx" . $i . " " . $min . " value='0' type='number' required='required' />
                        <strong>x<sub>" . ($i + 1) . "</sub></strong> ";
                }
            ?> → min
        </div>
        <div class="form-group" style="display:block">
            <div style="margin: 0 auto; width: 200px;">
                <input type='hidden' name='toresolve' value='1'/>
                <input type='hidden' name='vars' value='<?= $vars ?>'/>
                <input type='hidden' name='limits' value='<?= $limits ?>'/>
                <input type='hidden' name='method' value='<?= $_POST['method'] ?>'/>
                <button type="submit" class="btn btn-primary btn-default btn-block">Далі</button>
            </div>
        </div>

        <?php
            }
            elseif ($_POST['toresolve'] == 1)
            {
                for ($i = 0; $i < $_POST['limits']; $i++)
                {
                    $lims = array();
                    $funcFactors = array();
                    for ($j = 0; $j < $_POST['vars']; $j++)
                    {
                        $lims[] = $_POST['row' . $i . 'col' . $j];
                        $funcFactors[] = $_POST['fx' . $j];
                    }
                    $limitations[] = array('X' => $lims, 'inequality' => $_POST['usl' . $i], 'member' => $_POST['r' . $i]);
                }

                $html = '';
                switch ($_POST['method'])
                {
                    case '1':
                        include_once('methods/simplex/SimpleSimplexMethod.php');
                        $simple_simplex = new SimpleSimplexMethod($funcFactors, $limitations, $_POST['vars'], $_POST['limits']);
                        $html .= '<h2 class="title">Симплекс-метод</h2>';
                        $html .= $simple_simplex->error_msg != null ? $simple_simplex->error_msg : $simple_simplex->html;
                        break;
                    case '2':
                        include_once('methods/simplex/DualSimplexMethod.php');
                        $dual_simplex = new DualSimplexMethod($funcFactors, $limitations, $_POST['vars'], $_POST['limits']);
                        $html .= '<h2 class="title">Двоїстий симплекс-метод</h2>';
                        $html .= $dual_simplex->error_msg != null ? $dual_simplex->html : $dual_simplex->html;
                        break;
                    case '3':
                        include_once('methods/GomoriFirst.php');
                        $gomori_first = new GomoriFirst($funcFactors, $limitations, $_POST['vars'], $_POST['limits']);
                        $html .= '<h2 class="title">Перший алгоритм Гоморі</h2>';
                        $html .= $gomori_first->error_msg != null ? $gomori_first->error_msg : $gomori_first->html;
                        break;
                    default:
                        $html .= 'Не обрано метод для розв`язання!';
                        break;
                }

                echo $html;
            }
            else
            {
                ?>
                <div class="title">
                    <h2 class="text-center">Розв'язання задач ММДО</h2>
                </div>
                <form action="index.php" method="post" class="form-horizontal" style="width:400px; margin: 0 auto;">
                    <div class="form-group">
                        <label for="method-select" class="col-sm-4 control-label">Метод:</label>

                        <div class="col-md-7">
                            <select id="method-select" name="method" class="form-control" required="required">
                                <option value="0" disabled selected></option>
                                <option value="1">Симплекс-метод</option>
                                <option value="2">Двоїстий симплекс-метод</option>
                                <option value="3">Перший алогоритм Гоморі</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="vars-count" class="col-sm-4 control-label">Змінних:</label>

                        <div class="col-md-7">
                            <input id="vars-count" class="form-control number-input" type="number"
                                   name="init[variables]"
                                   min="1"
                                   max="10"
                                   value="2"
                                   required="required"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="lims-count" class="col-sm-4 control-label">Обмежень:</label>

                        <div class="col-md-7">
                            <input id="lims-count" class="form-control number-input" type="number"
                                   name="init[limitations]"
                                   min="1"
                                   max="10"
                                   value="2" required="required"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-7">
                            <button type="submit" class="btn btn-primary btn-default btn-block">Далі</button>
                        </div>
                    </div>
                </form>
                <?php
            }
        ?>
</div>
<script>
    $('.number-input').bootstrapNumber();

    $(".input-group button").mousedown(function () {
        btn = $(this);
        input = btn.closest('.input-group').find('input');
        btn.closest('.input-group').find('button').prop("disabled", false);

        if (btn.attr('data-dir') == 'up') {
            action = setInterval(function(){
                if ( input.attr('max') == undefined || parseInt(input.val()) < parseInt(input.attr('max')) ) {
                    input.val(parseInt(input.val())+1);
                }else{
                    btn.prop("disabled", true);
                    clearInterval(action);
                }
            }, 50);
        } else {
            action = setInterval(function(){
                if ( input.attr('min') == undefined || parseInt(input.val()) > parseInt(input.attr('min')) ) {
                    input.val(parseInt(input.val())-1);
                }else{
                    btn.prop("disabled", true);
                    clearInterval(action);
                }
            }, 50);
        }
    }).mouseup(function(){
        clearInterval(action);
    });
</script>
</body>
</html>