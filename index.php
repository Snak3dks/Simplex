<html>
<head>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>
<?php
if (isset($_POST['init'])) {
    $vars = $_POST['init']['variables'];
    $limits = $_POST['init']['limitations'];

    $disabled = '<option value="equals">=</option>';
    if($_POST['method'] == 2 || $_POST['method'] == 3){
        $disabled = '<option value="more" %s>≥</option><option value="less" %s>≤</option>';
    }
    printf("<div class='simple-little-table'>
                <h2>Заповніть коефіцієнти при змінних, натиніть 'Далі'</h2>
                <p>Базисні змінні будуть додані автоматично!</p>
                <form action='index.php' method='post'>
                <table class='input'>");
    for ($i = 0; $i < $limits; $i++) {
        printf("<tr>");
        for ($j = 0; $j < $vars; $j++) {
            printf("<td width='80' align='center'>
                        <input type='number' name='row%dcol%d' value='0' /> x<sub>%d</sub>
                    </td>", $i, $j, $j + 1);
        }
        printf('<td width="80" align="center">
                    <select name="usl%d" size="1" class="input">
                    %s
                    </select>
                </td>
                <td width="80" align="center"><input type="number" name="r%d" value="0" /></td>', $i, $disabled, $i);
        printf("</tr>");
    }
    printf("</table><strong>Функція цілі F(x)</strong><br/>");
    for ($i = 0; $i < $vars; $i++) {
        printf("<input name='fx%d' value='0' type='number' /> x<sub>%d</sub> ", $i, $i + 1);
    }
    printf("→ min<br /><br />
            <input type='submit' value='Далі' />
            <input type='hidden' name='toresolve' value='1' />
            <input type='hidden' name='vars' value='%d' />
            <input type='hidden' name='limits' value='%d' />
            <input type='hidden' name='method' value='%d' />
             </form></div>", $vars, $limits, $_POST['method']);

} elseif ($_POST['toresolve'] == 1) {
    for ($i = 0; $i < $_POST['limits']; $i++) {
        $lims = array();
        $funcFactors = array();
        for ($j = 0; $j < $_POST['vars']; $j++) {
            $lims[] = $_POST['row' . $i . 'col' . $j];
            $funcFactors[] = $_POST['fx' . $j];
        }
        $limitations[] = array('X' => $lims, 'inequality' => $_POST['usl' . $i], 'member' => $_POST['r' . $i]);
    }

    $html = '<div style="text-align: center;">';
    switch($_POST['method']){
        case '1':
            include_once('methods/simplex/SimpleSimplexMethod.php');
            $simple_simplex = new SimpleSimplexMethod($funcFactors, $limitations, $_POST['vars'], $_POST['limits']);
            $html .= '<h2>Симплекс-метод</h2>';
            $html .= $simple_simplex->error_msg != null ? $simple_simplex->error_msg : $simple_simplex->html;
            break;
        case '2':
            include_once('methods/simplex/DualSimplexMethod.php');
            $dual_simplex = new DualSimplexMethod($funcFactors, $limitations, $_POST['vars'], $_POST['limits']);
            $html .= '<h2>Двоїстий симплекс-метод</h2>';
            $html .= $dual_simplex->error_msg != null ? $dual_simplex->error_msg : $dual_simplex->html;
            break;
        case '3':
            include_once('methods/GomoriFirst.php');
            $gomori_first = new GomoriFirst($funcFactors, $limitations, $_POST['vars'], $_POST['limits']);
            $html .= '<h2>Перший алгоритм Гоморі</h2>';
            $html .= $gomori_first->error_msg != null ? $gomori_first->error_msg : $gomori_first->html;
            break;
        default:
            $html .= 'Не обрано метод для розв`язання!';
            break;
    }
    $html .= '</div>';
    echo $html;
} else {
    ?>
    <div class="simple-little-table">
        <h2>Розв'язання задач ММДО</h2>
        <form action="index.php" method="post" id="init-form">
            <p>Метод:  <select name="method" required><option value="0" disabled selected></option><option value="1">Симплекс-метод</option><option value="2">Двоїстий симплекс-метод</option><option value="3">Перший алогоритм Гоморі</option></select></p>
            <p><label>К-сть змінних:     <input type="number" name="init[variables]" value="4" required/></label></p>
            <p><label>К-сть обмежень:  <input type="number" name="init[limitations]" value="2" required/></label></p>
            <p><input type="submit" value="Далі"/></p>
        </form>
    </div>
    <?php
}
?>
</body>
</html>