<?php
require_once('./settings.php');

try {
    $db = new \PDO(DB_DNS, DB_USER, DB_PASSWORD, []);
} catch (Exception $e) {
    echo 'Произошла ошибка: ',  $e->getMessage(), "\n";
    die();
}

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($_REQUEST['action']) {
        case 'add':
            if ($_REQUEST['name'] != false) {
                try {
                    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $statement = $db->prepare('INSERT INTO users (username, created_at) VALUES (?, ?)');
                    $statement->execute(array($_REQUEST['name'], date("Y-m-d")));
                } catch (Exception $th) {
                    $_SESSION['errors'] = "Желаемое имя недоступно";
                }
            } else {
                $_SESSION['errors'] = "Поле 'Имя' не заполнено";
            }
            break;

        case 'delete':
            $statement = $db->prepare('UPDATE users SET users.deleted_at = ? WHERE users.id = ?');
            $statement->execute(array(date("Y-m-d"), $_REQUEST['id']));
            break;
    }
    header("Location: ./index.php");
} else {
    $errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : '';
    unset($_SESSION['errors']);
    ?>

        <!DOCTYPE html>
        <html>

        <head>
            <title>Пользователи</title>
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        </head>

        <body>
            <div class="container-fluid">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th class="col-1">№</td>
                            <th class="col-10">Имя</td>
                            <th class="col-1">Действие</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                            $data = $db->query("SELECT username, id FROM users WHERE deleted_at IS NULL;");
                            $result = $data->fetchAll();
                            if ($result) {
                                for ($i = 0; $i < sizeof($result); $i++) {
                                    echo "<tr><td>", $i + 1, "</td><td>{$result[$i]['username']}</td>";
                                    echo '<td><form action="./index.php" method="POST"><input type="hidden" name="action" value="delete">';
                                    echo '<input type="hidden" name="id" value="', $result[$i]['id'];
                                    echo '"><input type="submit" value="Удалить" class="btn btn-danger"></td></form></tr>';
                                }
                            } else {
                                echo "<tr><td>Пользователей нет</td></tr>";
                            }
                            ?>

                    </tbody>
                </table>

                <?php
                    if (!empty($errors)) {
                        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
                        echo $errors;
                        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>';
                    }
                    ?>

            </div>
            <form action="./index.php" method="POST">
                <div class="row form-group">
                    <label class="col-form-label col-sm-1 text-right" for="name">Имя</label>
                    <div class="col-sm-10">
                        <input type="text" name="name" id="name" class="form-control">
                    </div>
                    <div class="col-sm-1">
                        <input type="hidden" name="action" value="add">
                        <input type="submit" value="Создать" class="btn btn-primary">
                    </div>
                </div>
            </form>
            </div>
            <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        </body>

        </html>
    <?php
    }
