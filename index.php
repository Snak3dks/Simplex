<html>
<head>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>
<?php
include_once('methods/DualSimplexMethod.php');
if (isset($_POST['init'])) {
    $vars = $_POST['init']['variables'];
    $limits = $_POST['init']['limitations'];

    printf("<div class='simple-little-table'><h2>Заповніть коефіцієнти при змінних, натиніть 'Далі'</h2><form action='index.php' method='post'>
<table class='input'>");
    for ($i = 0; $i < $limits; $i++) {
        printf("<tr>");
        for ($j = 0; $j < $vars; $j++) {
            printf("<td width='80' align='center'>
                        <input type='number' name='row%dcol%d' value='0' /> x<sub>%d</sub>
                    </td>", $i, $j, $j + 1);
        }
        printf('<td><select name="usl%d" size="1" class="input">
                    <option value="more">≥</option>
                    <option value="less">≤</option>
                </select></td>
                <td><input type="number" name="r%d" value="0" /></td>', $i, $i);
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
             </form></div>", $vars, $limits);

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

    $dual_simplex = new DualSimplexMethod($funcFactors, $limitations, $_POST['vars'], $_POST['limits']);

    printf('<div class="simple-little-table"><h2>Двоїстий симплекс-метод, розв`язання</h2>');
    if($dual_simplex->error_msg){
        printf("<h3>Помилка: <i>%s</i></h3>", $dual_simplex->error_msg);
    }
    else{
        echo $dual_simplex->html;
    }
    printf('</div>');

} else {
    ?>
    <div class="simple-little-table"><h2>Двоїстий симплекс-метод, параметризація</h2>

        <form action="index.php" method="post">
            <p>Кількість змінних: <input type="number" name="init[variables]" value="4"/></p>

            <p>Кількість обмежень: <input type="number" name="init[limitations]" value="2"/></p>
            <input type="submit" value="Далі"/>
        </form>
    </div>
<?php
}
?>
</body>
</html>