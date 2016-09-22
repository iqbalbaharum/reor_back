<?php
  include_once 'reg_crud.php';
?>
 
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
  <title>Register Device</title>
  <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include_once 'nav_bar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
      <div class="page-header">
        <h2>Register</h2>
      </div>


    <form action="reg.php" method="post" class="form-horizontal">
    
  
   
      <div class="form-group">
          <label for="profileid" class="col-sm-3 control-label">User Name</label>
          <div class="col-sm-9">
          <input name="username" type="text" class="form-control" id="username" placeholder="Username" value="<?php if(isset($_GET['edit'])) echo $editrow['username']; ?>" >
        </div>
        </div>

       <div class="form-group">
          <label for="profileNOIc" class= "col-sm-3 control-label">Type / Category</label>
          <div class="col-sm-9">
          <input name="type" type="text" class="form-control" id="profileNOIc" placeholder="Category" value="<?php if(isset($_GET['edit'])) echo $editrow['type']; ?>" >
        </div>
        </div>

        <div class="form-group">
          <label for="profilename" class= "col-sm-3 control-label">Device Id</label>
          <div class="col-sm-9">
          <input name="devid" type="text" class="form-control" id="devid" placeholder="Device ID" value="<?php if(isset($_GET['edit'])) echo $editrow['devid']; ?>" >
        </div>
        </div>

       
        <div class="form-group">
          <label for="profileposkod" class="col-sm-3 control-label">Stream</label>
          <div class="col-sm-9">
          <input name="stream" type="text" class="form-control" id="stream" placeholder="Stream" value="<?php if(isset($_GET['edit'])) echo $editrow['stream']; ?>" >
        </div>
        </div>

        <div class="form-group">
          <div class="col-sm-offset-3 col-sm-9">
          <?php if (isset($_GET['edit'])) { ?>
          <input type="hidden" name="oldpid" value="<?php echo $editrow['id']; ?>">
          <button class="btn btn-default" type="submit" name="update"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Kemaskini</button>
          <?php } else { ?>
          <button class="btn btn-primary" type="submit" name="create"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Register</button>
          <?php } ?>
          <button class="btn btn-danger" type="reset"><span class="glyphicon glyphicon-erase" aria-hidden="true"></span> Delete</button>
        </div>
      </div>

<div class="page-header">
        <h2>Register List</h2>
      </div>

<!--
  <div data-role="page" id="pageone">
    <div data-role="main" class="ui-content">
  
    <form class="ui-filterable">
      <input id="myFilter" data-type="search">
    </form>
    <ul data-role="listview" data-filter="true" data-input="#myFilter" data-autodividers="true" data-inset="true">
      <li><a href="#">A</a></li>
      <li><a href="#">B</a></li>
      <li><a href="#">C</a></li>
      <li><a href="#">D</a></li>
 
    </ul>
  </div>
</div>
-->

<!--
<div data-role="main" class="ui-content">
    <form>
      <input id="filterTable-input" data-type="search" placeholder="Search For Customers...">
    </form>
    <table data-role="table" data-mode="columntoggle" class="ui-responsive ui-shadow" id="myTable" data-filter="true" data-input="#filterTable-input">
      <thead>
        <tr>
          <th data-priority="6">CustomerID</th>
          <th>CustomerName</th>
          <th data-priority="1">ContactName</th>
          <th data-priority="2">Address</th>
          <th data-priority="3">City</th>
          <th data-priority="4">PostalCode</th>
          <th data-priority="5">Country</th>
        </tr>
      </thead>
</table>
</div>
-->

 <table class="table table-striped table-bordered">
        <tr>
          <th>User Name</th>
          <th>Category / Type</th>
          <th>Device ID</th>
          <th>Stream</th>
        </tr>

      <?php
      // Read    
      $per_page = 5;
      if (isset($_GET["page"]))
        $page = $_GET["page"];
      else
        $page = 1;
      $start_from = ($page-1) * $per_page;
      $sql = "select * from register LIMIT $start_from, $per_page";
      $result = $mydb->query($sql);
      while ($readrow = $result->fetch_array()) {
      ?>   
      <tr>
        <td><?php echo $readrow['username']; ?></td>
        <td><?php echo $readrow['type']; ?></td>
        <td><?php echo $readrow['devid']; ?></td>
        <td><?php echo $readrow['stream']; ?></td>

        <td>
          <a href="reg.php?delete=<?php echo $readrow['username']; ?>" onclick="return confirm('Are you sure to delete it?');" class="btn btn-danger btn-xs" role="button">Delete</a>
        </td>
      </tr>
      <?php } ?>
 
      </table>
    </div>
  </div>



<div class="row">
    <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-3">
      <nav>
          <ul class="pagination">
          <?php
        $sql = "select * from register";
        $result = $mydb->query($sql);
 
        $total_records = mysqli_num_rows($result);
        $total_pages = ceil($total_records / $per_page);
        ?>
        <li <?php if ($page==1) echo "class=\"disabled\"" ?>><a href="reg.php?page=<?php echo $page-1 ?>" aria-label="Previous"><span aria-hidden="true">«</span></a></li>
        <?php
        for ($i=1; $i<=$total_pages; $i++)
          if ($i == $page)
            echo "<li class=\"active\"><a href=\"reg.php?page=$i\">$i</a></li>";
          else
            echo "<li><a href=\"reg.php?page=$i\">$i</a></li>";
        ?>
        <li <?php if ($page==$total_pages) echo "class=\"disabled\"" ?>><a href="reg.php?page=<?php echo $page+1 ?>" aria-label="Next"><span aria-hidden="true">»</span></a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>







    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
 
</body>
</html>