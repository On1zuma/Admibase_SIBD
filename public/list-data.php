<?php

include('./components/navbar.php');

$status = new RightController();
$status->isLoggedIn();

$bdd = new PDO('mysql:host=localhost;dbname=gamedb;charset=utf8;', 'root', '');

$table_name = $_SESSION['id']['table']; // users tables
$tableUrl = $_GET['table']; // table set in the url
$stmt = $bdd->prepare("SHOW TABLES");
$stmt->execute();
$tablesBd = $stmt->fetchAll(PDO::FETCH_COLUMN);

$tableUrlWithSpaces = str_replace('_', ' ', $tableUrl);

// Check if the table exists in the database
if (!in_array($tableUrl, $tablesBd)) {
    header('Location: 404.php');
    exit; // stop the script
}

// Check if the user has access to the table
if ($table_name != "*" && !in_array($tableUrl, explode(", ", $table_name))) {
    header('Location: list-table.php');
    exit; // stop the script
}
?>

<div class="mx-auto" style="width: 70vw; margin-top: 2rem;">
  <div class="filter" style="margin-bottom: 1rem; display:flex; align-items:center; flex-direction:row; justify-content:space-between;">
    <span class="label label-default" style="font-weight: 900; text-transform: uppercase;"><?php echo $tableUrlWithSpaces;  ?></span>
    <div style="display:flex; align-items:center; flex-direction:row; justify-content:space-between; gap:1rem">
      <input type="text" class="form-control" placeholder="Search" aria-label="Username" aria-describedby="basic-addon1">
      <a href="form.php" type="button" class="btn btn-primary text-white"><span class="glyphicon glyphicon-remove"></span> Create</a>
      <a type="button" class="btn btn-danger text-white"><span class="glyphicon glyphicon-remove"></span> Delete</a>
    </div>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col"><a href="">First</a></th>
        <th scope="col"><a href="">Handle</a></th>
        <th scope="col"><a href="">Last (to filter by)</a></th>
        <th><a href=""></a></th>
      </tr>

    </thead>
    <tbody>
      <tr>
        <th scope="row"><input type="checkbox" value="" ></th>
        <td>Mark</td>
        <td>Otto</td>
        <td>@mdo</td>
        <td><a href="form.php">Edit</a></td>
      </tr>
      <tr>
      <th scope="row"><input type="checkbox" value="" ></th>
        <td>Jacob</td>
        <td>Thornton</td>
        <td>@fat</td>
        <td><a href="form.php">Edit</a></td>
      </tr>
      <tr>
      <th scope="row"><input type="checkbox" value="" ></th>
        <td>Larry</td>
        <td>the Bird</td>
        <td>@twitter</td>
        <td><a href="form.php">Edit</a></td>
      </tr>
    </tbody>
  </table>

  <nav aria-label="Page navigation example">
  <ul class="pagination">
    <li class="page-item"><a class="page-link" href="#">Previous</a></li>
    <li class="page-item"><a class="page-link" href="#">1</a></li>
    <li class="page-item"><a class="page-link" href="#">2</a></li>
    <li class="page-item"><a class="page-link" href="#">3</a></li>
    <li class="page-item"><a class="page-link" href="#">Next</a></li>
  </ul>
</nav>
</div>


<style>.list-group {line-height:30px}
.pull-right{
  position: absolute;
  right: 1rem;
}

@media (max-width: 1068px) {
    .pull-right{
    position: static;
    margin-left: 5rem;
  }
    }
</style>

<?php include('./components/footer.php'); ?>

