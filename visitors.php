<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#000000" />
    <meta name="description" content="Automated System Monitor "/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="./css/visit.css" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <script src='https://kit.fontawesome.com/a076d05399.js'></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <title>Vilog - List of visitors</title>
  </head>
  <body>
        <?php 
           include_once 'objet.php';
           include_once 'visitors.class.php';
           include_once 'roles.class.php';
           $roles = new roles($db);
        ?>
        <nav class="nana">
              <a class="name_project" href="#"><i class="fa fa-desktop"></i>  ViLog</a>
              <ul class="list">
                <li><a id="visitors" href="#">Visitors</a></li>
                <li><a id="employees" href="#">Employees</a></li>
                <li><?php echo $_SESSION['firstname'].'  '.$_SESSION['lastname'] ?></li>
                <li><a id="logout" class="logout" href="#">logout</a></li>
              </ul>
        </nav>
        <?php  
          if(!isset($_SESSION['firstname']) && !isset($_SESSION['lastname'])){
            echo '<script>document.location.href="index.php"</script>';
          }
        ?>
        <div class="contain">
            <div class="account">
                <?php $visitors->countRoles(); ?>
            </div>
            <div class="row mt-5">
              <div class="col">
                <?php 
                  $roles->selectroles();
                ?>
              </div>
              <div class="col">
                <input type="date" class="form-control">
              </div>
              <div class="col">
                <input type="date" class="form-control">
              </div>
              <div class="col gap-2 mx-auto">
                <button type="button" class="btn btn-success fw-bolder text-light  px-4">Search</button>
              </div>
             
            </div>

            <div class="cont_table">
              <table class="table">
                <thead>
                  <tr>
                    <th scope="col">Firstname</th>
                    <th scope="col">Lastname</th>
                    <th scope="col">Role</th>
                    <th scope="col">Email</th>
                    <th scope="col">IdNumber</th>
                    <th scope="col">PhoneNumber</th>
                    <th scope="col">Time in</th>
                    <th scope="col">Time out</th>
                  </tr>
                </thead>
                  <?php 
                    $visitors->list_visitors();
                    $visitors->list_employees();
                  ?>
              </table>
            </div>
        </div>
  </body>
  </html>

  <script>
      $(document).ready(function(){
        $('#logout').on('click' , function(e){
          e.preventDefault();
          var datas ={logout:$(this).text()}
          $.post(
            'visitors.class.php',
            datas,
            function(data){
              if(data)
                window.location.href=" http://localhost/Vilog/";
            }
          )
        });

        $("#visitors").on("click" , function(e){
          e.preventDefault();
          $("#list_employees").css("display" , "none");
          $("#list_visitors").css("display" , "");
        });

        $("#employees").on("click" , function(e){
          e.preventDefault();
          $("#list_visitors").css("display" , "none");
          $("#list_employees").css("display" , "");
        });

      });
  </script>