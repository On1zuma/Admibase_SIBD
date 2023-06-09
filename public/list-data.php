<?php

include('./components/navbar.php');

$status = new RightController();
$status->isLoggedIn();

$data = new DataController();
$tableUrl = $data->checkIfUserCanAccessTable();
$columns = $data->listOfTableName($tableUrl);
$rows = $data->listOfRowName($tableUrl);
$order = 0;
if (isset($_GET['order'])) {
    if ($_GET['order'] == "DESC" || $_GET['order'] == "ASC") {
        $order = $_GET['order'];
        $columnFilter = $_GET['column'];
        if (in_array($columnFilter, $columns)) {
            $rows = $data->listOfRowNameWithFilter($tableUrl, $columnFilter, $order);
        }
    }
}
if (isset($_GET["search"])) {
    $rows = $data->listOfRowNameWithSearch($tableUrl, $_GET["search"]);
}

$tableUrlWithSpaces = str_replace('_', ' ', $tableUrl);

if (isset($_GET['page'])) {
    $pg = $_GET['page'];
} else {
    $pg = 1;
}

?>
  <div class="mx-auto" style="width: 100vw; margin-top: 2rem;">
  <form method="POST" action="search.php?table=<?php echo $tableUrl ?>">
  <div style="display: flex; margin-bottom: 2rem; justify-content: center;">
    <div style="position: relative; width: 60%;">
      <input name="search" type="text" class="form-control" placeholder="Search" aria-describedby="basic-addon1" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
      <?php if (!empty($_GET['search'])) { ?>
        <a href="list-data.php?table=<?php echo $tableUrl ?>" style="position: absolute; top: 50%; right: 1rem; transform: translateY(-50%); cursor: pointer;" onclick="document.querySelector('input[name=\'search\']').value=''; this.style.display='none';"><i class="fa-solid fa-xmark"></i></a>
      <?php } ?>
    </div>
    <button href="list-data.php?table=<?php echo $tableUrl ?>" style="margin-left:.4rem" class="btn btn-primary text-white"><i class="fa-solid fa-magnifying-glass" style="margin-left:.3rem; margin-right:.3rem;"></i></button>
  </div>
</form>

  </div>
    <form method="POST" action="delete.php?table=<?php echo $tableUrl ?>">
      <div class="filter" style="margin-bottom: 1rem; display:flex; align-items:center; flex-direction:row; justify-content:space-around;">
        <span class="label label-default" style="font-weight: 900; text-transform: uppercase;"><i class="fa-solid fa-database"></i> <?php echo $tableUrlWithSpaces;  ?></span>
        <div style="display:flex; align-items:center; flex-direction:row; justify-content:space-between; gap:1rem">
          <a href="form.php?table=<?php echo  $tableUrl ?>" type="button" class="btn btn-primary text-white"> Create</a>
          <button id="delete-btn" type="submit" class="btn btn-danger text-white">Delete</button></div>
      </div>
      <div  style="overflow: auto;" >
      <table class="table">
        <thead>
          <tr>
            <th scope="col">#</th>
            <?php foreach ($columns as $column) {
                if ($order == "DESC") {
                    if (isset($_GET['column']) &&$column == $_GET['column']) {
                        echo '<th scope="col"><a href="list-data.php?table='.$tableUrl.'&column='.$column.'&order=ASC" style="white-space:nowrap;"><i class="fa-solid fa-caret-down"></i> '.$column.'</a></th>';
                    } else {
                        echo '<th scope="col"><a href="list-data.php?table='.$tableUrl.'&column='.$column.'&order=ASC">'.$column.'</a></th>';
                    }
                } else {
                    if (isset($_GET['column']) && $column == $_GET['column']) {
                        echo '<th scope="col"><a href="list-data.php?table='.$tableUrl.'&column='.$column.'&order=DESC" style="white-space:nowrap;"><i class="fa-solid fa-caret-up"></i> '.$column.'</a></th>';
                    } else {
                        echo '<th scope="col"><a href="list-data.php?table='.$tableUrl.'&column='.$column.'&order=DESC">'.$column.'</a></th>';
                    }
                }
            } ?>
            <th><a href=""></a></th>
          </tr>

        </thead>
        <tbody>
          <?php
            foreach ($rows as $index => $row) {
                echo "<tr>";
                echo "<td><input type='checkbox' name='ids[]' value='".$row['id']."' ></td>";
                foreach ($columns as $column) {
                    echo "<td>" . $row[$column] . "</td>";
                }
                echo "<td><a href='form.php?table=".$tableUrl."&id=".$row['id']."'>Edit</a></td>";
                echo "</tr>";
            }
