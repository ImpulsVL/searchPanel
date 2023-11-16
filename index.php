<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Новостной блог</title>
</head>

<body class="mx-auto p-5 m-5">
  <form id="searchForm" method="GET" name="search-form">
    <div class="form-floating m-5  align-middle d-flex justify-content-center ">
      <input type="text" class="form-control" id="search" name="search" required minlength="3">
      <label for="searchlabel">Поиск по комментариям: введите ключевое слово для поиска</label>
      <button type="submit" class="ms-5 btn btn-primary pe-5 ps-5" id="submitBtn">Найти</button>
    </div>
  </form>

  <?php
  /* Подключение к БД */
  $conn = new mysqli("localhost", "root", "", "blog");

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  if (isset($_GET['search'])) {
    $input = $_GET['search'];

    if (strlen($input) < 3) {
        echo "Строка поиска должна быть не менее 3 символов.";
        exit();
    }

    /* Запрос к БД для получения заголовка записи*/
    $sql = "SELECT posts.id, posts.title FROM posts JOIN comments ON posts.id = comments.postId WHERE comments.body LIKE '%{$input}%' GROUP BY posts.id, posts.title";

    $result = $conn->query($sql);

    if ($result !== false && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<h3>{$row['title']}</h3>";

            /* Запрос к БД для получения комментария к нужной записи*/
            $commentSql = "SELECT comments.body FROM comments WHERE comments.postId = {$row['id']} AND comments.body LIKE '%{$input}%'";
            $commentResult = $conn->query($commentSql);

            if ($commentResult !== false && $commentResult->num_rows > 0) {
                $comment = $commentResult->fetch_assoc();
                echo "<p>{$comment['body']}</p>";
            } else {
                echo "В комментариях к этой записи не найдена строка \"{$input}\".";
            }
        }
    } else {
        echo "В записях не найдена строка \"{$input}\".";
    }
}

  /*Хранит в себе массив JSON*/
  $jsonSources = [
    "https://jsonplaceholder.typicode.com/posts",
    "https://jsonplaceholder.typicode.com/comments",
  ];

  $postCount = 0;
  $commentCount = 0;

  /*Получаем записи и преобразовываем файлы JSON*/
  foreach ($jsonSources as $url) {
    $json = file_get_contents($url);
    $data = json_decode($json, true);

    switch ($url) {
      case "https://jsonplaceholder.typicode.com/posts":
        $fields = ['userId', 'id', 'title', 'body'];
        $table = 'posts';
        break;
      case "https://jsonplaceholder.typicode.com/comments":
        $fields = ['postId', 'id', 'name', 'email', 'body'];
        $table = 'comments';
        break;
      default:
        break;
    }

    /*Выбираются только те записи, которые не существуют в БД*/
    foreach ($data as $item) {
      $columns = implode(", ", $fields);
      $values = "'" . implode("', '", $item) . "'";

      $selectSql = "SELECT * FROM $table WHERE ";
      $whereConditions = [];

      foreach ($fields as $field) {
        $whereConditions[] = "$field = '{$item[$field]}'";
      }

      $selectSql .= implode(" AND ", $whereConditions);
      $result = $conn->query($selectSql);

      /*Вносим записи в БД*/
      if ($result->num_rows == 0) {
        $sql = "INSERT INTO $table($columns) VALUES ($values)";

        if ($conn->query($sql) === TRUE) {
          if ($table === 'posts') {
            $postCount++;
          } elseif ($table === 'comments') {
            $commentCount++;
          }
        } else {
          echo "Error: " . $sql . "<br>" . $conn->error;
        }
      }
    }
  }

  $conn->close();
  ?>

  <!-- Выводим количество записей в консоль -->
  <script>
    var postCount = <?php echo $postCount ?>;
    var commentCount = <?php echo $commentCount ?>;
    console.log("Загружено " + postCount + " записей и " + commentCount + " комментариев");
  </script>

</body>

</html>