<!DOCTYPE html>
<html>
    <head>
        <title>Balances</title>
        <style>
            table {
                width: 100%;
                border-collapse: collapse;
                text-align: center;
            }

            table tr th, table tr td {
                padding: 5px;
                border: 1px #eee solid;
            }

            tfoot tr th, tfoot tr td {
                font-size: 20px;
            }

            tfoot tr th {
                text-align: right;
            }
        </style>
    </head>
    <body>
        <table>
            <thead>
                <tr>
                    <th>Остатки IEK от:</th>
                    <th>Остатки EKF от:</th>
                    <th>Остатки SE от:</th>
                    <th>Остатки TDM от:</th>
                </tr>
                <tr>
                    <?php foreach ($dateToScreen as $date): ?>
                        <td><?=  $date ?></td>
                    <?php endforeach ?>

                </tr>
                <tr>
                    <th>Артикул</th>
                    <th>Количество</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th colspan="2"> Список к запросу: </th>
                </tr>
                    <?php foreach ($requestList as $k => $v): ?>
                            
                        <tr>
                            <td><?=  $k ?></td>
                            <td><?=  $v ?></td>
                        </tr>
                    <?php endforeach ?>

                <tr>
                    <th colspan="2"> Позиции со сроком доставки до 5 р.д.: </th>
                </tr>   
                    <?php foreach ($safficientListBy as $k => $v): ?>
                        <tr>
                            <td><?=  $k ?></td>
                            <td><?=  $v ?></td>
                        </tr>
                    <?php endforeach ?>

                <tr>
                    <th colspan="2"> Позиции со сроком доставки более 5 р.д.: </th>
                </tr>
                    <?php foreach ($safficientListAfar as $k => $v): ?>
                        <tr>
                            <td><?=  $k ?></td>
                            <td><?=  $v ?></td>
                        </tr>
                    <?php endforeach ?>
            </tbody>
            <tfoot>
                
            </tfoot>
        </table>
    </body>
</html>