?>
          </tbody>
        </table>  
      </div>

    </form>

    <nav aria-label="Page navigation example">
      <ul class="pagination m-3">
        <li class="page-item user-select-none <?php if ($pg <= 1) {
            echo 'disabled';
        }?>"><a class="page-link" href="list-data.php?table=<?php echo $tableUrl;?>&page=<?php echo $pg-1;
if (isset($_GET['order'])) {
    echo '&order='.$_GET['order'].'&column='.$_GET['column'];
}if (isset($_GET['search'])) {
    echo '&search='.$_GET['search'];
}?>">Previous</a></li>
        <li class="page-item" style="<?php if ($pg <= 2) {
            echo 'display:none;';
        } ?>"><a class="page-link" href="list-data.php?table=<?php echo $tableUrl;?>&page=<?php echo $pg-2;
if (isset($_GET['order'])) {
    echo '&order='.$_GET['order'].'&column='.$_GET['column'];
}if (isset($_GET['search'])) {
    echo '&search='.$_GET['search'];
}?>"><?php echo $pg-2;?></a></li>
        <li class="page-item" style="<?php if ($pg <= 1) {
            echo 'display:none;';
        } ?>"><a class="page-link" href="list-data.php?table=<?php echo $tableUrl;?>&page=<?php echo $pg-1;
if (isset($_GET['order'])) {
    echo '&order='.$_GET['order'].'&column='.$_GET['column'];
}if (isset($_GET['search'])) {
    echo '&search='.$_GET['search'];
}?>"><?php echo $pg-1;?></a></li>
        <li class="page-item"><a class="page-link bg-primary text-white" href="#"><?php echo $pg;?></a></li>
        <li class="page-item"><a class="page-link" href="list-data.php?table=<?php echo $tableUrl;?>&page=<?php echo $pg+1;
if (isset($_GET['order'])) {
    echo '&order='.$_GET['order'].'&column='.$_GET['column'];
}if (isset($_GET['search'])) {
    echo '&search='.$_GET['search'];
}?>"><?php echo $pg+1;?></a></li>
        <li class="page-item"><a class="page-link" href="list-data.php?table=<?php echo $tableUrl;?>&page=<?php echo $pg+2;
if (isset($_GET['order'])) {
    echo '&order='.$_GET['order'].'&column='.$_GET['column'];
}if (isset($_GET['search'])) {
    echo '&search='.$_GET['search'];
}?>"><?php echo $pg+2;?></a></li>
        <li class="page-item"><a class="page-link" href="list-data.php?table=<?php echo $tableUrl;?>&page=<?php echo $pg+1;
if (isset($_GET['order'])) {
    echo '&order='.$_GET['order'].'&column='.$_GET['column'];
}if (isset($_GET['search'])) {
    echo '&search='.$_GET['search'];
}?>">Next</a></li>
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

<script>
  document.getElementById("delete-btn").addEventListener("click", function(e) {
    var checkboxes = document.querySelectorAll("input[type=checkbox]:checked");
    if (checkboxes.length === 0) {
      e.preventDefault(); // We prevent form from being submitted
      alert("Please select at least one row to delete.");
    }
  });
</script>

<?php include('./components/footer.php'); ?>


